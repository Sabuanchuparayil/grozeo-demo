<?php

namespace App\Schedulers\Drivers;

use App\Models\{
    Branch,
    QugeoOrder,
    ProcessLock,
    Drivers\QugeoDriverLog,
    Drivers\QugeoOrderDetails,
    Drivers\QugeoScheduleSlots
};
use App\Helpers\HttpCurlCalls;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;
use App\Status\DriverDeliveryStatus;
use App\Traits\CanSendNotificationsToBoy;
use App\Events\DelayedOrderActions as DelayedOrderEvent;

class ScheduleNewBookings
{
    use CanSendNotificationsToBoy;
    public function __invoke()
    {
        try
        {
            $bookings = QugeoOrder::selectRaw($this->selectFields())->where([
                ["quor_slot_id", 0],
                ["quor_PickupToBeManual", 0],
                ["quor_ScheduleOpeningTime", "<=", date('Y-m-d H:i:s')],
                ["quor_Type", 1],
                ["quor_Status", DriverDeliveryStatus::PICKUP_AT_ORIGIN]
            ])->with('deliveryStatus')->orderBy('quor_CreatedOn', 'ASC')->get();
            if(!empty($bookings))
            {
                $this->orderBookingPolling($bookings);
            }

            ProcessLock::updateColData("BizAPI_ScheduleNewBookings", 0);
        }
        catch (\Exception $e)
        {
            info("ScheduleNewBookings SCHEDULER => {$e->getMessage()}");
            info($e);
            ProcessLock::updateColData("BizAPI_ScheduleNewBookings", 1);
        }
    }

