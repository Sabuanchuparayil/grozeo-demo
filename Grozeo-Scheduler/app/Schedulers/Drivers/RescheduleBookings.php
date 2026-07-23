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

class RescheduleBookings
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
                'TableName'                 => config('aws.prefix').'QugeoOrderDetails',
                'FilterExpression'          => 'IsClosed = :IsClosed AND OrderStatus BETWEEN :OrderStatus1 AND :OrderStatus2 AND updateddatetime < :updateddatetime',
                'ExpressionAttributeValues' => [
                    ':IsClosed'         => ['N' => '0'],
                    ':OrderStatus1'     => ['N' => (string)QugeoStatus::ORDER_PICKUP_POLL_REJECTED_DLS_ID],
                    ':OrderStatus2'     => ['N' => (string)QugeoStatus::ORDER_PICKUP_POLL_NORESP_DLS_ID],
                    ':updateddatetime'  => ['N' => (string)$valdateTime]
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
                            "quor_LastScheduleRun"          => now(),
                            "quor_QugeoPickupDDBOrderId"    => $odetails['orderid']['S'],
                            "quor_Status"                   => QugeoStatus::ORDER_PICKUP_POLLED_DLS_ID
                        ]);
                    }
                    else
                    {
                        QugeoOrder::where("quor_id", $odetails['quor_id']['N'])
                        ->whereNotIn('quor_Status', [QugeoStatus::ORDER_DELIVERY_COMPLETED_DLS_ID, QugeoStatus::ORDER_DELIVERY_MARKED_DLS_ID])
                        ->update([
                            "quor_UpdateOn"                 => now(),
                            "quor_LastScheduleRun"          => now()
                        ]);
                        $this->setDeffredPickupSchedule($orderID, $qugeoOrder);
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
            info("RescheduleBookings SCHEDULER => {$e->getMessage()}");
            info($e);
            ProcessLock::updateColData("BizAPI_RescheduleBookings", 1);
        }
    }
    
    private function setDeffredPickupSchedule($orderID, $book) //isPickup=true
    {
        $manualScheduleMax = config("drivers.manual_schedule_max") ?? 200;
        $now = date('Y-m-d H:i:s');
        if ((strtotime($now) - strtotime($book->quor_FirstScheduleRun)) > ($manualScheduleMax))
        {
            $this->handoverForManualScheduling($orderID, $book);
            return;
        }
        else
        {
            $scheduletime = $book->quor_ScheduleOpeningTime;
            if((strtotime($scheduletime) - strtotime($now)) > 0)
            {
                return;
            }
        }
        if (config("drivers.secondary_candidate_enabled") == 1)
        {
            $marked = $this->FindandMarkProspectiveCandidates($orderID, $book);
        }
        $pickup = $this->setPickup($orderID, $book);
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
    private function FindandMarkProspectiveCandidates($orderID, $book)
    {
        $availableVehicles = $this->getSecondaryQugeoCandidates($book->pickupLat, $book->pickupLng);
        if($availableVehicles)
        {
            $candidate = $this->getBestSecondaryQugeoCandidate($availableVehicles, $orderID);
            if($candidate)
            {
                $MarkedNextBkId = (@$book->MarkedNextBkId) ?? '0';
                $MarkedNextBrId = (@$book->MarkedNextBkId) ?? '0';
                $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
                $result = $dynamoClient->updateItem([
                    'TableName'                 => config('aws.prefix').'QugeoOrderDetails',
                    'Key'                       => [
                        'orderid'      => ['S' => $orderID]
                    ],
                    'ExpressionAttributeNames' => [
                        '#MarkedNextBkId' => 'MarkedNextBkId',
                        '#MarkedNextBrId' => 'MarkedNextBrId',
                    ],
                    'ExpressionAttributeValues' => [
                        ':MarkedNextBkId' => ['N' => (string)$MarkedNextBkId],
                        ':MarkedNextBrId' => ['S' => (string)$MarkedNextBkId]
                    ],
                    'UpdateExpression'          => 'SET #MarkedNextBkId=:MarkedNextBkId,  #MarkedNextBrId=:MarkedNextBrId'
                ]);
                return true;
            }
        }
        return false;
    }
    private function getSecondaryQugeoCandidates($orderLat, $orderLng)
    {
        $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
        $dist = config('drivers.pickup_distance') ?? 15;
        $degreeMatrix = getDegreeMatrix($orderLat, $orderLng, $dist);

        $lat1 = (@$degreeMatrix['lat1']) ?? 0;
        $lat2 = (@$degreeMatrix['lat2']) ?? 0;
        $lon1 = (@$degreeMatrix['lon1']) ?? 0;
        $lon2 = (@$degreeMatrix['lon2']) ?? 0;
        $filterExpression = "Is_Live = :isLive AND OnJobCompletionLatitude BETWEEN :Latitude1 AND :Latitude2 AND OnJobCompletionLongitude BETWEEN :Longitude1 AND :Longitude2 AND MarkedNextBkId = :MarkedNextBkId AND IsEngaged = :IsEngaged";
        $attributeValues = [
            ':isLive'               => ['N' => '1'],
            ':MarkedNextBkId'       => ['N' => '0'],
            ':IsEngaged'            => ['N' => '0'],
            ':Latitude1'            => ['N' => (string)$lat1],
            ':Latitude2'            => ['N' => (string)$lat2],
            ':Longitude1'           => ['N' => (string)$lon1],
            ':Longitude2'           => ['N' => (string)$lon2],
        ];

        $params = [
            'TableName'                 => config('aws.prefix').'QugeoLiveVehicles',
            'ProjectionExpression'      => 'apikey, v_id, v_no, Latitude, Longitude, v_capacity, CurrentLoadedWeight, RatePerKm, AWS_SNS_ARN, DeliveryRange, IsEngaged, MarkedNextBkId, MarkedNextBrId, FCM_ID, DriverPhone, DriverBranchId, createdBy, sourceId, Home_Latitude, Home_Longitude',
            'FilterExpression'          => $filterExpression,
            'ExpressionAttributeValues' => $attributeValues,
        ];

        $vehicleList = [];
        do
        {
            $result = $dynamoClient->scan($params);
            $vehicleList = array_merge($vehicleList, $result['Items']);
            $params['ExclusiveStartKey'] = $result['LastEvaluatedKey'];
        } while (!empty($params['ExclusiveStartKey']));

        array_reverse($vehicleList);
        $vehicleDetails= false;
        array_reverse($vehicleList);
        if(count($vehicleList) > 0)
        {
            foreach ($vehicleList as $key => $value)
            {
                foreach($value as $k => $v)
                {
                    $vehicleList[$key][$k] = array_values($v)[0];
                }
            }
            $vehicleDetails = [];
            foreach ($vehicleList as $vl)
            {
                $distance = GetDrivingDistance($orderLat, $vl['OnJobCompletionLatitude'], $orderLng, $vl['OnJobCompletionLongitude']);
                $vehicleDetails[] = [
                    'apikey'                => $vl['apikey'],
                    'v_No'                  => $vl['v_no'],
                    'distance'              => $distance,
                    'capacity'              => $vl['v_capacity'],
                    'CurrentLoadedWeight'   => $vl['CurrentLoadedWeight'],
                    'RatePerKm'             => $vl['RatePerKm'],
                    'AWS_SNS_ARN'           => $vl['AWS_SNS_ARN'],
                    'DeliveryRange'         => $vl['DeliveryRange']
                ];
            }
        }
        return $vehicleDetails;
    }
    private function getBestSecondaryQugeoCandidate($availableVehicles, $orderID)
    {
        foreach ($availableVehicles as $key => $av)
        {
            if($this->isQueuedVehicle($orderID, $av['apikey']) > 0)
            {
                unset($availableVehicles[$key]);
            }
        }
        if($availableVehicles)
        {
            $sortedList = sortedData($availableVehicles, 'distance');
            return @$sortedList[0];
        }
        return false;
    }
    private function setPickup($orderID, $book)
    {
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');
        $manualScheduleBy = config('drivers.manual_schedule_by') ?? 100;
        $nextTime = date('His', strtotime("{$now} + {$manualScheduleBy} SECONDS"));

        $pickableToday = $this->isPickableToday($today, $nextTime);
        if($pickableToday > 0)
        {
            $nextTime = $this->getEarliestProcessingTime($nextTime);
            $scheduleTime = $today.' '.date('H:i:s', strtotime($nextTime));
            $endTime = date('Y-m-d H:i:s', strtotime("{$scheduleTime} + {$manualScheduleBy} SECONDS"));
            $this->updateSchedule($orderID, $scheduleTime, $now, $endTime, $book);
        }
        else
        {
            $pickupday = $this->getPickupableDay();
            $slot = $this->getPickupScheduleSlots(0);
            if($slot)
            {
                $pickuptime = date('H:i:s', strtotime($slot->starttime));
                $scheduleTime  = "{$pickupday} {$pickuptime}";
                $endTime = date('Y-m-d H:i:s', strtotime("{$scheduleTime} + {$manualScheduleBy} SECONDS"));
                $this->updateSchedule($orderID, $scheduleTime, $now, $endTime, $book);
            }
        }
    }
    private function isPickableToday($today, $nexttime)
    {	
        $holidays = $this->getHolidays($today, $today);
        if(!$holidays)
        {
            $getSlotTime = QugeoScheduleSlots::where('endtime', '>', $nexttime)->count();
            return $getSlotTime;
        }
        return 0;
    }
    private function getHolidays($start, $end)
    {
        $holidays = DB::select("SELECT UNIX_TIMESTAMP(holi_days) FROM holidays WHERE holi_days BETWEEN '{$start}' AND '{$end}' ORDER BY holi_days ASC");
        return (count($holidays) > 0) ? true : false;
    }
    private function getPickupableDay()
    {
        $pickupDays = $this->getPickupDays();
        $holidays = $this->getHolidays($pickupDays[0], $pickupDays[1]);

        $pickupday = $pickupDays[0];
				
        while (in_array(strtotime($pickupday), $holidays) && count($holidays) > 0)
        {
            $pickupday = date('Y-m-d', strtotime("{$pickupday} +1 day"));
        }
        return $pickupday;
    }
    private function getPickupDays()
    {
        $today = date('Y-m-d');
        $now = date("His");
        $scheduledSlots = $this->getPickupScheduleSlots($now);					
        if(!$scheduledSlots)
        {
            $today = date('Y-m-d', strtotime("+1 day"));				
        }
        $tillDate  = date('Y-m-d', strtotime("+30 days"));
        return [
            $today,
            $tillDate
        ];
    }
    public function getPickupScheduleSlots($startTime = 0)
    {
        $slotDetails = QugeoScheduleSlots::where('availabletill', $startTime)->orderBy('availabletill', 'ASC')->first();					
        return $slotDetails;
    }
    private function getEarliestProcessingTime($nexttime)
    {
        $slotTime = QugeoScheduleSlots::select('starttime')->whereBetween($nexttime, ['starttime', 'endtime'])->first();
        if(@$slotTime->starttime == "")
        {
            $slotTime = QugeoScheduleSlots::select('starttime')->where('starttime', $time)->latest()->first();
        }
        return @$slotTime->starttime;
    }
    private function updateSchedule($orderID, $scheduleTime, $now, $endTime, $book)
    {
        $updateOrder = QugeoOrder::whereRaw("(quor_id = {$orderID}) OR (quor_PickupPincode = {$book->PickupPincode} AND quor_PickupToBeManual = 0 AND quor_ScheduleOpeningTime BETWEEN {$now} AND {$endTime})")->update([
            'quor_ScheduleOpeningTime'  => $scheduleTime
        ]);
    }
    private function updatePolledOrder($details)
    {
        $result = $this->dynamoClient->updateItem([
            'TableName'                 => config('aws.prefix').'QugeoOrderDetails',
            'Key'                       => [
                'orderid'      => $details['orderid']
            ],
            'ExpressionAttributeNames' => [
                '#OrderStatus' => 'OrderStatus',
            ],
            'ExpressionAttributeValues' => [
                ':OrderStatus' => ['N' => (string)QugeoStatus::ORDER_PICKUP_POLLED_DLS_ID]
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