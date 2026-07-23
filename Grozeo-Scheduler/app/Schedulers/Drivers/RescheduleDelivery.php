<?php

namespace App\Schedulers\Drivers;

use App\Models\{
    QugeoOrder,
    ProcessLock,
    Drivers\QugeoDriver,
    Drivers\QugeoDriverLog,
    Drivers\QugeoOrderDetails,
    Drivers\QugeoScheduleSlots
};
use App\Status\{
    QugeoStatus,
    DriverDeliveryStatus
};
use App\Helpers\HttpCurlCalls;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;

class RescheduleDelivery
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
            $params = [
                'TableName'                 => config('aws.prefix').'QugeoOrderDetails',
                'FilterExpression'          => 'IsClosed = :IsClosed AND OrderStatus BETWEEN :OrderStatus1 AND :OrderStatus2',
                'ExpressionAttributeValues' => [
                    ':IsClosed'         => ['N' => "0"],
                    ':OrderStatus1'     => ['N' => (string)QugeoStatus::ORDER_DELIVERY_POLL_REJECTED_DLS_ID],
                    ':OrderStatus2'     => ['N' => (string)QugeoStatus::ORDER_DELIVERY_POLL_NORESP_DLS_ID]
                ],
            ];
            $orderDetails = [];
            do
            {
                $result = $this->dynamoClient->scan($params);
                $orderDetails = array_merge($orderDetails, $result['Items']);
                $params['ExclusiveStartKey'] = $result['LastEvaluatedKey'];
            } while (!empty($params['ExclusiveStartKey']));

            foreach ($orderDetails as $odetails)
            {
                $orderID = $odetails['orderid']['S'];
                $qugeoOrder = QugeoOrder::where("quor_id", $odetails['quor_id']['N'])->first();
                if ($odetails['ManuallyPushed']['N'] == 0)
                {
                    $createPoll = $this->createNewPoll($odetails);
                    if($createPoll)
                    {
                        $this->updatePolledOrder($odetails);
                        QugeoOrder::where("quor_id", $details['quor_id']['N'])
                        ->whereNotIn('quor_Status', [QugeoStatus::ORDER_DELIVERY_COMPLETED_DLS_ID, QugeoStatus::ORDER_DELIVERY_MARKED_DLS_ID])
                        ->update([
                            "quor_UpdateOn"                 => now(),
                            "quor_QugeoPickupDDBOrderId"    => $odetails['orderid']['S'],
                            "quor_Status"                   => QugeoStatus::ORDER_DELIVERY_POLLED_DLS_ID
                        ]);
                    }
                    else
                    {
                        $this->handoverForManualScheduling($orderID, $qugeoOrder);
                    }
                }
                else
                {
                    $this->handoverForManualScheduling($orderID, $qugeoOrder);
                }
            }
        }
        catch (\Exception $e)
        {
            info("RescheduleDelivery SCHEDULER => {$e->getMessage()}");
            info($e);
            ProcessLock::updateColData("BizAPI_RescheduleDelivery", 1);
        }
    }
    
    private function handoverForManualScheduling($orderID, $book)
    {
        $updateQugeo = QugeoOrder::where('quor_id', $book->quor_id)
        ->whereNotIn("quor_Status", [15, 38])
        ->update([
            "quor_UpdateOn"         => date('Y-m-d H:i:s'),
            "quor_PickupToBeManual" => 1,
            "quor_Type"             => 0,
            "quor_Status"           => DriverDeliveryStatus::PICKUP_AT_ORIGIN
        ]);
        $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
        $result = $dynamoClient->updateItem([
            'TableName'                 => config('aws.prefix').'QugeoOrderDetails',
            'Key'                       => [
                'orderid'      => ['S' => $orderID]
            ],
            'ExpressionAttributeNames' => [
                '#IsClosed' => 'IsClosed',
            ],
            'ExpressionAttributeValues' => [
                ':IsClosed' => ['N' => '1']
            ],
            'UpdateExpression'          => 'SET #IsClosed=:IsClosed'
        ]);
    }

    private function updatePolledOrder($details)
    {
        $result = $this->dynamoClient->updateItem([
            'TableName'                 => config('aws.prefix').'QugeoOrderDetails',
            'Key'                       => [
                'orderid'      => $details['orderid']['S']
            ],
            'ExpressionAttributeNames' => [
                '#OrderStatus' => 'OrderStatus',
            ],
            'ExpressionAttributeValues' => [
                ':OrderStatus' => ['N' => (string)QugeoStatus::ORDER_DELIVERY_POLLED_DLS_ID]
            ],
            'UpdateExpression'          => 'SET #OrderStatus=:OrderStatus'
        ]);
    }
    private function createNewPoll($details)
    {
        $qugeoOrder = QugeoOrder::with('transferOrder', 'transferOrder.order', 'branch')
        ->where("quor_id", $details['quor_id']['N'])
        ->first();
        if(@$qugeoOrder->quor_TransferOrder_Type == 1)
        {
            $deliveryRule = DB::table('retaline_delivery_rules')->select("rdr_calculationMode", "is_default", "rdr_ruleFor", "rdr_ruleForId", "rdr_storeGroupId")->where('rdr_id', $qugeoOrder->transferOrder->order->delivery_rule_id)->first();
            
            $rsnopickupLat = (@$details['pickupLat']['N'] ? $details['pickupLat']['N'] : @$details['pickupLat']['S']);
            $rsnopickupLng = (@$details['pickupLng']['N'] ? $details['pickupLng']['N'] : @$details['pickupLng']['S']);
            
            $onlineDrivers = $this->getOnlineDrivers($rsnopickupLat, $rsnopickupLng, $qugeoOrder->branch->br_storeGroup);

            if(@$deliveryRule->rdr_storeGroupId == 0)
            {
                $ownDrivers = $this->getOnlineDrivers($rsnopickupLat, $rsnopickupLng, 0);
                $onlineDrivers = array_merge($onlineDrivers, $ownDrivers);
            }
            $bestDriver = $this->getBestDriver($onlineDrivers, $details['orderid']['S']);
            return $bestDriver;
        }
    }
    private function getBestDriver($drivers, $orderID)
    {
        foreach ($drivers as $key => $driver)
        {
            if($this->isQueuedVehicle($orderID, $driver['apikey']) > 0)
            {
                unset($drivers[$key]);
            }
            elseif ($this->hasLivePoll($driver['apikey']) > 0)
            {
                unset($drivers[$key]);
            }
        }
        if($drivers)
        {
            $sortedList = sortedData($drivers, 'distance');
            return @$sortedList[0];
        }
        return false;
    }
    private function isQueuedVehicle($orderID, $apiKey)
    {
        $params = [
            'TableName'                 => config('aws.prefix').'QugeoOrderPollingDetails',
            'FilterExpression'          => 'orderid = :orderid AND apikey = :apikey AND ispickup = :ispickup',
            'ExpressionAttributeValues' => [
                ':orderid'  => ['S' => (string)$orderID],
                ':apikey'   => ['S' => $apiKey],
                ':ispickup' => ['N' => '1']
            ],
        ];
        $pollingAvailable = $this->dynamoClient->scan($params);

        return (@$pollingAvailable->toArray()['Count'] ? $pollingAvailable->toArray()['Count'] : 0);
    }
    private function hasLivePoll($apiKey)
    {
        $params = [
            'TableName'                 => config('aws.prefix').'QugeoOrderPollingDetails',
            'FilterExpression'          => 'apikey = :apikey AND isclosed = :isclosed',
            'ExpressionAttributeValues' => [
                ':apikey'   => ['S' => $apiKey],
                ':isclosed' => ['N' => '0']
            ],
        ];
        $hasLivePolls = $this->dynamoClient->scan($params);

        return (@$hasLivePolls->toArray()['Count'] ? $hasLivePolls->toArray()['Count'] : 0);
    }
    private function getOnlineDrivers($orderLat, $orderLng, $storegroup)
    {
        $dist = config('drivers.pickup_distance') ?? 15;
        $degreeMatrix = getDegreeMatrix($orderLat, $orderLng, $dist);

        $lat1 = (@$degreeMatrix['lat1']) ?? 0;
        $lat2 = (@$degreeMatrix['lat2']) ?? 0;
        $lon1 = (@$degreeMatrix['lon1']) ?? 0;
        $lon2 = (@$degreeMatrix['lon2']) ?? 0;
        $filterExpression = "Is_Live = :isLive AND Latitude BETWEEN :Latitude1 AND :Latitude2 AND Longitude BETWEEN :Longitude1 AND :Longitude2 AND MarkedNextBkId = :MarkedNextBkId AND IsEngaged = :IsEngaged AND isallowAutoSchedule = :isallowAutoSchedule AND createdBy = :createdBy AND sourceId = :sourceId";
        $attributeValues = [
            ':isLive'               => ['N' => '1'],
            ':MarkedNextBkId'       => ['N' => '0'],
            ':IsEngaged'            => ['N' => '0'],
            ':isallowAutoSchedule'  => ['N' => '1'],
            ':Latitude1'            => ['N' => (string)$lat1],
            ':Latitude2'            => ['N' => (string)$lat2],
            ':Longitude1'           => ['N' => (string)$lon1],
            ':Longitude2'           => ['N' => (string)$lon2],
            ':createdBy'            => ['N' => '1'],
            ':sourceId'             => ['N' => (string)$storegroup],

        ];

        $params = [
            'TableName'                 => config('aws.prefix').'QugeoLiveVehicles',
            'ProjectionExpression'      => 'apikey, v_id, v_no, Latitude, Longitude, v_capacity, CurrentLoadedWeight, RatePerKm, AWS_SNS_ARN, DeliveryRange, IsEngaged, MarkedNextBkId, MarkedNextBrId, FCM_ID, DriverPhone, DriverBranchId, createdBy, sourceId, Home_Latitude, Home_Longitude',
            'FilterExpression'          => $filterExpression,
            'ExpressionAttributeValues' => $attributeValues,
        ];

        $liveVehicles = [];
        do
        {
            $result = $this->dynamoClient->scan($params);
            $liveVehicles = array_merge($liveVehicles, $result['Items']);
            $params['ExclusiveStartKey'] = $result['LastEvaluatedKey'];
        } while (!empty($params['ExclusiveStartKey']));
        $vehicleList = [];
        foreach ($liveVehicles as $vehicle)
        {
            $totalDistance = GetDrivingDistance($orderLat, $vehicle['Latitude']['N'], $orderLng, $vehicle['Longitude']['N']);
            $coverageDistance = GetDrivingDistance($orderLat, $vehicle['Home_Latitude']['N'], $orderLng, $vehicle['Home_Longitude']['N']);
            $deliveryRange = $vehicle['DeliveryRange']['N'];
            if($coverageDistance <= $deliveryRange)
            {
                $vehicleList[] = [
                    'apikey'                => $vehicle['apikey'][''],
                    'v_No'                  => $vehicle['v_no']['S'],
                    'distance'              => $totalDistance,
                    'capacity'              => $vehicle['v_capacity']['N'],
                    'CurrentLoadedWeight'   => $vehicle['CurrentLoadedWeight']['N'],
                    'RatePerKm'             => $vehicle['RatePerKm']['N'],
                    'AWS_SNS_ARN'           => $vehicle['AWS_SNS_ARN']['NULL'],
                    'DeliveryRange'         => $vehicle['DeliveryRange']['N'],
                    'FCM_ID'                => $vehicle['FCM_ID']['S'],
                    'DriverPhone'           => $vehicle['DriverPhone']['S']
                ];
            }
        }
        return $vehicleList;
    }
}