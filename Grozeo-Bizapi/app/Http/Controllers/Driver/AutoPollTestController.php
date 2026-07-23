<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Aws\DynamoDb\DynamoDbClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Drivers\QugeoDriver;
use App\Models\Drivers\QugeoOrderDetail;
use App\Models\Drivers\QugeoOrder;
use App\Models\Drivers\RetalineDeliveryRule;

class AutoPollTestController extends Controller
{
    protected $dynamoClient;

    public function __construct()
    {
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
    }

    public function autoPoll()
    {

      $this->validateLiveQugeos();
      $this->scheduleNewBookings();

    }
    //********************************** validateLiveQugeos() *********************************************************************//
    public function validateLiveQugeos()
    {
        $valdatetime = Carbon::now()->format("YmdHis");
        $logoutcutofftime = Carbon::now()->subSeconds(config('constant.QUGEO_LOGIN_KEEPALIVE_TIMEOUT'))->format("YmdHis");

        $params = [
            'TableName' => config('aws.prefix') . 'QugeoLiveVehicles',
            'IndexName' => 'Is_Live-LocationUpdateddatetime-index',
            'KeyConditionExpression' => 'Is_Live = :isLiveValue AND LocationUpdateddatetime < :logoutCutoffTime',
            'ExpressionAttributeValues' => [
                ':isLiveValue' => ['N' => '1'],
                ':logoutCutoffTime' => ['N' => (string) $logoutcutofftime],
            ],
            'ProjectionExpression' => 'apikey, LocationUpdateddatetime, DriverId',
        ];

        $result = $this->dynamoClient->query($params);
        $items = $result['Items'] ?? [];
       

        if (count($items) > 0) {
            $batchUpdates = [];

            foreach ($items as $value) {
                // Batch update for QugeoLiveVehicles
                $batchUpdates[config('aws.prefix') . 'QugeoLiveVehicles'][] = [
                    'PutRequest' => [
                        'Item' => [
                            'apikey' => ['S' => (string) $value['apikey']['S']],
                            'Is_Live' => ['N' => (string) 0],
                            'LoggedOutAt' => ['S' => (string) $valdatetime],
                            'IsCleanLogout' => ['N' => (string) 3],
                        ],
                    ],
                ];
            
                // Batch update for APIHistory
                $batchUpdates[config('aws.prefix') . 'APIHistory'][] = [
                    'PutRequest' => [
                        'Item' => [
                            'apikey' => ['S' => (string) $value['apikey']['S']],
                            'HasLoggedOut' => ['N' => (string) 1],
                            'LoggedOutAt' => ['S' => (string) $valdatetime],
                            'IsCleanLogout' => ['N' => (string) 0],
                        ],
                    ],
                ];
            
                // Batch update for APISession
                $batchUpdates[config('aws.prefix') . 'APISession'][] = [
                    'PutRequest' => [
                        'Item' => [
                            'usertype' => ['N' => '2'],
                            'id' => ['S' => (string) $value['DriverId']['N']],
                            'apikey' => ['S' => '-'],
                        ],
                    ],
                ];
            }
            
            $batchResponse = $this->dynamoClient->batchWriteItem([
                'RequestItems' => $batchUpdates,
            ]);
            

            // Update QugeoDriver model in a single query
            $driverIds = array_column($items, 'DriverId', 'S');
            $driverIds = array_map(function ($id) {
                return (string) $id['N'];
            }, $driverIds);

            QugeoDriver::whereIn('d_ID', $driverIds)->update(['d_apikey' => '-']);

            return true;
        }
        else
        {
            return false;
        }

    }
    // ******************************  scheduleNewBookings() **********************************************************************//
    public function scheduleNewBookings()
    {
        $orderPickupAtOriginDlsId = config('constant.ORDER_PICKUP_AT_ORIGIN_DLS_ID');

        $booking = QugeoOrder::select(
                'quor_id', 'quor_RefNo', 'quor_QugeoPickupDDBOrderId', 'quor_QugeoDeliveryDDBOrderId',
                'quor_PickupToBeManual', 'quor_ScheduleOpeningTime', 'quor_Date', 'quor_PickupName',
                'quor_PickupAddress', 'quor_PickupLocation', 'quor_PickupPhone', 'quor_PickupLat as pickupLat',
                'quor_PickupLng as pickupLng', 'quor_PickupSMS', 'quor_PickupStage1Distance', 'quor_PickupStage2Distance',
                'quor_DeliveryName', 'quor_DeliveryAddress', 'quor_DeliveryLocation', 'quor_DeliveryPhone',
                'quor_DeliveryLat as deliveryLat', 'quor_DeliveryLng as deliveryLng', 'quor_DeliverySMS',
                'quor_DeliveryStage1Distance', 'quor_DeliveryStage2Distance', 'quor_CreatedOn', 'quor_Status',
                'quor_DistanceinKM', 'quor_LastScheduleRun', 'quor_PickupToBeManual', 'quor_ScheduleOpeningTime',
                'quor_FirstScheduleRun', 'quor_PickupPincode', 'quor_DeliveryPincode', 'quor_Pickupbr_id',
                'quor_Deliverybr_id', 'quor_AmountCollectible'
            )
            ->where('quor_slot_id', 0)
            ->where('quor_PickupToBeManual', 0)
            ->where('quor_ScheduleOpeningTime', '<=', Carbon::now()->toDateTimeString())
            ->where('quor_Type', 1)
            ->where('quor_Status', $orderPickupAtOriginDlsId)
            ->orderBy('quor_CreatedOn')
            ->get();


        if ($booking->isNotEmpty()) {
            echo "Found booking for Auto scheduling \n";
            $this->getBookingOrderAndPollIt($booking->toArray(), true, '', false);
            $quorIds = $booking->pluck('quor_id')->toArray();
        } else {
            echo "Found zero booking for Auto scheduling \n";
        }
    }

