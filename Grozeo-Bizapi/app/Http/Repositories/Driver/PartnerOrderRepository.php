<?php

namespace App\Http\Repositories\Driver;

use App\Models\Drivers\QugeoOrder;
use App\Models\Drivers\QugeoDriver;
use App\Models\Drivers\FinascopBranch;
use App\Models\Drivers\QuorScheduledDelivery;
use Aws\DynamoDb\DynamoDbClient;
use App\Traits\Driver\CommonTrait;
use Illuminate\Support\Facades\Log;
class PartnerOrderRepository
{
    use CommonTrait;

    protected $dynamoClient;
    protected $pullPendingOrderRepo;

    public function __construct(PullPendingOrderRepository $pullPendingOrderRepo)
    {
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
        $this->pullPendingOrderRepo = $pullPendingOrderRepo;
    }

    // Send Notification to driver
    public function partnerOrder($request)
    {
        try {
            $uniqueId = uniqid();
            $br_id = intval($request->input('br_id'));
            $isScheduled = intval($request->input('isScheduled'));
            $handling_br_id = intval($request->input('handling_br_id'));
            $v_id = $request->input('hdnVehicleId');
            $type = $request->input('type');

            if(isset($v_id))
            {
                $d_id=QugeoDriver::where('d_apikey',$v_id)->value('d_ID');
            }
           
            $response = [];

            if ($isScheduled == 1) {
               
                $quorIdsArray = $request->input('quorIds');
                $quorIds = json_decode($quorIdsArray);
                $totalOrders = count($quorIds);

                if (empty($quorIds)) {
                    throw new \Exception('Invalid quorIds provided.');
                }

                $geoData = [];

                foreach ($quorIds as $quorId) {
                    $delLocations = QugeoOrder::where('quor_id', $quorId)
                        ->select('quor_id', 'quor_DeliveryLat', 'quor_DeliveryLng', 'quor_DeliveryPincode', 'quor_Status', 'quor_Pickupbr_id', 'quor_Deliverybr_id')
                        ->first();

                    if ($delLocations) {
                        $drivetype = $delLocations->quor_Status == 22 ? 'PICKUP' : ($delLocations->quor_Status == 31 ? 'DELIVERY' : '');
                        $geoData[] = [
                            'quor_id' => $delLocations->quor_id,
                            'latitude' =>(float) $delLocations->quor_DeliveryLat,
                            'longitude' =>(float) $delLocations->quor_DeliveryLng,
                            'zipcode' => $delLocations->quor_DeliveryPincode,
                            'drivetype' => $drivetype,
                            'quor_Pickupbr_id' => $delLocations->quor_Pickupbr_id,
                            'quor_Deliverybr_id' => $delLocations->quor_Deliverybr_id,
                        ];

                        $quor_PickupLat = $delLocations->quor_PickupLat;
                        $quor_PickupLng = $delLocations->quor_PickupLng;
                    } else {
                        Log::error("Delivery locations not found for quor_id: $quorId");
                    }
                }

                $slotNearestDeliveryLocations = $this->sortByNearestLatLongPoints($geoData, $quor_PickupLat, $quor_PickupLng, false);

                if ($this->hasLivePoll($v_id)) {
                    return ['success' => false, 'msg' => 'The driver has a live poll, please try after two minutes.', 'Data' => 0];
                }

                $slotDeliveryLocations = array_reverse($slotNearestDeliveryLocations);
                $packingOrderIds = array_column($slotDeliveryLocations, 'quor_id');

                if (!$this->pullPendingOrderRepo->isQugeoAPIAlive($d_id)) {
                    return ['success' => false, 'msg' => 'The Vehicle isn\'t active anymore, please reload.', 'Data' => 0];
                }

                $totalOrders = count($slotDeliveryLocations);
                $ddbOrderIds = [];

                foreach ($slotDeliveryLocations as $key=>$slotOrder) {
                    $currentOrderCount = $key + 1;
                    $orderDetails = QugeoOrder::select('quor_id', 'quor_PickupPincode', 'quor_PickupLat', 'quor_PickupLng', 'quor_PickupLocation', 'quor_DeliveryPincode', 'quor_DeliveryLat', 'quor_DeliveryLng', 'quor_DeliveryLocation')
                        ->where('quor_id', $slotOrder['quor_id'])
                        ->first();

                    if (!$orderDetails) {
                        throw new \Exception('Order details not found.');
                    }

                    $bk_id = intval($orderDetails->quor_id);
                    $drivetype = $orderDetails->quor_Status == 22 ? 'PICKUP' : ($orderDetails->quor_Status == 31 ? 'DELIVERY' : '');

                    if ($drivetype == 'PICKUP') {
                        $br_id = intval($slotOrder['quor_Pickupbr_id']);
                        $handling_br_id = intval($slotOrder['quor_Pickupbr_id']);
                    } else {
                        $br_id = intval($slotOrder['quor_Deliverybr_id']);
                        $handling_br_id = intval($slotOrder['quor_Deliverybr_id']);
                    }

                    if (!$this->pullPendingOrderRepo->isQugeoAPIAlive($d_id)) {
                        return ['success' => false, 'msg' => 'The Vehicle isn\'t active anymore, please reload'];
                    }

                    dd($bk_id);
                 
                    $orderid = $this->pullPendingOrderRepo->scheduleABookingSchJobs($bk_id, $orderDetails, true, $v_id, true, $packingOrderIds, $totalOrders, $currentOrderCount, 'Sch');

                    $schdata = [
                        'quor_id' => $bk_id,
                        'quorddb_id' => $orderid,
                        'sch_uuid' => $uniqueId,
                    ];

                    QuorScheduledDelivery::create($schdata);
                    $ddbOrderIds[] = $orderid;
                }

            } else {
                
                $quorIds = $request->input('quorIds');
                $bk_id = intval($quorIds[0]);
                if (empty($quorIds)) {
                    throw new \Exception('Invalid quorIds provided.');
                }

                if (!$this->pullPendingOrderRepo->isQugeoAPIAlive($d_id)) {
                    return ['success' => false, 'msg' => 'The Vehicle isn\'t active anymore, please reload', 'Data' => 0];
                }

                if ($this->hasLivePoll($v_id)) {
                    return ['success' => false, 'msg' => 'The driver has a live poll, please try after two minutes.', 'Data' => 0];
                }

                $orderid = ($type == 'PICKUP') ?
                    $this->pullPendingOrderRepo->scheduleABookingSchJobs($bk_id, $orderdetails, true, $v_id, true, $quorIds, 1, 1, 'Nor') :
                    $this->pullPendingOrderRepo->scheduleADeliverySchJobs($bk_id, $orderdetails, true, $v_id, true, false, $handling_br_id, $quorIds, 1, 1, 'Nor');
            }

            $response = [
                'success' => true,
                'msg' => 'Queued for scheduling',
                'Data' => $orderid ?? 0,
            ];
        } catch (\Exception $e) {
            $response = ['success' => false, 'msg' => $e->getMessage(), 'Data' => 0];
        }

        return $response;
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

    // List live vehicles ie, Is_Live is 1
    public function listLiveVehicles($request)
    {
        $date = date('Y-m-d');
        $vehicles = [];
        $storeGroupId = $request['storeGroupId'];
    
        $branches = FinascopBranch::where('br_status', 'Active')
            ->where('br_storeGroup', $storeGroupId)
            ->orderBy('br_Name', 'asc')
            ->get(['br_ID', 'br_Name'])
            ->keyBy('br_ID');
    
        if ($branches->isNotEmpty()) {
            foreach ($branches as $values) {
                $partitionKey = ['N' => (string) $values['br_ID']];
                $params = [
                    'TableName' =>config('aws.prefix') . 'QugeoLiveVehicles',
                    'IndexName' => 'ReportingBranch-createddate-index',
                    'KeyConditionExpression' => 'ReportingBranch = :pk AND createddate = :sk',
                    'ExpressionAttributeValues' => [
                        ':pk' => $partitionKey,
                        ':sk' => ['N' => str_replace('-', '', $date)],
                    ],
                    'ProjectionExpression' => 'apikey, v_id, v_no, Latitude, Longitude, LocationUpdateddatetime, DriverName, v_typename, v_capacity, CurrentLoadedWeight, v_MapIcon,Is_Live',
                ];
    
                $result = $this->dynamoClient->query($params);
    
                if (isset($result['Items']) && count($result['Items']) > 0) {
                    foreach ($result['Items'] as $vehicleapi) {
                        if ($vehicleapi['Is_Live']['N'] == '1' || $vehicleapi['Is_Live']['N'] == 1) {
                            $vehicle = [
                                'v_ID' => $vehicleapi['apikey']['S'] ?? null,
                                'v_No' => $vehicleapi['v_no']['S'] ?? null,
                                'Latitude' =>(float) $vehicleapi['Latitude']['N'] ?? null,
                                'Longitude' => (float) $vehicleapi['Longitude']['N'] ?? null,
                                'LastLocationDtTm' =>(int) $vehicleapi['LocationUpdateddatetime']['N'] ?? null,
                                'DriverName' => $vehicleapi['DriverName']['S'] ?? null,
                                'Vehicletypename' => $vehicleapi['v_typename']['S'] ?? null,
                                'MaxLoad' =>(int) $vehicleapi['v_capacity']['N'] ?? null,
                                'CurrentLoad' =>(int) $vehicleapi['CurrentLoadedWeight']['N'] ?? null,
                                'v_MapIcon' => $vehicleapi['v_MapIcon']['S'] ?? null,
                            ];
                            $vehicles[] = $vehicle; // Add the live vehicle to the vehicles array
                        }
                    }
                }
            }
        }
    
        $response = [
            'success' => !empty($vehicles),
            'msg' => !empty($vehicles) ? 'Vehicle details' : 'No vehicles found',
            'Data' => $vehicles, // Return the array of live vehicles
        ];
    
        return $response;
    }
    
    // Load Live vehicles details
    public function loadVehicleDetails($request)
    {
        $longitude = $request->input('longitude');
        $latitude = $request->input('latitude');
        $brId = $request->input('br_id');
    
        // Fetch store group details
        $storeGroup = FinascopBranch::select('br_storeGroup', 'br_Name')
            ->where('br_ID', $brId)
            ->first();
    
        if (!$storeGroup) {
            return [
                'success' => false,
                'msg' => 'No Store Group Found',
                'Data' => []
            ];
        }
    
        $arrDegrees = getDegreeMatrix($longitude, $latitude, config('constant.QC_VEHICLE_NEAR_PICKUP_CIRCLE_DIST'));
    
        $params = [
            'TableName' => config('aws.prefix') . 'QugeoLiveVehicles',
            'IndexName' => 'Is_Live-Latitude-index',
            'KeyConditionExpression' => '#pk = :pk AND #sk BETWEEN :val1 AND :val2',
            'ExpressionAttributeNames' => [
                '#pk' => 'Is_Live',
                '#sk' => 'Latitude',
                '#lon' => 'Longitude',
                '#cb' => 'createdBy',
                '#sid' => 'sourceId',
            ],
            'ExpressionAttributeValues' => [
                ':pk' => ['N' => '1'],
                ':val1' => ['N' => (string) $arrDegrees['lat1']],
                ':val2' => ['N' => (string) $arrDegrees['lat2']],
                ':lon1' => ['N' => (string) $arrDegrees['lon1']],
                ':lon2' => ['N' => (string) $arrDegrees['lon2']],
                ':cb' => ['N' => '1'],
                ':sid' => ['N' => (string) $storeGroup['br_storeGroup']],
            ],
            'ProjectionExpression' => 'apikey, v_id, v_no, Latitude, Longitude, LocationUpdateddatetime, DriverName, v_typename, v_capacity, CurrentLoadedWeight, v_MapIcon',
            'FilterExpression' => '#lon BETWEEN :lon1 AND :lon2 AND #cb = :cb AND #sid = :sid',
        ];
    
        $result = $this->dynamoClient->query($params);
    
        if (isset($result['Items']) && count($result['Items']) > 0) {
            $vehicles = [];
            foreach ($result['Items'] as $item) {
                $vehicles[] = [
                    'v_ID' => $item['apikey']['S'] ?? null,
                    'v_No' => $item['v_no']['S'] ?? null,
                    'Latitude' =>(float) $item['Latitude']['N'] ?? null,
                    'Longitude' =>(float) $item['Longitude']['N'] ?? null,
                    'LastLocationDtTm' => (int)$item['LocationUpdateddatetime']['N'] ?? null,
                    'DriverName' => $item['DriverName']['S'] ?? null,
                    'Vehicletypename' => $item['v_typename']['S'] ?? null,
                    'MaxLoad' =>(int) $item['v_capacity']['N'] ?? null,
                    'CurrentLoad' =>(int) $item['CurrentLoadedWeight']['N'] ?? null,
                    'v_MapIcon' => $item['v_MapIcon']['S'] ?? null
                ];
            }
    
            return [
                'success' => true,
                'msg' => 'Vehicle details',
                'Data' => $vehicles
            ];
        }
    
        return [
            'success' => false,
            'msg' => 'No vehicles found',
            'Data' => []
        ];
    }
    

}
