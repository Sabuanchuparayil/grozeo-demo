<?php

namespace App\Schedulers;

use App\Schedulers\PostingScheduler\{
    FinascopPosting,
    CostDistribution,
    RestaurantAutoposting,
    AutopostingCalculations
};
use App\Models\ProcessLock;
use Aws\DynamoDb\DynamoDbClient;

class PostingScheduler
{
    public function __invoke()
    {
        try
        {
            $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));

            $params = [
                'TableName'                 => config('aws.prefix').'named_events',
                'FilterExpression'          => 'neStatus = :statCond AND (tstamp < :timeStampDiff)',
                'ExpressionAttributeValues' => [
                    ':statCond'         => ['S' => '0'],
                    ':timeStampDiff'    => ['S' => date('Y-m-d H:i:s', strtotime("-3 minutes"))]
                ],
            ];
            $eventList = [];
            do
            {
                $result = $dynamoClient->scan($params);
                $eventList = array_merge($eventList, $result['Items']);
                $params['ExclusiveStartKey'] = $result['LastEvaluatedKey'];
            } while (!empty($params['ExclusiveStartKey']));

            array_reverse($eventList);
            
            if(!empty($eventList))
            {
                foreach($eventList as $event)
                {
                    (new RestaurantAutoposting)->addAutopostingDetails($event['order_id']['S'], $event['finascopEventRefId']['S']);
                    (new AutopostingCalculations)->addAutopostingDetails($event['order_id']['S'], $event['finascopEventRefId']['S']);
                    (new CostDistribution)->costDistribution($event['order_id']['S'], $event['finascopEventRefId']['S'], $event['storeGroupId']['S']);
                    (new FinascopPosting)->finascopPosting($event['order_id']['S'], $event['finascopEventRefId']['S'], $event['storeGroupId']['S']);

                    $result = $dynamoClient->updateItem([
                        'TableName'                 => config('aws.prefix').'named_events',
                        'Key'                       => [
                            'uuid'      => $event['uuid'],
                            'tstamp'    => $event['tstamp']
                        ],
                        'ExpressionAttributeNames' => [
                            '#neStatus' => 'neStatus',
                        ],
                        'ExpressionAttributeValues' => [
                            ':neStatus' => ['S' => '1']
                        ],
                        'UpdateExpression'          => 'SET #neStatus=:neStatus'
                    ]);
                }
            }
            ProcessLock::updateColData("BizAPI_PostingScheduler", 0);
        }
        catch (\Exception $e)
        {
            info("PostingScheduler ERROR => ".$e->getMessage());
            ProcessLock::updateColData("BizAPI_PostingScheduler", 1);
            return [];
        }
    }
}