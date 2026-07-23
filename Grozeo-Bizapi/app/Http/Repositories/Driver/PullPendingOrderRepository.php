<?php

namespace App\Http\Repositories\Driver;
use App\Models\Drivers\QugeoDriver;
use App\Models\Drivers\QugeoOrder;
use App\Models\Drivers\QugeoOrderDetail;
use App\Models\Drivers\QugeoScheduleSlot;
use App\Models\Drivers\FinascopBranch;
use App\Http\Responses\{
    SuccessResponse,
    SuccessWithData,
    ErrorResponse
};
use Illuminate\Support\Facades\DB;
use Aws\DynamoDb\DynamoDbClient;
use App\Http\Controllers\Driver\Notification\FcmNotificationController;
use Log;
use App\CloudFcmNotification;
use Illuminate\Support\Facades\Http;
use App\Status\DelayedOrderActions;
use App\Events\DelayedOrderActions as DelayedOrderEvent;
use BackOffice\Models\TransferOrder;

class PullPendingOrderRepository
{
    public $request_from=null;
    public function __construct()
    {
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
    }
    /**
     * Pull pending orders
     */
  
    public function pullPendingOrder($request)
    {
        try {
            if (isset($request->from)) {
                $this->request_from = $request->from; 
            }
            // Fetch the updatedOn hash for comparison
            $updateOn = QugeoOrder::where('quor_id', $request->id)
            ->first();

            if ($updateOn) {
                $updateOnValue = md5($updateOn->quor_UpdateOn);
            } else {
                 return new ErrorResponse("Order not found");

            }

            // Check if the provided key matches the updateOn hash
            // if ($request->key !== $updateOnValue) {
            //     return new ErrorResponse("Order has been updated, please reload and try again");
            // }

            // Check if the Qugeo API is alive
            $checkVehicle = $this->isQugeoAPIAlive(auth_user()->d_ID);
            if (!$checkVehicle) {
                return new ErrorResponse("The Vehicle isn't active anymore, please reload");
            }

            // Determine the type of operation (pickup or delivery) and schedule accordingly
            if ($request->drivetype === "PICKUP") {
               
                $data= $this->scheduleABookingSchJobs($request->id, $orderdetails, true, auth_user()->d_apikey, true, $request->id, 1, 1, 'Nor');

            } else {
              
                $data=  $this->scheduleADeliverySchJobs($request->id, $orderdetails, true, auth_user()->d_apikey, true, false, 0, $request->id, 1, 1, 'Nor');
         
            }


            if(isset($data))
            {

                return new SuccessWithData(json_encode($data));
  
            }
        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            \Log::error('Error in pullPendingOrder: ' . $e->getMessage());

            // Return an error response
            return new ErrorResponse('An error occurred while processing the order');
        }
    }

    public function isQugeoAPIAlive($apikey)
    {
        try {
    
            $result = $this->dynamoClient->getItem([
                'TableName' =>config('aws.prefix') . 'APISession',
                'Key' => [
                    'usertype' => ['N' => '2'],
                    'id' => ['S' => (string)$apikey ],
                ],
                'ProjectionExpression' => 'apikey',
            ]);
    
            return isset($result['Item']); // Check if the 'Item' key exists in the result
        } catch (\Exception $e) {
            \Log::error('Error in isQugeoAPIAlive: ' . $e->getMessage());
            return false; // Return false on error or exception
        }
    }
    
    public function scheduleABookingSchJobs($quorid, &$order, $schedule = true, $specificvehicle = '', $Manualpush = true, $quorIds, $totalOrders, $currentOrderCount, $type)
    {
        try {

          
         
            $booking = QugeoOrder::select(
                'quor_id',
                'quor_RefNo',
                'quor_QugeoPickupDDBOrderId',
                'quor_QugeoDeliveryDDBOrderId',
                'quor_PickupToBeManual',
                'quor_ScheduleOpeningTime',
                'quor_Date',
                'quor_PickupName',
                'quor_PickupAddress',
                'quor_PickupLocation',
                'quor_PickupPhone',
                'quor_PickupLat as pickupLat',
                'quor_PickupLng as pickupLng',
                'quor_PickupSMS',
                'quor_PickupStage1Distance',
                'quor_PickupStage2Distance',
                'quor_DeliveryName',
                'quor_DeliveryAddress',
                'quor_DeliveryLocation',
                'quor_DeliveryPhone',
                'quor_DeliveryLat as deliveryLat',
                'quor_DeliveryLng as deliveryLng',
                'quor_DeliverySMS',
                'quor_DeliveryStage1Distance',
                'quor_DeliveryStage2Distance',
                'quor_CreatedOn',
                'quor_Status',
                'quor_DistanceinKM',
                'quor_LastScheduleRun',
                'quor_FirstScheduleRun',
                'quor_PickupPincode',
                'quor_DeliveryPincode',
                'quor_Pickupbr_id',
                'quor_Deliverybr_id',
                'quor_AmountCollectible'
            )
                ->where('quor_id', $quorid)
                ->first();
    
            // $orderid = '';
           
            if (!empty($booking)) {
                $orderid = $this->getBookingOrderAndPollItSchJob($booking, $schedule, $specificvehicle, $Manualpush, $quorIds, $totalOrders, $currentOrderCount, $type);
             
            }
            
            return $orderid;
        } catch (\Exception $e) {
            \Log::error('Error in scheduleABookingSchJobs: ' . $e->getMessage());
            return null;
        }
    }
    
