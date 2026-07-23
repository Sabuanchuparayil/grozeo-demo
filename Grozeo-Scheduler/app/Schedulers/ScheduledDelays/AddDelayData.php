<?php
namespace App\Schedulers\ScheduledDelays;

use App\Models\DelayedOrderLog;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;

class AddDelayData
{
    public static function addData($details)
    {
        $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
        foreach ($details as $det)
        {
            $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
            $dynamoClient->putItem([
            'TableName' => config('aws.prefix').'delayed_orders',
                'Item'      => [
                    'uuid'              => ['S' => $uuid],
                    'tstamp'            => ['S' => date('Y-m-d H:i:s')],
                    'orderID'           => ['N' => (string)$det['orderID']],
                    'type'              => ['N' => (string)$det['type']],
                    'orderMethod'       => ['N' => (string)$det['orderMethod']],
                    'branchID'          => ['N' => (string)$det['branchID']],
                    'status'            => ['N' => (string)$det['status']],
                    'mode'              => ['S' => (string)$det['mode']],
                    'modeMethod'        => ['S' => $det['modeMethod']],
                    'orderDate'         => ['S' => $det['orderDate']],
                    'orderTotal'        => ['N' => (string)$det['orderTotal']],
                    'merchantDetails'   => ['S' => $det['merchantDetails']],
                    'customerDetails'   => ['S' => $det['customerDetails']],
                    'deliveryType'      => ['S' => (string)$det['deliveryType']],
                    'paymentMode'       => ['N' => (string)$det['paymentMode']],
                    'orderSubtotal'     => ['N' => (string)$det['orderSubtotal']],
                    'orderOrderID'      => ['N' => (string)$det['orderOrderID']],
                    'action'            => ['N' => '0'],
                    'skipDate'          => ['S' => ""]
                ]
            ]);
            DelayedOrderLog::create([
                'orderId'   => $det['orderID'],
                'type'      => $det['type'],
                'uuid'      => $uuid
            ]);
        }
    }
}