<?php

namespace App\Schedulers\Drivers;

use App\Models\{
    QugeoOrder,
    ProcessLock,
    Drivers\QugeoDriver
};
use App\Status\QugeoStatus;
use App\Helpers\HttpCurlCalls;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;

class ResponsePolls
{
    protected $dynamoClient;
    public function __construct()
    {
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
    }
    public function __invoke()
    {
        try
        {
            $pollRespTimeout = config('drivers.poll_response_timeout') ?? 180;
            $valdateTime = date("YmdHis", strtotime(date("YmdHis")) - $pollRespTimeout);
            $params = [
                'TableName'                 => config('aws.prefix').'QugeoOrderPollingDetails',
                'ProjectionExpression'      => 'pollingid, apikey, orderid, currentstatus, ispickup',
                'FilterExpression'          => 'isclosed = :isclosed AND createddatetime < :createddatetime',
                'ExpressionAttributeValues' => [
                    ':isclosed'         => ['N' => "0"],
                    ':createddatetime'  => ['N' => $valdateTime]
                ],
            ];
            $polledOrderDetails = [];
            do
            {
                $result = $this->dynamoClient->scan($params);
                $polledOrderDetails = array_merge($polledOrderDetails, $result['Items']);
                $params['ExclusiveStartKey'] = $result['LastEvaluatedKey'];
            } while (!empty($params['ExclusiveStartKey']));

            foreach ($polledOrderDetails as $pd)
            {
                $this->orderDetailsUpdate($pd);
                $this->updatePolledOrder($pd);
            }
        }
        catch (\Exception $e)
        {
            info("ResponsePolls SCHEDULER => {$e->getMessage()}");
            info($e);
            ProcessLock::updateColData("BizAPI_ResponsePolls", 1);
        }
    }

    private function orderDetailsUpdate($polledDetails)
    {
        $polledOrdID = $polledDetails['orderid']['S'];
        $params = [
            'TableName'                 => config('aws.prefix').'QugeoOrderDetails',
            'ProjectionExpression'      => 'orderid, quor_id, IsPickup',
            'FilterExpression'          => 'orderid = :orderid',
            'ExpressionAttributeValues' => [
                ':orderid'         => ['S' => $polledOrdID]
            ],
        ];
        $result = $this->dynamoClient->scan($params);
        if(@$result['Items'])
        {
            $orderDetails = reset($result['Items']);
            if($orderDetails['IsPickup']['N'] == 1)
            {
                QugeoOrder::where('quor_id', $orderDetails['quor_id']['N'])
                ->whereNotIn('quor_Status', $this->statusChecker())
                ->update([
                    'quor_UpdateOn' => date('Y-m-d H:i:s'),
                    'quor_Status'   => QugeoStatus::ORDER_PICKUP_POLL_NORESP_DLS_ID
                ]);
                $updateStatus = QugeoStatus::ORDER_PICKUP_POLL_NORESP_DLS_ID;
            }
            else
            {
                QugeoOrder::where('quor_id', $orderDetails['quor_id']['N'])
                ->whereNotIn('quor_Status', [QugeoStatus::ORDER_DELIVERY_COMPLETED_DLS_ID, QugeoStatus::ORDER_DELIVERY_MARKED_DLS_ID])
                ->update([
                    'quor_UpdateOn' => date('Y-m-d H:i:s'),
                    'quor_Status'   => QugeoStatus::ORDER_DELIVERY_POLL_NORESP_DLS_ID
                ]);
                $updateStatus = QugeoStatus::ORDER_DELIVERY_POLL_NORESP_DLS_ID;
            }
            $updateOrderDetails = $this->dynamoClient->updateItem([
                'TableName'                 => config('aws.prefix').'QugeoOrderDetails',
                'Key'                       => [
                    'orderid'       => ['S' => $polledOrdID]
                ],
                'ExpressionAttributeNames' => [
                    '#OrderStatus'  => 'OrderStatus',
                ],
                'ExpressionAttributeValues' => [
                    ':OrderStatus'  => ['N' => (string)$updateStatus]
                ],
                'UpdateExpression'          => 'SET #OrderStatus=:OrderStatus'
            ]);
        }
    }

    private function updatePolledOrder($polledDetails)
    {
        $updatePolledDetails = $this->dynamoClient->updateItem([
            'TableName'                 => config('aws.prefix').'QugeoOrderPollingDetails',
            'Key'                       => [
                'pollingid'      => $polledDetails['pollingid']
            ],
            'ExpressionAttributeNames' => [
                '#isclosed'         => 'isclosed',
                '#closedat'         => 'closedat',
                '#currentstatus'    => 'currentstatus'
            ],
            'ExpressionAttributeValues' => [
                ':isclosed'         => ['N' => '1'],
                ':closedat'         => ['S' => date("YmdHis")],
                ':currentstatus'    => ['S' => 'NORESPONSE']
            ],
            'UpdateExpression'          => 'SET #isclosed=:isclosed, #closedat=:closedat, #currentstatus=:currentstatus'
        ]);
    }

    private function statusChecker()
    {
        return [
            QugeoStatus::ORDER_DELIVERY_OUT_FOR_DELIVERY,
            QugeoStatus::ORDER_DELIVERY_COMPLETED_DLS_ID,
            QugeoStatus::ORDER_PICKUP_POLL_REJECTED_DLS_ID,
            QugeoStatus::ORDER_PICKUP_FLAGGED_TODST_DLS_ID,
            QugeoStatus::ORDER_DELIVERY_MARKED_DLS_ID
        ];
    }
}