    public function getBookingOrderAndPollIt($booking, $schedule, $specificvehicle, $Manualpush)
    {
        $bookings=$booking;
        foreach ($bookings as $key => $booking) {
            if (!$manualPush) {
                if ($booking['quor_PickupToBeManual'] == 1) {
                    echo "Manual push cause multiple booking from same customer " . $booking['quor_id'] . "\n";
                    continue;
                }
            }
        
            $consignment = QugeoOrderDetail::where('quor_id', $booking['quor_id'])
                                            ->select('quod_id as detid', 'quor_RefNo as RefNo', 'quor_IsBarcode as Barcode')
                                            ->get();
        
            $orderId =$this->createOrder(true, $booking, 0, $consignment, $Manualpush);
            if (empty($orderId)) {
                echo "Error on creating order " . $orderId . " for booking " . $booking['bk_no'];
                exit;
            }
        
            if ($schedule) {
                $orderArr = [$booking['quor_id']];
                $createdPoll = $this->CreateAPoll($orderId, true, $order, $specificvehicle, $orderArr, 1, 1, $booking['quor_id']);
        
                $now = empty($booking['quor_LastScheduleRun']) ? now() : $booking['quor_LastScheduleRun'];
                $order['LastScheduleRun'] = $now;
        
                if ($createdPoll) {
                    $quorType['quor_UpdateOn'] = now();
                    QugeoOrder::where('quor_id', $booking['quor_id'])
                            ->update([
                                'quor_Type' => 1,
                                'quor_UpdateOn' => $quorType['quor_UpdateOn'],
                                'quor_LastScheduleRun' => $now,
                                'quor_QugeoPickupDDBOrderId' => $orderId,
                                'quor_Status' => config('constant.ORDER_PICKUP_POLLED_DLS_ID')
                            ]);
                } else {
                    $quorType['quor_UpdateOn'] = now();
                    if (!empty($now)) {
                        QugeoOrder::where('quor_id', $booking['quor_id'])
                            ->update([
                                'quor_UpdateOn' => $quorType['quor_UpdateOn'],
                                'quor_LastScheduleRun' => $now
                            ]);
                    }
                    $this->setDeffredPickupSchedule($orderId, true, $order);
                }
            }
        }
    }
    private function setDeffredPickupSchedule($orderid, $ispickup, $order) {

        $now = Carbon::now();

        if (($now->diffInSeconds(Carbon::parse($order['FirstScheduleRun'])) > config('constant.QUDEO_DEFER_MANUAL_SCHEDULE_MAX'))) {
            $this->handoverForManualScheduling($orderid, $ispickup, $order);
            return;
        } else {
            $scheduletime = $this->db->getItemFromDB("select quor_ScheduleOpeningTime from qugeo_order where quor_id = " . $order['quor_id'], true);
            $scheduletime = Carbon::parse($scheduletime);

            if ($scheduletime->diffInSeconds($now) > 0) {
                return;
            }
        }

        if (config('constant.QUGEO_SECONDARY_CANDIDATE_ENABLED') == 1) {
            $marked = $this->findAndMarkProspectiveCandidates($orderid, $ispickup, $order);
        }
        $this->setPickupTime($order, true, $this->db);
    }