    private function getBookingOrderAndPollItSchJob($booking, $schedule, $specificvehicle, $Manualpush, $quorIds, $totalOrders, $currentOrderCount, $type)
    {
        try {
            
            $quorId = $booking->quor_id;
            $order = null;
            $consignment = QugeoOrderDetail::select(
                'quod_id as detid',
                'quor_RefNo as RefNo',
                'quor_IsBarcode as Barcode'
            )->where('quor_id', $quorId)->get();
    
            $orderid = $this->createOrder(true, $booking, 0, $consignment, $Manualpush);

        
         
            if (empty($orderid)) {
                abort(500, "Error on creating order {$orderid} for booking {$booking['bk_no']}");
            }
    
            if ($type === 'Sch') {
          
                
                if (!empty($orderid)) {
                    Log::error("getBookingOrderAndPollItSchJob orderids--------------------------------------------- {$booking['quor_id']} ---------------------------------------------");
    
                    $quorType['quor_UpdateOn'] = now()->format('Y-m-d H:i:s');
    
                    QugeoOrder::where('quor_id', $booking['quor_id'])
                        ->update([
                            'quor_Type' => 1,
                            'quor_UpdateOn' => $quorType['quor_UpdateOn'],
                            'quor_QugeoPickupDDBOrderId' => $orderid,
                        ]);
    
                    Log::error(print_r($orderid, true));
                }
            }
            
    
            if ($schedule) {
               
               
                $createdPoll= $this->createAPollSchJob($orderid, true, $order, $specificvehicle, $quorIds, $totalOrders, $currentOrderCount, $booking['quor_id']);
             
             
                $now = empty($booking['quor_LastScheduleRun']) ? now()->format('Y-m-d H:i:s') : $booking['quor_LastScheduleRun'];
    
                $order = ['LastScheduleRun' => $now];
    
                if ($type === 'Sch') {
                 
                    if (!empty($orderid)) {
                      
                        $quorType['quor_UpdateOn'] = now()->format('Y-m-d H:i:s');
                        QugeoOrder::where('quor_id', $booking['quor_id'])
                            ->update([
                                'quor_Type' => 1,
                                'quor_UpdateOn' => $quorType['quor_UpdateOn'],
                                'quor_LastScheduleRun' => $now,
                                'quor_QugeoPickupDDBOrderId' => $orderid,
                                'quor_Status' => config('constant.ORDER_PICKUP_POLLED_DLS_ID'),
                            ]);
                           
                    }
                } else {
                    if ($createdPoll) {
                        $quorType['quor_UpdateOn'] = now()->format('Y-m-d H:i:s');
                        QugeoOrder::where('quor_id', $booking['quor_id'])
                            ->update([
                                'quor_Type' => 1,
                                'quor_UpdateOn' => $quorType['quor_UpdateOn'],
                                'quor_LastScheduleRun' => $now,
                                'quor_QugeoPickupDDBOrderId' => $orderid,
                                'quor_Status' => config('constant.ORDER_PICKUP_POLLED_DLS_ID'),
                            ]);
                    } else {
                        $quorType['quor_UpdateOn'] = now()->format('Y-m-d H:i:s');
                        QugeoOrder::where('quor_id', $booking['quor_id'])
                            ->update([
                                'quor_UpdateOn' => $quorType['quor_UpdateOn'],
                                'quor_LastScheduleRun' => $now,
                            ]);
                        $this->setDeffredPickupSchedule($orderid, true, $order);
                    }
                }
              
            }
          
            if($this->request_from==null)
            {
                return $orderid;
            }
            else
            {
                return isset($createdPoll) ? $createdPoll : $orderid;
            }
            
        } catch (\Exception $e) {
            \Log::error('Error in getBookingOrderAndPollItSchJob: ' . $e);
            return null;
        }
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
    
    public function CreateAPollSchJob($orderid, $isPickup, $order, $specificcandidate = '', $quorIds, $totalOrders, $currentOrderCount, $quor_id)
    {
        
      
       
        $rsno = $this->dynamoClient->getItem([
            'TableName' =>config('aws.prefix') . 'QugeoLiveVehicles',
            'Key' => [
                'apikey' => ['S' => $specificcandidate], // Ensure $specificcandidate contains the correct API key value
            ],
            'ProjectionExpression' => 'apikey, AWS_SNS_ARN, DeliveryRange, FCM_ID, DriverPhone',
        ]);
    
       
        
        if (isset($rsno['Item']) && count($rsno['Item']) > 0) {
            $candidate = [
                'AWS_SNS_ARN' => $rsno['Item']['AWS_SNS_ARN']['S'],
                'apikey' => $rsno['Item']['apikey']['S'],
                'DeliveryRange' => intval($rsno['Item']['DeliveryRange']['N']),
                'FCM_ID' => $rsno['Item']['FCM_ID']['S'],
                'DriverPhone' => $rsno['Item']['DriverPhone']['S'],
            ];
        } else {
            $candidate = false;
        }
       
      
     
        if ($candidate !== false) {
            $pollid = sha1(microtime(true) . mt_rand(10000, 90000));
            $valdate = date("Ymd");
            $valdatetime = date("YmdHis");
            $message = ''; // Define your message here
            
           
           
            // Use AWS SNS to push message
            $pushedmessage = $this->PushPollingDetailsSchJobs(
                $candidate['apikey'],
                $pollid,
                'NEW',
                $orderid,
                $isPickup,
                $order,
                $candidate['AWS_SNS_ARN'],
                $message,
                $isPickup ? (intval($candidate['DeliveryRange']) >= intval($order['TotalDistKM']) ? true : false) : false,
                $candidate['FCM_ID'],
                $candidate['DriverPhone'],
                $quorIds,
                $totalOrders,
                $currentOrderCount,
                $quor_id

            );
           
         
            if ($pushedmessage === true) {
              
                // Save message to table
                $savedmessage = $this->SavePollingDetails(
                    $pollid,
                    $orderid,
                    $candidate['apikey'],
                    $isPickup,
                    $valdate,
                    $valdatetime,
                    $message,
                    $isPickup ? (intval($candidate['DeliveryRange']) >= intval($order['TotalDistKM']) ? true : false) : false
                );
    
                return $savedmessage;
            }
        }
    
       
        
        // No candidate or error in processing
        return $pushedmessage;
    }
    
   
    private function pushPollingDetailsSchJobs($apikey, $pollid, $msgtype, $orderid, $isPickup, $order, $arn, &$message, $withinrange, $fcmid, $mobno, $quorIds, $totalOrders, $currentOrderCount, $quor_id)
    {
       
      
        // if ($totalOrders == $currentOrderCount) {
           
            $message = [];
            $deliveryLocs = [];
            $orderIds = [];
         
            if (is_array($quorIds)) {
                for ($i = 0; $i < count($quorIds); $i++) {
                    $delLocations = QugeoOrder::join('qugeo_deliverystatus', 'qugeo_order.quor_Status', '=', 'qugeo_deliverystatus.dls_ID')
                        ->where('qugeo_order.quor_id', $quorIds[$i])
                        ->select(
                            'qugeo_order.quor_id',
                            'qugeo_order.quor_RefNo',
                            'qugeo_order.quor_PickupPincode',
                            'qugeo_order.quor_PickupLat',
                            'qugeo_order.quor_PickupLng',
                            'qugeo_order.quor_PickupLocation',
                            'qugeo_order.quor_DeliveryPincode',
                            'qugeo_order.quor_DeliveryLat',
                            'qugeo_order.quor_DeliveryLng',
                            'qugeo_order.quor_DeliveryLocation',
                            'qugeo_order.quor_DeliveryAddress',
                            'qugeo_order.quor_Pickupbr_id',
                            'qugeo_order.quor_Deliverybr_id',
                            'qugeo_order.quor_QugeoPickupDDBOrderId',
                            'qugeo_deliverystatus.dls_DelStatus',
                            'qugeo_order.quor_Status',
                            'qugeo_order.quor_PickupAddress',
                            'qugeo_order.quor_PickupPhone'
                        )
                        ->first();
            
                       
                    if (!empty($delLocations['quor_QugeoPickupDDBOrderId'])) {
                        $quor_QugeoPickupDDBOrderId = $delLocations['quor_QugeoPickupDDBOrderId'];
                    } else {
                        $quor_QugeoPickupDDBOrderId = $orderid;
                    }
                    $storeGroup = FinascopBranch::where('br_ID', $delLocations['quor_Deliverybr_id'])->value('br_storeGroup');
                    $quor_DeliveryLocation = $delLocations['quor_DeliveryAddress'] . ' ' . $delLocations['quor_DeliveryPincode'];
        
                        
                    $orderIds[] = [
                        "id" => $quor_QugeoPickupDDBOrderId,
                        "order" => $delLocations['quor_id'],
                        "orderNo" => $delLocations['quor_RefNo'],
                        "location" => $quor_DeliveryLocation,
                        "latitude" =>(double) $delLocations['quor_DeliveryLat'],
                        "longitude" =>(double) $delLocations['quor_DeliveryLng'],
                        "orderStatus" => $delLocations['dls_DelStatus'],
                        "statusId" => $delLocations['quor_Status']
                    ];
        
                    $deliveryLocs[] = [
                        "latitude" =>(double) $delLocations['quor_DeliveryLat'],
                        "longitude" =>(double) $delLocations['quor_DeliveryLng'],
                        "location" => $quor_DeliveryLocation
                    ];
            
                    $delBoyReq = [
                        'fcmid' => $fcmid,
                        'apikey' => $apikey,
                        'pollid' => $pollid,
                        'mobile' => $mobno,
                        'quorId' => $quorIds[$i],
                        'quor_QugeoPickupDDBOrderId' => $quor_QugeoPickupDDBOrderId,
                    ];
            
                        DB::table('qugeo_driver_log')->insert($delBoyReq);
                    }
            }
            else
            {
              
                    $delLocations = QugeoOrder::join('qugeo_deliverystatus', 'qugeo_order.quor_Status', '=', 'qugeo_deliverystatus.dls_ID')
                        ->where('qugeo_order.quor_id', $quorIds)
                        ->select(
                            'qugeo_order.quor_id',
                            'qugeo_order.quor_RefNo',
                            'qugeo_order.quor_PickupPincode',
                            'qugeo_order.quor_PickupLat',
                            'qugeo_order.quor_PickupLng',
                            'qugeo_order.quor_PickupLocation',
                            'qugeo_order.quor_DeliveryPincode',
                            'qugeo_order.quor_DeliveryLat',
                            'qugeo_order.quor_DeliveryLng',
                            'qugeo_order.quor_DeliveryLocation',
                            'qugeo_order.quor_DeliveryAddress',
                            'qugeo_order.quor_Pickupbr_id',
                            'qugeo_order.quor_Deliverybr_id',
                            'qugeo_order.quor_QugeoPickupDDBOrderId',
                            'qugeo_deliverystatus.dls_DelStatus',
                            'qugeo_order.quor_Status',
                            'qugeo_order.quor_PickupAddress',
                            'qugeo_order.quor_PickupPhone'
                        )
                        ->first();
            
                       
                    if (!empty($delLocations['quor_QugeoPickupDDBOrderId'])) {
                        $quor_QugeoPickupDDBOrderId = $delLocations['quor_QugeoPickupDDBOrderId'];
                    } else {
                        $quor_QugeoPickupDDBOrderId = $orderid;
                    }
                    $storeGroup = FinascopBranch::where('br_ID', $delLocations['quor_Deliverybr_id'])->value('br_storeGroup');
                    $quor_DeliveryLocation = $delLocations['quor_DeliveryAddress'] . ' ' . $delLocations['quor_DeliveryPincode'];
        
                        
                    $orderIds[] = [
                        "id" => $quor_QugeoPickupDDBOrderId,
                        "order" => $delLocations['quor_id'],
                        "orderNo" => $delLocations['quor_RefNo'],
                        "location" => $quor_DeliveryLocation,
                        "latitude" =>(double) $delLocations['quor_DeliveryLat'],
                        "longitude" =>(double) $delLocations['quor_DeliveryLng'],
                        "orderStatus" => $delLocations['dls_DelStatus'],
                        "statusId" => $delLocations['quor_Status']
                    ];
        
                    $deliveryLocs[] = [
                        "latitude" =>(double) $delLocations['quor_DeliveryLat'],
                        "longitude" =>(double) $delLocations['quor_DeliveryLng'],
                        "location" => $quor_DeliveryLocation
                    ];
            
                    $delBoyReq = [
                        'fcmid' => $fcmid,
                        'apikey' => $apikey,
                        'pollid' => $pollid,
                        'mobile' => $mobno,
                        'quorId' => $quorIds,
                        'quor_QugeoPickupDDBOrderId' => $quor_QugeoPickupDDBOrderId,
                    ];
            
                        DB::table('qugeo_driver_log')->insert($delBoyReq);
                    
            }
         
    
                $disttobr = $order['TotalDistKM'] . ' KM';



                $geocoords = [
                    "pickup" => [
                        "latitude" =>(double) $delLocations['quor_PickupLat'],
                        "longitude" =>(double) $delLocations['quor_PickupLng'],
                        "location" =>  $delLocations['quor_PickupLocation'],
                        "address" => $delLocations['quor_PickupAddress'],
                        "mobile" => $delLocations['quor_PickupPhone']
                    ],
                    "delivery" => $deliveryLocs
                ];

            
                $data = [
                    "yourapikey" => $apikey,
                    "msgid" => $pollid,
                    "msgtype" => $msgtype,
                    "storeGroup" => $storeGroup,
                    "orderid" => json_encode($orderIds),
                    "orderCount" =>(string)$totalOrders,
                    "ispickuporder" => $isPickup,
                    "details" => json_encode($geocoords)
                ];

                
                $message['data'] = $data;
                $res['data'] = ["data" => $data];

              
                if($this->request_from==null)
                {
                    
                     $this->sendmessage(60, $mobno, "Scheduled Order Received", "Drive", $message['data'], $fcmid) ;
           
                }
            
        // }
   
        return $res['data'];
    }
    

    public function sendmessage($ttl, $labeltext, $bodytext, $title, $data, $fcmid)
    {
    
        
        $newNotification = new CloudFcmNotification();
        $response = $newNotification
            ->setTimeToLive(60)
            ->setAnalyticalLabel($labeltext)
            ->setBody($bodytext)
            ->setTitle($title)
            ->setSound('default')
            ->setData($data)
            ->to($fcmid)
            ->send();
    }
    
    private function savePollingDetails($pollid, $orderid, $apikey, $ispickup, $message, $withinrange)
    {
        $valdatetime = date("YmdHis");
        $valdate = date("Ymd");
    
        $item = [
            'pollingid' => ['S' => (string) $pollid],
            'apikey' => ['S' => (string) $apikey],
            'orderid' => ['S' => (string) $orderid],
            'createddatetime' => ['N' => (string) $valdatetime],
            'createddate' => ['N' => (string) $valdate],
            'currentstatus' => ['S' => 'POLLED'],
            'ispickup' => ['N' => ($ispickup ? '1' : '0')], // Convert to string representation of boolean
            'isclosed' => ['N' => '0'],
            'pollingdetails' => ['S' => (string) json_encode($message)],
            'withinrange' => ['N' => ($withinrange ? '1' : '0')], // Convert to string representation of boolean
        ];
    
        try {
            $this->dynamoClient->putItem([
                'TableName' => config('aws.prefix') . 'QugeoOrderPollingDetails',
                'Item' =>  $item,
            ]);
            return true;
        } catch (\Exception $e) {
            // Handle the exception, log the error, or return false as needed
            // Example: Log::error('Error saving polling details: ' . $e->getMessage());
            return false;
        }
    }
    
    private function setDeferredPickupSchedule($orderid, $ispickup, $order) {
        $now = now()->format('Y-m-d H:i:s');
        $maxDeferredTime = config('constant.QUGEO_DEFER_MANUAL_SCHEDULE_MAX');
    
        if ((strtotime($now) - strtotime($order['FirstScheduleRun'])) > $maxDeferredTime) {
            // If deferring has passed the maximum deferring time
            $this->handoverForManualScheduling($orderid, $ispickup, $order);
            return;
        } else {
            $scheduleTime = QugeoOrder::where('quor_id', $order['quor_id'])->value('quor_ScheduleOpeningTime');
    
            if ((strtotime($scheduleTime) - strtotime($now)) > 0) {
                // If the schedule time is pushed to a new time during processing
                return;
            }
        }
    
        if (config('constant.QUGEO_SECONDARY_CANDIDATE_ENABLED') == 1) {
            $marked = $this->findAndMarkProspectiveCandidates($orderid, $ispickup, $order);
        }
    
        $this->setPickupTime($order, true, $this->db);
    }
    
    public function handoverForManualScheduling($orderid, $ispickup, $order) {
        $now = now()->format('Y-m-d H:i:s');
    
        $updateData = [
            'quor_UpdateOn' => $now,
            'quor_Type' => 0,
        ];
    
        if ($ispickup) {
            $updateData['quor_PickupToBeManual'] = 1;
            $updateData['quor_Status'] = config('constant.ORDER_PICKUP_AT_ORIGIN_DLS_ID');
        } else {
            $updateData['quor_DeliveryToBeManual'] = 1;
            $updateData['quor_Status'] = config('constant.ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID');
        }
    
        QugeoOrder::whereNotIn('quor_Status', [15, 38])
            ->where('quor_id', $order['quor_id'])
            ->update($updateData);
        
        $this->delayedDeliveryStarted($order['quor_id']);
    
        $tableName =config('aws.prefix') . 'QugeoOrderDetails';
    
        $this->dynamoClient->updateItem([
            'TableName' => $tableName,
            'Key' => [
                'orderid' => ['S' => $orderid],
            ],
            'AttributeUpdates' => [
                'IsClosed' => [
                    'Action' => 'PUT',
                    'Value' => ['N' => '1']
                ],
            ]
        ]);
    }
    
    public function FindandMarkProspectiveCandidates($orderid, $isPickup, $order, $specificcandidate = '') {
        $availablevehicles = [];
        $locationLat = $isPickup ? $order['pickupLat'] : $order['deliveryLat'];
        $locationLng = $isPickup ? $order['pickupLng'] : $order['deliveryLng'];
        
        $availablevehicles = $this->getSecondaryQugeoCandidates($locationLat, $locationLng);
        
        if (empty($availablevehicles)) {
            return false;
        }
    
        $candidate = $this->getBestSecondaryQugeoCandidate($availablevehicles, $orderid, $isPickup, true);
        
        if ($candidate !== false) {

            $tableName =config('aws.prefix') . 'QugeoLiveVehicles';
    
            $this->dynamoClient->updateItem([
                'TableName' => $tableName,
                'Key' => [
                    'apikey' => ['S' => $candidate['apikey']],
                ],
                'AttributeUpdates' => [
                    'MarkedNextBkId' => [
                        'Action' => 'PUT',
                        'Value' => ['N' => (string) $order['bk_id']]
                    ],
                    'MarkedNextBrId' => [
                        'Action' => 'PUT',
                        'Value' => ['N' => (string) $order['bk_brk_br_id']]
                    ],
                ]
            ]);
            
            return true;
        } else {
            return false;
        }
    }
    
    private function getSecondaryQugeoCandidates($orderLat, $orderLong)
    {
        // Calculate the degree matrix for the order location
        $this->getDegreeMatrix($orderLong, $orderLat, config('constant.QC_VEHICLE_NEAR_PICKUP_CIRCLE_DIST'));
    
        // Define the query using Laravel Collections
        $query = collect([
            'PartitionKey' => ['col' => 'Is_Live', 'val' => 1, 'oper' => '='],
            'SortKey' => ['col' => 'OnJobCompletionLatitude', 'val1' => (float) $arrDegrees['lat1'], 'val2' => (float) $arrDegrees['lat2'], 'SortKeyBetween' => true],
            'IndexName' => 'Is_Live-OnJobCompletionLatitude-index',
            'queryAttributes' => ['apikey', 'v_id', 'v_no', 'OnJobCompletionLatitude', 'OnJobCompletionLongitude', 'v_capacity', 'CurrentLoadedWeight', 'RatePerKm', 'AWS_SNS_ARN', 'DeliveryRange', 'IsEngaged', 'MarkedNextBkId', 'MarkedNextBrId'],
            'Condition' => [],
        ]);
    
        // Add conditions using Laravel Collections
        $query['Condition']->push(['col' => 'OnJobCompletionLongitude', 'val1' => (float) $arrDegrees['lon1'], 'val2' => (float) $arrDegrees['lon2'], 'ConditionBetween' => true]);
        $query['Condition']->push(['col' => 'MarkedNextBkId', 'val' => 0, 'oper' => '=']);
        $query['Condition']->push(['col' => 'IsEngaged', 'val' => 0, 'oper' => '=']);
    
        // Convert the Laravel Collection to an array
        $queryArray = $query->toArray();
    
        // Perform the getItem operation with DynamoDB using the constructed query
        $result = $this->dynamoClient->getItem([
            'TableName' => config('aws.prefix') . 'QugeoLiveVehicles',
            'Key' => [
                'PartitionKey' => ['S' => $queryArray['PartitionKey']['col']],
                'SortKey' => ['S' => $queryArray['SortKey']['col']],
            ],
            'ProjectionExpression' => implode(', ', $queryArray['queryAttributes']),
        ]);
    
        $vehicledetails = [];
        if (isset($result) && count($result) > 0) {
            foreach ($result as $vehicle) {
                $dist = $this->GetDrivingDistance($orderLat, $vehicle['OnJobCompletionLatitude'], $orderLong, $vehicle['OnJobCompletionLongitude']);
                array_push($vehicledetails, [
                    'apikey' => $vehicle['apikey'],
                    'v_No' => $vehicle['v_no'],
                    'distance' => $dist,
                    'capacity' => $vehicle['v_capacity'],
                    'CurrentLoadedWeight' => $vehicle['CurrentLoadedWeight'],
                    'RatePerKm' => $vehicle['RatePerKm'],
                    'AWS_SNS_ARN' => $vehicle['AWS_SNS_ARN'],
                    'DeliveryRange' => $vehicle['DeliveryRange']
                ]);
            }
            return $vehicledetails;
        } else {
            return false;
        }
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
    
    public function setPickupTime($order, $updateTime = true, $db)
    {
        $today = now()->format('Y-m-d');
        $now = now();
        $nextTime = $now->addSeconds(config('constant.QUGEO_DEFER_MANUAL_SCHEDULE_BY'));
        $pickupableToday = $this->getIsPickableToday($nextTime->format('His'));
    
        if ($pickupableToday > 0) {
            $nextTime = $this->getEarliestProcessingTime($nextTime->format('His'));
            $scheduleTime = $today . ' ' . $nextTime->format('H:i:s');
    
            $endTime = $now->addSeconds(config('constant.QUGEO_DEFER_MANUAL_SCHEDULE_BY'));
            $this->updatePickupSchedule($order, $scheduleTime, false, $now, $endTime, $db);
            $this->updatePickupSchedule($order, $scheduleTime, true, $now, $endTime, $db);
        } else {
            $pickupDay = $this->getPickupableDay();
            $slots = $this->getPickupScheduleSlots(0);
            $slotTime = $this->getPickupStartTimeOfSlots($slots[0]);
            $pickupTime = $now->format('H:i:s', strtotime($slotTime));
            $scheduleTime = $pickupDay . ' ' . $pickupTime;
    
            $endTime = $now->addSeconds(config('constant.QUGEO_DEFER_MANUAL_SCHEDULE_BY'));
            $this->updatePickupSchedule($order, $scheduleTime, false, $now, $endTime, $db);
            $this->updatePickupSchedule($order, $scheduleTime, true, $now, $endTime, $db);
        }
    
        return $scheduleTime;
    }
    
    public function getPickupableDay() {
        $pickupdays = $this->getPickupdays();
        $holidays = $this->getHolidays($pickupdays[0], $pickupdays[1]);
    
        if (empty($pickupdays) || empty($holidays)) {
            // Handle empty or invalid data
            return null; // Or throw an exception as per your error handling strategy
        }
    
        $pickupday = $pickupdays[0];
        $maxIterations = 365; // Maximum iterations to prevent infinite looping
    
        for ($i = 0; $i < $maxIterations; $i++) {
            if (!in_array(strtotime($pickupday), $holidays)) {
                return $pickupday; // Found a non-holiday pickup day
            }
            $pickupday = date('Y-m-d', strtotime($pickupday . " +1 day"));
        }
    
        // Log error if all days within the range are holidays
        \Log::error('No pickupable day found within the range.');
        return null; // Or handle this case based on your application's logic
    }
    
    public function getPickupStartTimeOfSlots($slots)
    {
        $slotTime = QugeoScheduleSlot::where('slots', $slots)
            ->orderBy('id')
            ->value('starttime');
    
        if ($slotTime === null) {
            \Log::error('No start time found for the given slots: ' . $slots);
            // Handle the error condition, such as returning a default start time or throwing an exception
            return null; // Or return a default value or throw an exception as per your application's logic
        }
    
        return $slotTime;
    }
    

    public function getPickupdays()
    {
        $today = date('Y-m-d');
        $now = date("His");
        $dets = $this->getPickupScheduleSlots($now);
    
        if (empty($dets)) {
            $today = date('Y-m-d', strtotime("+1 day"));
        }
    
        $tilldate = date('Y-m-d', strtotime("+30 days"));
    
        return [$today, $tilldate];
    }
    
    public function getPickupScheduleSlots($startTime = 0)
    {
        $slots = QugeoScheduleSlot::where('availabletill', '>', $startTime)
            ->orderBy('availabletill')
            ->pluck('slots')
            ->toArray();
    
        return $slots;
    }
    

    public function updatePickupSchedule($order, $scheduleTime, $otherBookings, $startTime, $endTime)
    {
        $query = QugeoOrder::where('quor_PickupPincode', $order['PickupPincode'])
            ->whereBetween('quor_ScheduleOpeningTime', [$startTime, $endTime]);

        if (!$otherBookings) {
            // Defer this booking
            $query->where('quor_id', $order['quor_id']);
        } else {
            // Defer other bookings from the same location in the same period
            $query->where('quor_PickupToBeManual', 0);
        }

        $query->update(['quor_ScheduleOpeningTime' => $scheduleTime]);
    }


    public function getIsPickableToday($startTime = 0)
    {
        // Check if today is a holiday
        $holidaysCount = $this->getHolidays(date('Y-m-d'), date('Y-m-d'));
    
        if ($holidaysCount > 0) {
            return 0; // No pickup available on holidays
        }
    
        // Count the available pickup slots
        $slotCount = QugeoScheduleSlot::where('endtime', '>', $startTime)
            ->count();
    
        return $slotCount;
    }
    
    public function getHolidays($startDate, $endDate)
    {
        $holidays = Holiday::whereBetween('holi_days', [$startDate, $endDate])
            ->orderBy('holi_days', 'asc')
            ->pluck('holi_days')
            ->toArray();
    
        return $holidays;
    }
    
    public function getEarliestProcessingTime($time)
    {
        // Check if there's a slot where $time is between starttime and endtime
        $slotTime = QugeoScheduleSlot::where('starttime', '<=', $time)
            ->where('endtime', '>=', $time)
            ->orderBy('starttime')
            ->value('starttime');

        if (!$slotTime) {
            // If no slot is found, find the next available slot after $time
            $slotTime = QugeoScheduleSlot::where('starttime', '>', $time)
                ->orderBy('starttime')
                ->value('starttime');
        }

        if ($slotTime) {
            echo "slottime: " . $slotTime . "\n";
            return $slotTime;
        } else {
            echo "No slot found.\n";
            // Handle the case where no slot is found
            return null;
        }
    }
 
    private function getBestSecondaryQugeoCandidate($vehicledetails, $orderid, $isPickup, $ignorepreviouspushes)
    {
        // Filter out vehicles based on previous pushes if needed
        if (!$ignorepreviouspushes) {
            $vehicledetails = $vehicledetails->reject(function ($row) use ($orderid, $isPickup) {
                return $this->WasQueued($orderid, $row['apikey'], $isPickup) > 0;
            });
        }
    
        // Extract relevant data for sorting
        $sorted = $vehicledetails->map(function ($row) {
            return [
                'vehicle' => $row,
                'currentload' => $row['CurrentLoadedWeight'],
                'ratePerKm' => $row['RatePerKm'],
                'capacity' => $row['capacity'],
            ];
        })->sortBy('distance');
    
        // Check if there are vehicles available after filtering
        if ($sorted->isNotEmpty()) {
            return $sorted->first()['vehicle'];
        }
    
        return false;
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

    public function scheduleADeliverySchJobs($quorid, &$order, $schedule = true, $specificvehicle = '', $Manualpush = true, $WasDirectDeliveryPickUp = false, $deliveringbranch, $quorIds, $totalOrders, $currentOrderCount, $type) {
        $booking = QugeoOrder::select(
            'quor_id',
            'quor_RefNo',
            'quor_QugeoPickupDDBOrderId',
            'quor_QugeoDeliveryDDBOrderId',
            'quor_PickupToBeManual',
            'quor_ScheduleOpeningTime',
            'quor_Date',
            'quor_PickupName',
            'quor_PickupAddress',
            'quor_PickupLocation',
            'quor_PickupPhone',
            'quor_PickupLat as pickupLat',
            'quor_PickupLng as pickupLng',
            'quor_PickupSMS',
            'quor_PickupStage1Distance',
            'quor_PickupStage2Distance',
            'quor_DeliveryName',
            'quor_DeliveryAddress',
            'quor_DeliveryLocation',
            'quor_DeliveryPhone',
            'quor_DeliveryLat as deliveryLat',
            'quor_DeliveryLng as deliveryLng',
            'quor_DeliverySMS',
            'quor_DeliveryStage1Distance',
            'quor_DeliveryStage2Distance',
            'quor_CreatedOn',
            'quor_Status',
            'quor_DistanceinKM',
            'quor_LastScheduleRun',
            'quor_FirstScheduleRun',
            'quor_PickupPincode',
            'quor_DeliveryPincode',
            'quor_Pickupbr_id',
            'quor_Deliverybr_id',
            'quor_AmountCollectible'
        )
        ->where('quor_id', $quorid)
        ->first();
        $orderid = '';
        if (!empty($booking)) {
      
            $orderid = $this->getDeliveryOrderAndPollItSchJobs($booking, $schedule, $specificvehicle, $Manualpush, $WasDirectDeliveryPickUp, $deliveringbranch, $quorIds, $totalOrders, $currentOrderCount, $type);
    
        }
        return $orderid;
    }
    public function getDeliveryOrderAndPollItSchJobs($booking, $schedule, $specificVehicle, $manualPush, $WasDirectDeliveryPickUp = true, $deliveringBranch, $quorIds, $totalOrders, $currentOrderCount, $type)
    {
      
        try {
        
                $consignment = QugeoOrderDetail::select('quod_id as detid', 'quor_RefNo as RefNo', 'quor_IsBarcode as Barcode')
                    ->where('quor_id', $booking->quor_id)
                    ->first();
    
                    
        
                if (!$consignment) {
                    throw new \Exception("Error fetching consignment for booking " .$booking->bk_no);
                }
            
    
                $order = null;
               
                $orderid = $this->CreateOrder(false, $booking, 0, $consignment, $manualPush);
               
                if (empty($orderid)) {
                    throw new \Exception("Error creating order for booking " . $booking->bk_no);
                }
     
              
               
                if($schedule) {
                 
                    $createdPoll = $this->CreateAPollSchJob($orderid, true, $order, $specificVehicle, $quorIds, $totalOrders, $currentOrderCount, $booking['quor_id']);
    
                  
                    $quorType['quor_UpdateOn'] = now();
    
                    QugeoOrder::whereNotIn('quor_Status', [15, 38])
                        ->where('quor_id', $booking->quor_id)
                        ->update([
                            'quor_Type' => 1,
                            'quor_UpdateOn' => $quorType['quor_UpdateOn'],
                            'quor_QugeoDeliveryDDBOrderId' => $orderid,
                            'quor_Status' => config('constant.ORDER_DELIVERY_POLLED_DLS_ID'),
                        ]);
    
                    if ($type !== 'Sch' && !$createdPoll) {
                        $this->handoverForManualScheduling($orderid, false, $order);
                    }
                }
                if($this->request_from==null)
                {
                    return $orderid;
                }
                else
                {
                    return isset($createdPoll) ? $createdPoll : $orderid;
                }

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error or handle it as per your application's error handling strategy
            return null;
        }
    }
    

    private function delayedDeliveryStarted($quorID = NULL)
    {
        $qugeoOrder = QugeoOrder::where('quor_id', $quorID)->value('quor_TransferOrder_id');
        $orderID = TransferOrder::where('fsto_id',@$quor_TransferOrder_id)->value('fstr_id');
        event(new DelayedOrderEvent($orderID, 6));
    }
        
}
