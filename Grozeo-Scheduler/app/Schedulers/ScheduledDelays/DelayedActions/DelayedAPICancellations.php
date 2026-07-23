<?php

namespace App\Schedulers\ScheduledDelays\DelayedActions;

use DateTime;
use App\Models\{
    Order,
    ProcessLock,
    FinanceDeliveryType,
    CourierDelivery\ShippingConsignment
};
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\PartnerOrderUpdateRepository;


class DelayedAPICancellations
{
    public function __invoke()
    {
        try
        {
            $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
            $params = [
                'TableName'                 => config('aws.prefix').'delayed_orders',
                'FilterExpression'          => '#statusAttr = :statusVal',
                'ExpressionAttributeNames' => [
                    '#statusAttr' => 'status'
                ],
                'ExpressionAttributeValues' => [
                    ':statusVal'  => ['N' => '5']
                ],
            ];
            $cancelledList = [];
            do
            {
                $result = $dynamoClient->scan($params);
                $cancelledList = array_merge($cancelledList, $result['Items']);
                $params['ExclusiveStartKey'] = $result['LastEvaluatedKey'];
            } while (!empty($params['ExclusiveStartKey']));
            array_reverse($cancelledList);
            
            if(!empty($cancelledList))
            {
                foreach ($cancelledList as $cl)
                {
                    $tpCancelDelivery = $this->cancelThirdPartyDelivery($cl);
                    if ($tpCancelDelivery !== false) {
                        $dynamoClient->updateItem([
                            'TableName'                 => config('aws.prefix').'delayed_orders',
                            'Key'                       => [
                                'uuid'      => $cl['uuid'],
                                'tstamp'    => $cl['tstamp']
                            ],
                            'ExpressionAttributeNames' => [
                                '#statusAttr' => 'status',
                            ],
                            'ExpressionAttributeValues' => [
                                ':statusVal' => ['N' => '16']
                            ],
                            'UpdateExpression'          => 'SET #statusAttr=:statusVal'
                        ]);
                    }
                }
            }
            ProcessLock::updateColData("BizAPI_DelayedAPICancellations", 0);
        }
        catch (\Exception $e)
        {
            info("DelayedAPICancellations ERROR => ".$e);
            ProcessLock::updateColData("BizAPI_DelayedAPICancellations", 1);
        }
    }

    private function cancelThirdPartyDelivery($details)
    {
        $orderOrderID = $details['orderOrderID']['N'];
        $partner = $details['modeMethod']['S'];
        $orderMethod = $details['orderMethod']['N'];
        if($partner != "")
        {
            $shipping = ($orderMethod == 3) ? config("courierpartners.{$partner}.sClass") : (($orderMethod == 1) ? config("expresspartners.{$partner}.sClass") : "");
            if($shipping != "")
            {
                $shipper = new $shipping();
                return $shipper->cancelConsignment($orderOrderID);
            }
        }
        return false;
    }
}