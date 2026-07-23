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


class DelayedMerchantCancellations
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
                    ':statusVal'  => ['N' => '18']
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
                    try {
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
                                ':statusVal' => ['N' => '19'],
                                ':expectedVal' => ['N' => '18'],
                            ],
                            'UpdateExpression'          => 'SET #statusAttr=:statusVal',
                            'ConditionExpression'       => '#statusAttr = :expectedVal',
                        ]);
                    } catch (\Exception $e) {
                        continue;
                    }

                    $data = ["orderID" => $cl["orderOrderID"]["N"]];
                    (new PartnerOrderUpdateRepository)->orderCancel($data);

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
                            ':statusVal' => ['N' => '15']
                        ],
                        'UpdateExpression'          => 'SET #statusAttr=:statusVal'
                    ]);
                }
            }
            ProcessLock::updateColData("BizAPI_DelayedMerchantCancellations", 0);
        }
        catch (\Exception $e)
        {
            info("DelayedMerchantCancellations ERROR => ".$e);
            ProcessLock::updateColData("BizAPI_DelayedMerchantCancellations", 1);
        }
    }
}