<?php

namespace App\Schedulers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\{
    Order,
    Branch,
    ProcessLock
};
use App\Helpers\HttpCurlCalls;
use Aws\DynamoDb\DynamoDbClient;
use App\Models\MerchantSettlements as MSModel;

class MerchantSettlements
{
    public function __invoke()
    {
        try
        {
            // TODO: Review timezone handling — cutoffs use date() while app timezone is fixed to Asia/Kolkata.
            $orders = Order::select('order_id', 'order_order_id', 'order_branch_id', 'settlement_date')
                ->where([
                    ['settlement_date', '<=', date('Y-m-d')],
                    ['settlementStatus', 0],
                    ['status_id', 18]
                ])
                ->with('branchDetails', 'branchDetails.storegroup')
                ->get();

            if ($orders->isEmpty()) {
                ProcessLock::updateColData("BizAPI_MerchantSettlements", 0);
                return;
            }

            $orderIds = $orders->pluck('order_id')->toArray();
            Order::whereIn('order_id', $orderIds)
                ->where('settlementStatus', 0)
                ->update(['settlementStatus' => 1]);

            $orders = Order::select('order_id', 'order_order_id', 'order_branch_id', 'settlement_date')
                ->whereIn('order_id', $orderIds)
                ->where('settlementStatus', 1)
                ->with('branchDetails', 'branchDetails.storegroup')
                ->get();

            $listingMode = config('app.settlement_mode') ?? 0;
            $request = array_map(function($ord)
            {
                return [
                    'OrderId'       => @$ord['order_order_id'],
                    'BranchId'      => @$ord['order_branch_id'],
                    'StoreRefId'    => @$ord['branch_details']['storegroup']['storeRefId']
                ];
            }, $orders->toArray());
            $response = NULL;
            if($request)
            {
                $apiURL = DB::table('sys_configuration')->where('cfg_Name', 'MERCHANT_SETTLEMENT_API')->value('cfg_Value');
                $response = (new HttpCurlCalls)->curlCall($apiURL, json_encode($request), 'POST', ['Content-Type: application/json']);

                if($response->status == "Success")
                {
                    $proceedParams = ($listingMode == 1) ? $this->singleListing($response, $orders) : $this->groupedListing($response, $orders);

                    foreach($proceedParams as $param)
                    {
                        $resp[] = DB::select(
                            'CALL insertSettlementData(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                            [
                                json_encode($param['orderDetails']),
                                $param['amount_due'],
                                $param['sale_proceeds'],
                                $param['expenses'],
                                $param['refunds'],
                                $param['storegroup'],
                                $param['BranchId'],
                                $param['BankId'],
                                $param['BankAccountNo'],
                                $param['IFSC'],
                                $param['uuid'],
                                $param['AccountName'],
                                $param['StoreEmail'],
                                $param['PgType'],
                                $param['PgAccountId'],
                            ]
                        );
                    }
                }
                else
                {
                    Order::whereIn('order_id', $orderIds)->where('settlementStatus', 1)->update(['settlementStatus' => 0]);
                    Log::error("MerchantSettlements: settlement API returned non-success status");
                }
            }
            ProcessLock::updateColData("BizAPI_MerchantSettlements", 0);
        }
        catch (\Exception $e)
        {
            Log::error("MerchantSettlements ERROR => " . $e->getMessage(), ['exception' => $e]);
            ProcessLock::updateColData("BizAPI_MerchantSettlements", 0);
        }
    }

    private function nullHandler($item, $default = "")
    {
        if($item == "" || $item == NULL)
        {
            return $default;
        }
        return $item;
    }
    private function groupedListing($response, $orders)
    {
        $proceedParams = [];
        foreach ($response as $data)
        {
            if(is_null(@$data->BranchId))
            {
                continue;
            }
            $proceedParams[$data->BranchId]['BankId'] = $this->nullHandler(@$data->BankId, 0);
            $proceedParams[$data->BranchId]['BankAccountNo'] = $this->nullHandler(@$data->BankAccountNo, "");
            $proceedParams[$data->BranchId]['IFSC'] = $this->nullHandler(@$data->IFSC, "");
            $proceedParams[$data->BranchId]['BranchId'] = $data->BranchId;
            $proceedParams[$data->BranchId]['AccountName'] = $data->AccountName;
            $proceedParams[$data->BranchId]['StoreEmail'] = $data->StoreEmail;

            $proceedParams[$data->BranchId]['PgType'] = $this->nullHandler(@$data->PgType, 0);
            $proceedParams[$data->BranchId]['PgAccountId'] = $this->nullHandler(@$data->PgAccountId, "");

            $found = array_filter($orders->toArray(), function($v, $k) use ($data)
            {
                return ($v['order_order_id'] == $data->entity_id);
            }, ARRAY_FILTER_USE_BOTH);
            $found = array_values($found);
            $proceedParams[$data->BranchId]['storegroup'] = @$found[0]['branch_details']['br_storeGroup'];
            $proceedParams[$data->BranchId]['uuid']  = (@$proceedParams[$data->BranchId]['uuid']) ?? (DB::select('SELECT UUID() as uuid')[0]->uuid);;

            $orderID = array_values(array_filter(array_map(function($order) use ($data) {
                if($order["order_order_id"] == $data->entity_id)
                {
                    return $order["order_id"];
                }
            }, $orders->toArray())));

            $proceedParams[$data->BranchId]['orderDetails'][] = [
                'order_id'          => @$orderID[0],
                'ms_ref_id'         => $proceedParams[$data->BranchId]['uuid'],
                'amount_due'        => ($data->amountdue) ?? 0,
                'sale_proceeds'     => ($data->saleproceedes) ?? 0,
                'expenses'          => ($data->expenses) ?? 0,
                'refunds'           => ($data->refund) ?? 0
            ];
            $proceedParams[$data->BranchId]['amount_due'] = (@$proceedParams[$data->BranchId]['amount_due'] ?? 0) + ($data->amountdue ?? 0);
            $proceedParams[$data->BranchId]['sale_proceeds'] = (@$proceedParams[$data->BranchId]['sale_proceeds'] ?? 0) + ($data->saleproceedes ?? 0);
            $proceedParams[$data->BranchId]['expenses'] = (@$proceedParams[$data->BranchId]['expenses'] ?? 0) + ($data->expenses ?? 0);
            $proceedParams[$data->BranchId]['refunds'] = (@$proceedParams[$data->BranchId]['refunds'] ?? 0) + ($data->refund ?? 0);
        }
        return $proceedParams;
    }
    private function singleListing($response, $orders)
    {
        $proceedParams = [];
        foreach ($response as $data)
        {
            if(is_null(@$data->BranchId))
            {
                continue;
            }
            $params = [];
            $params['BankId'] = $this->nullHandler(@$data->BankId, 0);
            $params['BankAccountNo'] = $this->nullHandler(@$data->BankAccountNo, "");
            $params['IFSC'] = $this->nullHandler(@$data->IFSC, "");
            $params['BranchId'] = $data->BranchId;
            $params['AccountName'] = $data->AccountName;
            $params['StoreEmail'] = $data->StoreEmail;

            $params['PgType'] = $this->nullHandler(@$data->PgType, 0);
            $params['PgAccountId'] = $this->nullHandler(@$data->PgAccountId, "");

            $found = array_filter($orders->toArray(), function($v, $k) use ($data)
            {
                return ($v['order_order_id'] == $data->entity_id);
            }, ARRAY_FILTER_USE_BOTH);
            $found = array_values($found);
            $params['storegroup'] = @$found[0]['branch_details']['br_storeGroup'];
            $params['uuid']  = DB::select('SELECT UUID() as uuid')[0]->uuid;

            $orderID = array_values(array_filter(array_map(function($order) use ($data) {
                if($order["order_order_id"] == $data->entity_id)
                {
                    return $order["order_id"];
                }
            }, $orders->toArray())));
            $params['orderDetails'][] = [
                'order_id'          => @$orderID[0],
                'ms_ref_id'         => $params['uuid'],
                'amount_due'        => ($data->amountdue) ?? 0,
                'sale_proceeds'     => ($data->saleproceedes) ?? 0,
                'expenses'          => ($data->expenses) ?? 0,
                'refunds'           => ($data->refund) ?? 0
            ];
            $params['amount_due'] = (@$params['amount_due'] ?? 0) + ($data->amountdue ?? 0);
            $params['sale_proceeds'] = (@$params['sale_proceeds'] ?? 0) + ($data->saleproceedes ?? 0);
            $params['expenses'] = (@$params['expenses'] ?? 0) + ($data->expenses ?? 0);
            $params['refunds'] = (@$params['refunds'] ?? 0) + ($data->refund ?? 0);

            $proceedParams[] = $params;
        }
        return $proceedParams;
    }
}