    public function CreateOrder($ispickup, $booking, $pktcount, $consignment, $Manualpush)
    {
        try {
            
            $orderid = sha1(microtime(true) . mt_rand(10000, 90000));
            $valdate = date("Ymd");
            $valdatetime = date("YmdHis");
    
            $item = [
                'orderid' => ['S' => (string) $orderid],
                'date' => ['S' => (string) $booking->quor_Date],
                'IsPickup' => ['N' => (string) ($ispickup ? 1 : 0)],
                'ManuallyPushed' => ['N' => (string) ($Manualpush ? 1 : 0)],
                'createddatetime' => ['N' => (string) $valdatetime],
                'updateddatetime' => ['N' => (string) $valdatetime],
                'createddate' => ['N' => (string) $valdate],
                'quor_id' => ['N' => (string) $booking->quor_id],
                'quor_RefNo' => ['S' => (string) $booking->quor_RefNo],
                'IsMilestoneLock' => ['N' => '0'],
                'MilestoneCovered' => ['N' => '0'],
                'OrderStatus' => ['N' => (string) $booking->quor_Status],
                'Acceptedapikey' => ['S' => "-"],
                'bkno' => ['S' => (string) $booking->quor_RefNo],
                'pickupmobile' => ['S' => (string) $booking->quor_PickupPhone],
                'pickupname' => ['S' => (string) $booking->quor_PickupName],
                'pickupaddress' => ['S' => (string) $booking->quor_PickupAddress],
                'pickuplocation' => ['S' => (string) $booking->quor_PickupLocation],
                'pickupOTP' => ['S' => (string) $booking->quor_PickupSMS],
                'pickupLat' => ['N' => (string) $booking->pickupLat],
                'pickupLng' => ['N' => (string) $booking->pickupLng],
                'pickupStage1Distance' => ['N' => (string) $booking->quor_PickupStage1Distance],
                'pickupStage2Distance' => ['N' => (string) $booking->quor_PickupStage2Distance],
                'deliverymobile' => ['S' => (string) $booking->quor_DeliveryPhone],
                'deliveryname' => ['S' => (string) $booking->quor_DeliveryName],
                'deliveryaddress' => ['S' => (string) $booking->quor_DeliveryAddress],
                'deliverylocation' => ['S' => (string) $booking->quor_DeliveryLocation],
                'deliveryOTP' => ['S' => (string) $booking->quor_DeliverySMS],
                'deliveryLat' => ['N' => (string) $booking->deliveryLat],
                'deliveryLng' => ['N' => (string) $booking->deliveryLng],
                'deliveryStage1Distance' => ['N' => (string) $booking->quor_DeliveryStage1Distance],
                'deliveryStage2Distance' => ['N' => (string) $booking->quor_DeliveryStage2Distance],
                'FirstScheduleRun' => ['S' => (string) $booking->quor_FirstScheduleRun],
                'TotalDistKM' => ['N' => (string) $booking->quor_DistanceinKM],
                'pktcount' => ['S' => (string) $pktcount],
                'totwt' => ['S' => '0'],
                'netamt' => ['S' => (string) $booking->quor_AmountCollectible],
                'Consignment' => ['S' => (string) $consignment],
                'IsClosed' => ['N' => '0'],
                'PickupPincode' => ['S' => (string) $booking->quor_PickupPincode],
                'DeliveryPincode' => ['S' => (string) $booking->quor_DeliveryPincode],
            ];
    
            if ($ispickup) {
                $item['Lat'] = ['S' => (string) $booking->pickupLat];
                $item['Lng'] = ['S' => (string) $booking->pickupLng];
                $item['DelAssetBucket'] = ['S' => '-'];
                $item['ImgSignLocation'] = ['S' => '-'];
                $item['ImgCustLocation'] = ['S' => '-'];
                $item['Location'] = ['S' => (string) $booking->quor_PickupLocation];
                $item['HandlingBranch'] = ['N' => (string) $booking->quor_Pickupbr_id];
            } else {
                $item['HandlingBranch'] = ['N' => (string) $booking->quor_Deliverybr_id];
                $item['Location'] = ['S' => (string) $booking->quor_DeliveryLocation];
                $item['Lat'] = ['N' => (string) $booking->deliveryLat];
                $item['Lng'] = ['N' => (string) $booking->deliveryLng];
                $item['DelAssetBucket'] = ['S' => (string)config('constant.QUGEO_DELIVERY_ASSET_BUCKET') ];
                $prefx = $this->getRandString();
                $item['ImgSignLocation'] = ['S' => (string) $prefx . "/" . "S_" . $booking->quor_RefNo . ".jpg"];
                $item['ImgCustLocation'] = ['S' => (string) $prefx . "/" . "C_" . $booking->quor_RefNo . ".jpg"];
            }

            $NewOrder = $this->dynamoClient->putItem([
                'TableName' => config('aws.prefix') . 'QugeoOrderDetails',
                'Item' => $item,
            ]);
            
            return $NewOrder ? $orderid : false;
        } catch (\Exception $e) {
            \Log::error('Error in CreateOrder: ' . $e->getMessage());
            return false;
        }
    }
     private function getRandString($len = 4)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randString = '';
    