    private function selectFields()
    {
        return "
            quor_id,
            quor_RefNo,
            quor_QugeoPickupDDBOrderId,
            quor_QugeoDeliveryDDBOrderId,
            quor_PickupToBeManual,
            quor_ScheduleOpeningTime,
            quor_Date,
            quor_PickupName,
            quor_PickupAddress,
            quor_PickupLocation,
            quor_PickupPhone,
            quor_PickupLat as pickupLat,
            quor_PickupLng as pickupLng,
            quor_PickupSMS,
            quor_PickupStage1Distance,
            quor_PickupStage2Distance,
            quor_DeliveryName,
            quor_DeliveryAddress,
            quor_DeliveryLocation,
            quor_DeliveryPhone,
            quor_DeliveryLat as deliveryLat,
            quor_DeliveryLng as deliveryLng,
            quor_DeliverySMS,
            quor_DeliveryStage1Distance,
            quor_DeliveryStage2Distance,
            quor_CreatedOn,
            quor_Status,
            quor_DistanceinKM,
            quor_LastScheduleRun,
            quor_PickupToBeManual,
            quor_ScheduleOpeningTime,
            quor_FirstScheduleRun,
            quor_PickupPincode,
            quor_DeliveryPincode,
            quor_Pickupbr_id,
            quor_Deliverybr_id,
            quor_AmountCollectible,
            quor_TransferOrder_id
        ";
    }
    private function orderBookingPolling($bookings)
    {
        foreach ($bookings as $book)
        {
            if($book->quor_PickupToBeManual == 1)
            {
                continue;
            }
            $consignment = QugeoOrderDetails::selectRaw('
                quod_id as detid,
                quor_RefNo as RefNo,
                quor_IsBarcode as Barcode
            ')->where('quor_id', $book->quor_id)->get();

            $orderID = $this->addQugeoOrderDetails($book, json_encode($consignment));
            if($orderID)
            {
                $createPoll = $this->createPoll($book, $orderID);

                $now = (@$book->quor_LastScheduleRun) ? $book->quor_LastScheduleRun : date('Y-m-d H:i:s');

                if($createPoll)
                {
                    QugeoOrder::where('quor_id', $book->quor_id)->update([
                        "quor_Type"                     => 1,
                        "quor_UpdateOn"                 => date('Y-m-d H:i:s'),
                        "quor_LastScheduleRun"          => $now,
                        "quor_QugeoPickupDDBOrderId"    => $orderID,
                        "quor_Status"                   => config("drivers.pickup_polled_status")
                    ]);
                    event(new DelayedOrderEvent(@$book->transferOrder->fstr_id, 5));
                }
                else
                {
                    QugeoOrder::where('quor_id', $book->quor_id)->update([
                        "quor_UpdateOn"                 => date('Y-m-d H:i:s'),
                        "quor_LastScheduleRun"          => $now
                    ]);
                    $this->setDeffredPickupSchedule($orderID, $book);
                }
            }
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
            $this->findandMarkProspectiveCandidates($orderID, $book);
        }
        $this->setPickup($orderID, $book);
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
        return  QugeoScheduleSlots::where('availabletill', $startTime)->orderBy('availabletill', 'ASC')->first();
    }
    private function updateSchedule($orderID, $scheduleTime, $now, $endTime, $book)
    {
        QugeoOrder::where(function ($query) use ($orderID, $book, $now, $endTime) {
            $query->where('quor_id', $orderID)
                ->orWhere(function ($q) use ($book, $now, $endTime) {
                    $q->where('quor_PickupPincode', $book->PickupPincode)
                        ->where('quor_PickupToBeManual', 0)
                        ->whereBetween('quor_ScheduleOpeningTime', [$now, $endTime]);
                });
        })->update([
            'quor_ScheduleOpeningTime'  => $scheduleTime
        ]);
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
    private function isPickableToday($today, $nexttime)
    {
        $holidays = $this->getHolidays($today, $today);
        if(!$holidays)
        {
            return QugeoScheduleSlots::where('endtime', '>', $nexttime)->count();
        }
        return 0;
    }
    private function getHolidays($start, $end)
    {
        $holidays = DB::select("SELECT UNIX_TIMESTAMP(holi_days) as ts FROM holidays WHERE holi_days BETWEEN ? AND ? ORDER BY holi_days ASC", [$start, $end]);
        return array_column($holidays, 'ts');
    }
    private function findandMarkProspectiveCandidates($orderID, $book)
    {
        $availableVehicles = $this->getSecondaryQugeoCandidates($book->pickupLat, $book->pickupLng);
        if($availableVehicles)
        {
            $candidate = $this->getBestSecondaryQugeoCandidate($availableVehicles, $orderID);
            if($candidate)
            {
                $markedNextBkId = (@$book->MarkedNextBkId) ?? '0';
                $markedNextBrId = (@$book->MarkedNextBkId) ?? '0';
                $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
                $result = $dynamoClient->updateItem([
                    'TableName'                 => config('aws.prefix').'QugeoOrderDetails',
                    'Key'                       => [
                        'orderid'      => ["S" => (string)$orderID]
                    ],
                    'ExpressionAttributeNames' => [
                        '#MarkedNextBkId' => 'MarkedNextBkId',
                        '#MarkedNextBrId' => 'MarkedNextBrId',
                    ],
                    'ExpressionAttributeValues' => [
                        ':MarkedNextBkId' => ['N' => (string)$markedNextBkId],
                        ':MarkedNextBrId' => ['S' => (string)$markedNextBkId]
                    ],
                    'UpdateExpression'          => 'SET #MarkedNextBkId=:MarkedNextBkId,  #MarkedNextBrId=:MarkedNextBrId'
                ]);
                return true;
            }
        }
        return false;
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
    private function getSecondaryQugeoCandidates($orderLat, $orderLng)
    {
        $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
        $dist = config('drivers.pickup_distance') ?? 15;
        $degreeMatrix = getDegreeMatrix($orderLng, $orderLat, $dist);

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
                'orderid'      => ["S" => (string)$orderID]
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
    private function createPoll($book, $orderID)
    {
        try
        {
            $deliveryRuleQuery = "SELECT is_default,br_rdrIdExpress,rdr_ruleFor FROM retaline_delivery_rules dr INNER JOIN finascop_branch b ON b.br_rdrIDExpress = dr. rdr_id AND b.br_ID = '{$book->quor_Pickupbr_id}' WHERE rdr_deliveryMode = 2";
            $deliveryRules = DB::select($deliveryRuleQuery);

            $getStore = Branch::select('br_storeGroup', 'br_Name')->where('br_ID', $book->quor_Pickupbr_id)->first();

            $vehicleListStore = $this->getVehicleList($book, @$getStore->br_storeGroup);
            $vehicleList = [];
            if($deliveryRules || (@$deliveryRules->rdr_ruleFor == 1 && @$deliveryRules->br_rdrIdExpress == 0))
            {
                $vehicleList = $this->getVehicleList($book);
            }
            if(!empty($vehicleListStore) && !empty($vehicleList))
            {
                $availableVehicles = array_merge($vehicleListStore, $vehicleList);
            }
            else
            {
                $availableVehicles = !empty($vehicleListStore) ? $vehicleListStore : $vehicleList;
            }
            if(empty($availableVehicles))
            {
                info("Drivers not available");
                return false;
            }
            $candidate = $this->getBestVehicle($availableVehicles, $orderID);
            if($candidate)
            {
                $pollID = sha1(microtime(true) . mt_rand(10000, 90000));
                $pushPolledJobs = $this->pushPolledJobs($candidate, $pollID, $orderID, $book);
                if($pushPolledJobs)
                {
                    $savedMessage = $this->savePollingDetails($pollID, $orderID, $candidate, $pushPolledJobs, $book);
                    return $savedMessage;
                }
            }
            return false;
        }
        catch (\Exception $e)
        {
            info("ADD createPoll Error");
            info($e);
            return false;
        }
    }
    private function savePollingDetails($pollID, $orderID, $candidate, $pushPolledJobs, $book)
    {
        $validate = date("Ymd");
        $validateTime = date("YmdHis");
        $withinRange = intval($candidate['DeliveryRange']) >= intval($book->TotalDistKM) ? "true" : "false";
        $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));

        $dynamoClient->putItem([
            'TableName' => config('aws.prefix').'QugeoOrderPollingDetails',
            'Item'      => [
                "pollingid"         => ['S' => $pollID],
                "apikey"            => ['S' => $candidate['apikey']],
                "orderid"           => ['S' => $orderID],
                "createddatetime"   => ['N' => (string)$validateTime],
                "createddate"       => ['N' => (string)$validate],
                "currentstatus"     => ['S' => 'POLLED'],
                "ispickup"          => ['N' => '1'],
                "isclosed"          => ['N' => '0'],
                "pollingdetails"    => ['S' => json_encode($pushPolledJobs)],
                "withinrange"       => ['S' => $withinRange]
            ]
        ]);
        return true;
    }
    private function pushPolledJobs($candidate, $pollID, $orderID, $book)
    {
        $message = [];
        $deliveryLocs = [];
        $orderIDs = [];
        $quor_QugeoPickupDDBOrderId = (@$book->quor_QugeoPickupDDBOrderId) ? $book->quor_QugeoPickupDDBOrderId : $orderID;

        $store = Branch::select('br_storeGroup', 'br_Name')->where('br_ID', $book->quor_Deliverybr_id)->first();
        $quor_DeliveryLocation = "{$book->quor_DeliveryAddress} {$book->quor_DeliveryPincode}";

        array_push($orderIDs, [
            "id"            => $quor_QugeoPickupDDBOrderId,
            "order"         => $book->quor_id,
            "orderNo"       => $book->quor_RefNo,
            "location"      => $quor_DeliveryLocation,
            "latitude"      => $book->quor_DeliveryLat,
            "longitude"     => $book->quor_DeliveryLng,
            "orderStatus"   => $book->deliveryStatus->dls_DelStatus,
            "statusId"      => $book->quor_Status
        ]);
        array_push($deliveryLocs, [
            "latitude"  => $book->quor_DeliveryLat,
            "longitude" => $book->quor_DeliveryLng,
            "location"  => $quor_DeliveryLocation
        ]);
        $createQugeoLog = QugeoDriverLog::create([
            "fcmid"                         => $candidate['FCM_ID'],
            "apikey"                        => $candidate['apikey'],
            "pollid"                        => $pollID,
            "mobile"                        => $candidate['DriverPhone'],
            "quorId"                        => $book->quor_id,
            "quor_QugeoPickupDDBOrderId"    => $quor_QugeoPickupDDBOrderId
        ]);

        $disttobr = "{$book->TotalDistKM} KM";
        $geoRecords = [
            "pickup"    => [
                "latitude"  => $book->pickupLat,
                "longitude" => $book->pickupLng,
                "location"  => $book->quor_PickupLocation,
                "address"   => $book->quor_PickupAddress,
                "mobile"    => $book->quor_PickupPhone
            ],
            "delivery"  => $deliveryLocs
        ];
        $message['data'] = array(
            "yourapikey"    => $candidate['apikey'],
            "msgid"         => $pollID,
            "msgtype"       => "NEW",
            "storeGroup"    => @$store->br_storeGroup,
            "orderid"       => json_encode($orderIDs),
            "orderCount"    => "1",
            "ispickuporder" => true,
            "details"       => json_encode($geoRecords)
        );
        $this->sendNotificationToDriver(
            config("drivers.order_time_to_live"),
            $candidate['DriverPhone'],
            "Scheduled Order Received",
            "Drive",
            $message['data'],
            $candidate['FCM_ID']
        );
        return $message;

    }
    private function getBestVehicle($availableVehicles, $orderID)
    {
        foreach ($availableVehicles as $key => $av)
        {
            if($this->isQueuedVehicle($orderID, $av['apikey']) > 0)
            {
                unset($availableVehicles[$key]);
            }
            elseif ($this->hasLivePoll($av['apikey']) > 0)
            {
                unset($availableVehicles[$key]);
            }
        }
        if($availableVehicles)
        {
            $sortedList = $availableVehicles;
            // $sortedList = sortedData($availableVehicles, 'distance');
            usort($sortedList, function ($a, $b) {
                return $a['distance'] <=> $b['distance'];
            });
            return @$sortedList[0];
        }
        return false;
    }
    private function hasLivePoll($apiKey)
    {
        $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
        $params = [
            'TableName'                 => config('aws.prefix').'QugeoOrderPollingDetails',
            'FilterExpression'          => 'apikey = :apikey AND isclosed = :isclosed',
            'ExpressionAttributeValues' => [
                ':apikey'   => ['S' => $apiKey],
                ':isclosed' => ['N' => '0']
            ],
        ];
        $hasLivePolls = $dynamoClient->scan($params);
        return (@$hasLivePolls->toArray()['Count'] ? $hasLivePolls->toArray()['Count'] : 0);
    }
    private function isQueuedVehicle($orderID, $apiKey)
    {
        $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
        $params = [
            'TableName'                 => config('aws.prefix').'QugeoOrderPollingDetails',
            'FilterExpression'          => 'orderid = :orderid AND apikey = :apikey AND ispickup = :ispickup',
            'ExpressionAttributeValues' => [
                ':orderid'  => ['S' => (string)$orderID],
                ':apikey'   => ['S' => $apiKey],
                ':ispickup' => ['N' => '1']
            ],
        ];
        $pollingAvailable = $dynamoClient->scan($params);
        return (@$pollingAvailable->toArray()['Count'] ? $pollingAvailable->toArray()['Count'] : 0);
    }
    private function getVehicleList($book, $storegroup = 0)
    {
        $dist = config('drivers.pickup_distance') ?? 15;
        $degreeMatrix = getDegreeMatrix($book->pickupLng, $book->pickupLat, $dist);
        $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
        $isLive = "1";
        $lat1 = (@$degreeMatrix['lat1']) ?? 0;
        $lat2 = (@$degreeMatrix['lat2']) ?? 0;
        $lon1 = (@$degreeMatrix['lon1']) ?? 0;
        $lon2 = (@$degreeMatrix['lon2']) ?? 0;
        $filterExpression = "Is_Live = :isLive AND Latitude BETWEEN :Latitude1 AND :Latitude2 AND isallowAutoSchedule = :isallowAutoSchedule AND Longitude BETWEEN :Longitude1 AND :Longitude2";
        $attributeValues = [
            ':isLive'               => ['N' => '1'],
            ':Latitude1'            => ['N' => (string)$lat1],
            ':Longitude1'           => ['N' => (string)$lon1],
            ':isallowAutoSchedule'  => ['N' => '1'],
            ':Latitude2'            => ['N' => (string)$lat2],
            ':Longitude2'           => ['N' => (string)$lon2],
        ];
        if(config('drivers.single_job_mode') == 1)
        {
            $filterExpression .= " AND IsEngaged = :IsEngaged";
            $attributeValues[':IsEngaged'] = ['N' => '0'];
        }
        if($storegroup > 0)
        {
            $filterExpression .= " AND createdBy = :createdBy AND sourceId = :sourceId";
            $attributeValues[':createdBy'] = ['N' => '1'];
            $attributeValues[':sourceId'] = ['N' => (string)$storegroup];
        }
        else
        {
            $filterExpression .= " AND createdBy > :createdBy AND sourceId > :sourceId";
            $attributeValues[':createdBy'] = ['N' => '1'];
            $attributeValues[':sourceId'] = ['N' => '0'];
        }
        $params = [
            'TableName'                 => config('aws.prefix').'QugeoLiveVehicles',
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
                $distance = GetDrivingDistance($book->pickupLat, $vl['Latitude'], $book->pickupLng, $vl['Longitude']);
                $coveredDist = GetDrivingDistance($book->pickupLat, $vl['Home_Latitude'], $book->pickupLng, $vl['Home_Longitude']);
                $deliRange = $value['DeliveryRange'];

                if ($coveredDist <= $deliRange)
                {
                    $vehicleDetails[] = [
                        'apikey'                => $vl['apikey'],
                        'v_No'                  => $vl['v_no'],
                        'distance'              => $distance,
                        'capacity'              => $vl['v_capacity'],
                        'CurrentLoadedWeight'   => $vl['CurrentLoadedWeight'],
                        'RatePerKm'             => $vl['RatePerKm'],
                        'AWS_SNS_ARN'           => $vl['AWS_SNS_ARN'],
                        'DeliveryRange'         => $vl['DeliveryRange'],
                        'FCM_ID'                => $vl['FCM_ID'],
                        'DriverPhone'           => $vl['DriverPhone']
                    ];
                }
            }
        }
        return $vehicleDetails;
    }
    private function addQugeoOrderDetails($book, $consignment)
    {
        try
        {
            $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
            $orderid = sha1(microtime(true) . mt_rand(10000, 90000));
            $validate = date("Ymd");
            $validatetime = date("YmdHis");
            $dynamoClient->putItem([
                'TableName' => config('aws.prefix').'QugeoOrderDetails',
                'Item'      => $this->qugeoOrderDetailsInsert($orderid, $validate, $book, $consignment, $validatetime)
            ]);
            return $orderid;
        }
        catch (\Exception $e)
        {
            info("ADD QugeoOrderDetails DynamoDB Error");
            info($e);
            return false;
        }
    }
    private function qugeoOrderDetailsInsert($orderid, $validate, $book, $consignment, $validatetime)
    {
        return [
            'orderid'                   => ['S' => $orderid],
            'date'                      => ['S' => (string)$book->quor_Date],
            'IsPickup'                  => ['N' => '1'],
            'ManuallyPushed'            => ['N' => '0'],
            'createddatetime'           => ['N' => (string)$validatetime],
            'updateddatetime'           => ['N' => (string)$validatetime],
            'createddate'               => ['N' => (string)$validate],
            'quor_id'                   => ['N' => (string)$book->quor_id],
            'quor_RefNo'                => ['S' => (string)$book->quor_RefNo],
            'IsMilestoneLock'           => ['N' => '0'],
            'MilestoneCovered'          => ['N' => '0'],
            'OrderStatus'               => ['N' => (string)$book->quor_Status],
            'Acceptedapikey'            => ['S' => ' - '],
            'bkno'                      => ['S' => (string)$book->quor_RefNo],
            'pickupmobile'              => ['S' => (string)$book->quor_PickupPhone],
            'pickupname'                => ['S' => (string)$book->quor_PickupName],
            'pickupaddress'             => ['S' => (string)$book->quor_PickupAddress],
            'pickuplocation'            => ['S' => (string)$book->quor_PickupLocation],
            'pickupOTP'                 => ['S' => (string)$book->quor_PickupSMS],
            'pickupLat'                 => ['N' => (string)$book->pickupLat],
            'pickupLng'                 => ['N' => (string)$book->pickupLng],
            'pickupStage1Distance'      => ['N' => (string)$book->quor_PickupStage1Distance],
            'pickupStage2Distance'      => ['N' => (string)$book->quor_PickupStage2Distance],
            'deliverymobile'            => ['S' => (string)$book->quor_DeliveryPhone],
            'deliveryname'              => ['S' => (string)$book->quor_DeliveryName],
            'deliveryaddress'           => ['S' => (string)$book->quor_DeliveryAddress],
            'deliverylocation'          => ['S' => (string)$book->quor_DeliveryLocation],
            'deliveryOTP'               => ['S' => (string)$book->quor_DeliverySMS],
            'deliveryLat'               => ['N' => (string)$book->deliveryLat],
            'deliveryLng'               => ['N' => (string)$book->deliveryLng],
            'deliveryStage1Distance'    => ['N' => (string)$book->quor_DeliveryStage1Distance],
            'deliveryStage2Distance'    => ['N' => (string)$book->quor_DeliveryStage2Distance],
            'FirstScheduleRun'          => ['S' => (string)$book->quor_FirstScheduleRun],
            'TotalDistKM'               => ['N' => (string)$book->quor_DistanceinKM],
            'pktcount'                  => ['S' => '0'],
            'totwt'                     => ['S' => '0'],
            'netamt'                    => ['S' => (string)$book->quor_AmountCollectible],
            'Consignment'               => ['S' => (string)$consignment],
            'IsClosed'                  => ['N' => '0'],
            'PickupPincode'             => ['S' => (string)$book->quor_PickupPincode],
            'DeliveryPincode'           => ['S' => (string)$book->quor_DeliveryPincode],
            'Lat'                       => ['S' => (string)$book->pickupLat],
            'Lng'                       => ['S' => (string)$book->pickupLng],
            'DelAssetBucket'            => ['S' => ' - '],
            'ImgSignLocation'           => ['S' => ' - '],
            'ImgCustLocation'           => ['S' => ' - '],
            'Location'                  => ['S' => (string)$book->quor_PickupLocation],
            'HandlingBranch'            => ['N' => (string)$book->quor_Pickupbr_id],
        ];
    }
}
