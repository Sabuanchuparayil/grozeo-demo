<?php

namespace BackOffice\Tasks;

use Illuminate\Support\Facades\DB;
use App\Models\Order;
use Aws\DynamoDb\DynamoDbClient;

class MerchantOrderIvrCalls
{
    public function __invoke()
    {
        $timeoutConfig = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name= 'IVR_ORDER_TIMEOUT'");
        $timeout = (@$timeoutConfig[0]->cfg_Value) ? $timeoutConfig[0]->cfg_Value : 30;

        $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));

        $orderList = $dynamoClient->scan([
            'TableName'             => config('aws.prefix').'merchant_order_ivr_log',
            'AttributesToGet'       => ['order_id'] 
        ]);
        $listedOrders = array_column(array_column($orderList['Items'], 'order_id'), 'S');

        $timeoutOrders = Order::select('order_id', 'order_order_id', 'order_branch_id', 'total', 'order_confirmed_on')->where([
            ['status_id', 7],
            [DB::raw('TIMESTAMPDIFF(MINUTE, `order_confirmed_on`, NOW())'), '>', $timeout]
        ])
        ->whereNotIn('order_id', $listedOrders)
        ->with('branchDetails:br_ID,br_Name,br_City,br_District,br_Email,br_Phone')
        ->with('deliveryAddress:customer_order_id,order_customer_name,order_customer_email,order_contact_no,order_city')
        ->orderBy('order_confirmed_on', 'DESC')->get();
        $x = 1;
        $outs = [];
        foreach ($timeoutOrders as $torder)
        {
            $uuid = date('YmdHis').str_pad($x, 6, "0", STR_PAD_LEFT);
            $x++;
            $dynamoClient->putItem([
                'TableName' => config('aws.prefix').'merchant_order_ivr_log',
                'Item'      => [
                    'uuid'              => ['S' => (string)$uuid],
                    'tstamp'            => ['S' => (string)date('Y-m-d H:i:s')],
                    'order_id'          => ['S' => (string)$torder->order_id],
                    'branch_name'       => ['S' => (string)$torder->branchDetails->br_Name],
                    'branch_email'      => ['S' => (string)$torder->branchDetails->br_Email],
                    'branch_phone'      => ['S' => (string)$torder->branchDetails->br_Phone],
                    'branch_city'       => ['S' => (string)$torder->branchDetails->br_City],
                    'branch_district'   => ['S' => (string)$torder->branchDetails->br_District],
                    'customer_name'     => ['S' => (string)$torder->deliveryAddress->order_customer_name],
                    'customer_email'    => ['S' => (string)$torder->deliveryAddress->order_customer_email],
                    'customer_phone'    => ['S' => (string)$torder->deliveryAddress->order_contact_no],
                    'customer_city'     => ['S' => (string)$torder->deliveryAddress->order_city],
                    'call_status'       => ['BOOL' => FALSE]
                ]
            ]);
            $outs[] = [
                'order_id'  => $torder->order_id,
                'uuid'      => $uuid
            ];
        }
        return response()->json($outs);
    }
}