        for ($i = 0; $i < $len; $i++) {
            $randString .= $characters[rand(0, strlen($characters) - 1)];
        }
    
        return $randString;
    }
    public function CreateAPoll($orderid, $isPickup, $order, $specificcandidate = '', $quorIds, $totalOrders, $currentOrderCount, $quor_id)
    {
        $delLocations = QugeoOrder::select('quor_id','quor_RefNo','quor_PickupPincode','quor_PickupLat','quor_PickupLng','quor_PickupLocation','quor_DeliveryPincode','quor_DeliveryLat','quor_DeliveryLng','quor_DeliveryLocation','quor_DeliveryAddress','quor_Pickupbr_id','quor_QugeoPickupDDBOrderId')
        ->where('quor_id', $quor_id)->first();

        $deliveryRules = RetalineDeliveryRule::where('rdr_deliveryMode', 2)
                        ->join('finascop_branch as b', 'b.br_rdrIDExpress', '=', 'retaline_delivery_rules.rdr_id')
                        ->where('b.br_ID', $delLocations->quor_Pickupbr_id)
                        ->select('retaline_delivery_rules.is_default','retaline_delivery_rules.br_rdrIdExpress','retaline_delivery_rules.rdr_ruleFor')
                        ->first();
        $storeGroupId = FinascopBranch::where('br_ID', $delLocations->quor_Pickupbr_id)
        ->select('br_storeGroup', 'br_Name')
        ->first();
        if ($specificcandidate == '') {
            $ownVehicle1 = $this->getQugeoCandidates($order['Lat'], $order['Lng'], $storeGroupId['br_storeGroup']);
            if (empty($deliveryRules) || ($deliveryRules['rdr_ruleFor'] == 1 && $deliveryRules['br_rdrIdExpress'] > 0)) {
                 $ownVehicle2 = $this->getQugeoCandidates($order['Lat'], $order['Lng']);
            }
            if (!empty($ownVehicle1) && !empty($ownVehicle2)) {
                $availablevehicles = array_merge($ownVehicle1, $ownVehicle2);
            } else {
                $availablevehicles = !empty($ownVehicle1) ? $ownVehicle1 : $ownVehicle2;
            }
            if (empty($availablevehicles)) {
                echo "No candidates " . "\n";
                return false;
            }
            $candidate = $this->getBestQugeoCandidate($availablevehicles, $orderid, $isPickup, false);
        }
        else
        {
            $nors = $this->dynamoClient->getItem([
                'TableName' => config('aws.prefix') . 'QugeoLiveVehicles',
                'Key' => [
                    'apikey' => ['S' => $specificcandidate],
                ],
                'AttributesToGet' => [
                    'apikey', 'v_id', 'v_no', 'Latitude', 'Longitude', 'v_capacity', 'CurrentLoadedWeight', 'RatePerKm', 'AWS_SNS_ARN', 'DeliveryRange', 'IsEngaged', 'MarkedNextBkId', 'MarkedNextBrId', 'FCM_ID', 'DriverPhone', 'DriverBranchId', 'createdBy', 'sourceId'
                ]
                ]);
                $candidate = isset($rsno['Item']) && count($rsno['Item']) > 0 ? [
                    'AWS_SNS_ARN' => $rsno['Item']['AWS_SNS_ARN']['S']??null,
                    'apikey' => $rsno['Item']['apikey']['S']??null,
                    'DeliveryRange' => $rsno['Item']['DeliveryRange']['S']??null,
                    'FCM_ID' => $rsno['Item']['FCM_ID']['S']??null,
                    'DriverPhone' => $rsno['Item']['DriverPhone']['S']??null,
                    'DriverBranchId' => $rsno['Item']['DriverBranchId']['S']??null,
                    'createdBy' => $rsno['Item']['createdBy']['S']??null,
                    'sourceId' => $rsno['Item']['sourceId']['S']??null
                ] : false;
                
        }
    }
    private function getQugeoCandidates($orderLat, $orderLong, $driversofbranch = 0)
    {
        $arrDegrees = $this->getDegreeMatrix($orderLong, $orderLat, config('constant.QC_VEHICLE_NEAR_PICKUP_CIRCLE_DIST'));
       
        $params = [
            'TableName' => config('aws.prefix') . 'QugeoOrderDetails',
            'IndexName' => 'Is_Live-Latitude-index',
            'KeyConditionExpression' => '#Is_Live = :val AND #Latitude BETWEEN :lat1 AND :lat2',
            'ExpressionAttributeNames' => [
                '#Is_Live' => 'Is_Live',
                '#Latitude' => 'Latitude',
            ],
            'ExpressionAttributeValues' => [
                ':val' => ['N' => '1'],
                ':lat1' => ['N' => (string) $arrVehicle['SortKey']['val1']],
                ':lat2' => ['N' => (string) $arrVehicle['SortKey']['val2']],
            ],
        ];

        // Add condition based on QUGEO_SINGLE_JOB_MODE
        if (config('constant.QUGEO_SINGLE_JOB_MODE') == 1) {
            $params['ExpressionAttributeNames']['#IsEngaged'] = 'IsEngaged';
            $params['ExpressionAttributeValues'][':isEngaged'] = ['N' => '0'];
            $params['FilterExpression'] = '#IsEngaged = :isEngaged';
        }

        // Add condition based on $driversofbranch
        if ($driversofbranch > 0) {
            $params['ExpressionAttributeNames']['#createdBy'] = 'createdBy';
            $params['ExpressionAttributeNames']['#sourceId'] = 'sourceId';
            $params['ExpressionAttributeValues'][':createdBy'] = ['N' => '1'];
            $params['ExpressionAttributeValues'][':sourceId'] = ['N' => (string) $driversofbranch];
            $params['FilterExpression'] .= ' AND (#createdBy = :createdBy AND #sourceId = :sourceId)';
        } else {
            $params['ExpressionAttributeNames']['#createdBy'] = 'createdBy';
            $params['ExpressionAttributeNames']['#sourceId'] = 'sourceId';
            $params['ExpressionAttributeValues'][':createdBy'] = ['N' => '1'];
            $params['ExpressionAttributeValues'][':sourceId'] = ['N' => '0'];
            $params['FilterExpression'] .= ' AND (#createdBy > :createdBy AND #sourceId > :sourceId)';
        }

        // Execute the query
        $result = $this->dynamoClient->query($params);
        $rsno=$result['Items'];

        $vehicledetails = [];

        if (isset($rsno) && count($rsno) > 0) {
            foreach ($rsno as $value) {
                $dist = $this->GetDrivingDistance($orderLat, $value['Latitude'], $orderLong, $value['Longitude']);
                $coveragedist = $this->GetDrivingDistance($orderLat, $value['Home_Latitude'], $orderLong, $value['Home_Longitude']);
                $DeliveryRange = $value['DeliveryRange'];

                if ($coveragedist <= $DeliveryRange) {
                    $vehicledetails[] = [
                        'apikey' => $value['apikey'],
                        'v_No' => $value['v_no'],
                        'distance' => $dist,
                        'capacity' => $value['v_capacity'],
                        'CurrentLoadedWeight' => $value['CurrentLoadedWeight'],
                        'RatePerKm' => $value['RatePerKm'],
                        'AWS_SNS_ARN' => $value['AWS_SNS_ARN'],
                        'DeliveryRange' => $value['DeliveryRange'],
                        'FCM_ID' => $value['FCM_ID'],
                        'DriverPhone' => $value['DriverPhone']
                    ];
                    echo "Distance " . $dist . "\n";
                }
            }
            return $vehicledetails;
        } else {
            // Get delivery rule of the order and check if it has own delivery rule or grozeo delivery rule
            return false;
        }

           
    }
    private function getBestQugeoCandidate($vehicledetails, $orderid, $isPickup, $ignorepreviouspushes)
    {
        $currentload = [];
        $RatePerKm = [];
        $Capacity = [];
        foreach ($vehicledetails as $key => $row) {
            if (!$ignorepreviouspushes) {
                if ($this->wasQueued($orderid, $row['apikey'], $isPickup) > 0) {
                    $vehicledetails->forget($key);
                } elseif ($this->hasLivePoll($row['apikey'])) {
                    $vehicledetails->forget($key);
                } else {
                    $currentload[$key] = $row['CurrentLoadedWeight'];
                    $RatePerKm[$key] = $row['RatePerKm'];
                    $Capacity[$key] = $row['capacity'];
                }
            }
        }
        if (!empty($vehicledetails)) {
            finascop_aasort($vehicledetails, 'distance');
            return $vehicledetails[0];

        } else {
            return false;
        }

    }

    private function WasQueued($orderid, $apikey, $isPickup)
    {
    try {
        $params = [
            'TableName' =>  config('aws.prefix') . 'QugeoOrderPollingDetails',
            'KeyConditionExpression' => '#orderid = :orderid AND #apikey = :apikey',
            'ExpressionAttributeNames' => [
                '#orderid' => 'orderid',
                '#apikey' => 'apikey',
            ],
            'ExpressionAttributeValues' => [
                ':orderid' => ['S' => $orderid],
                ':apikey' => ['S' => $apikey],
            ],
            'FilterExpression' => '#ispickup = :ispickup',
            'ExpressionAttributeValues' => [
                ':ispickup' => ['N' => (string) $isPickup],
            ],
        ];

        $rsno = $this->dynamoClient->query($params);

        return $rsno->get('Count') ?? 0; // Return count or 0 if count is null
    } catch (\Exception $e) {
        // Handle the exception (e.g., log error, return default value)
        return 0;
    }
    }

    public function hasLivePoll($apikey)
    {
        $params = [
            'TableName' => config('aws.prefix') . 'QugeoOrderPollingDetails',
            'KeyConditionExpression' => 'apikey = :apikey AND isclosed = :isclosed',
            'ExpressionAttributeValues' => [
                ':apikey' => ['S' => $apikey],
                ':isclosed' => ['N' => '0'],
            ],
            'IndexName' => 'apikey-isclosed-index',
            'ProjectionExpression' => 'isclosed',
        ];

        $result = $this->dynamoClient->query($params);

        return $result['Count'] > 0;
    }

    public function getDegreeMatrix($mylon, $mylat, $dist)
    {
        $kmtomile = $dist * 0.623;
        $lon1 = $mylon - $kmtomile / abs(cos(deg2rad($mylat)) * 69);
        $lon2 = $mylon + $kmtomile / abs(cos(deg2rad($mylat)) * 69);
        $lat1 = $mylat - ($kmtomile / 69);
        $lat2 = $mylat + ($kmtomile / 69);
        return array("kmtomile" => $kmtomile, "lon1" => $lon1, "lon2" => $lon2, "lat1" => $lat1, "lat2" => $lat2);
    }
    public function GetDrivingDistance($lat1, $lat2, $long1, $long2)
    {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?key=" . config('constant.GMAP_DIST_API_KEY'). "&origins=" . $lat1 . "," . $long1 . "&destinations=" . $lat2 . "," . $long2;
        $response = Http::get($url);
        if ($response->successful()) {
            $response_a = $response->json();
            $dist = $response_a['rows'][0]['elements'][0]['distance']['value'];
            if ((float) $dist == 0) {
                \Log::error("ERROR in DISTANCE " . config('constant.GMAP_DIST_API_KEY'));
                \Log::error($response_a);
            }
            return round($dist / 1000, 2);
        } else {
            \Log::error("Error fetching distance data from Google Maps API: " . $response->status());
            return null;
        }
    }
}
