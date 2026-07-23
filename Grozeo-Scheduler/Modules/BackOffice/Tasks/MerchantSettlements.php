<?php

namespace BackOffice\Tasks;

use Illuminate\Support\Facades\DB;
use App\Models\{
    Order,
    Branch
};
use App\Helpers\HttpCurlCalls;
use Aws\DynamoDb\DynamoDbClient;
use BackOffice\Models\MerchantSettlements as MSModel;

class MerchantSettlements
{
    public function __invoke()
    {
        try
        {
            $settleDate = date('Y-m-d');
            $orders = Order::select('order_id', 'order_order_id', 'order_branch_id', 'settlement_date')
                ->where([
                    ['settlement_date', $settleDate],
                    ['settlementStatus', 0],
                    ['status_id', 18]
                ])
                ->with('branchDetails', 'branchDetails.storegroup')
                ->get();
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
                    $proceedParams = [];
                    foreach ($response->data as $data)
                    {
                        $ordData = Order::where('order_order_id', $data->entity_id)->first();
                        if($data->BankId && $data->BankAccountNo && $data->IFSC)
                        {
                            $proceedParams[$data->BranchId]['BankId'] =$data->BankId;
                            $proceedParams[$data->BranchId]['BankAccountNo'] =$data->BankAccountNo;
                            $proceedParams[$data->BranchId]['IFSC'] =$data->IFSC;
                            $proceedParams[$data->BranchId]['BranchId'] =$data->BranchId;
                            $proceedParams[$data->BranchId]['AccountName'] =$data->AccountName;
                            $proceedParams[$data->BranchId]['StoreEmail'] =$data->StoreEmail;

                            $found = array_filter($orders->toArray(), function($v, $k) use ($data)
                            {
                                return ($v['order_order_id'] == $data->entity_id);
                            }, ARRAY_FILTER_USE_BOTH); 
                            $found = array_values($found);
                            $proceedParams[$data->BranchId]['storegroup'] = @$found[0]['branch_details']['br_storeGroup'];
                            $proceedParams[$data->BranchId]['uuid']  = (@$proceedParams[$data->BranchId]['uuid']) ?? (DB::select('SELECT UUID() as uuid')[0]->uuid);

                            $proceedParams[$data->BranchId]['orderDetails'][] = [
                                'order_id'          => $ordData->order_id,
                                'ms_ref_id'         => $proceedParams[$data->BranchId]['uuid'],
                                'amount_due'        => ($data->amountdue) ?? 0,
                                'sale_proceeds'     => ($data->saleproceedes) ?? 0,
                                'expenses'          => ($data->expenses) ?? 0,
                                'refunds'           => ($data->refund) ?? 0
                            ];
                        }
                    }

                    foreach($proceedParams as $param)
                    {
                        $qry = "CALL insertSettlementData('".json_encode($param['orderDetails'])."', {$param['storegroup']}, {$param['BranchId']}, {$param['BankId']}, '{$param['BankAccountNo']}', '{$param['IFSC']}', '{$param['uuid']}', '{$param['AccountName']}', '{$param['StoreEmail']}')";
                        $resp[] = DB::select($qry);
                    }
                }
            }

            return response()->json([
                'outs'      => $proceedParams,
                '$qry'      => $qry,
                'response'  => $resp,
            ]);
        }
        catch (\Exception $e)
        {
            info("MerchantSettlements ERROR => ".$e->getMessage());
        }
    }
}