<?php
namespace App\Http\Repositories\Driver;

use Illuminate\Support\Facades\DB;
use App\Models\Drivers\QugeoOrder;
use App\Models\Drivers\QugeoDriver;
use App\Models\Drivers\SysConfiguration;
use App\Models\Drivers\FirebaseLog;
use App\Models\Drivers\RetalineCustomerOrder;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
use Illuminate\Support\Facades\Auth;
use App\Traits\Driver\LocationTrait;
use App\Traits\Driver\StatusUpdate;
use Aws\DynamoDb\DynamoDbClient;
use BackOffice\Status\{
    QugeoStatus,
    CustomerOrderStatus
};

use App\Models\Order;


class PolledOrderRepository
{
    use LocationTrait;
    use StatusUpdate;
    public function __construct()
    {
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
    }
    public function pollOrder($request)
    {
        try
        {
            $driver = auth_user();
            $order = Order::from("retaline_customer_order as rc")
            ->join("retaline_customer_order_status as rs", "rs.status_id", "rc.status_id")
            ->join('qugeo_order as qo', 'qo.quor_RefNo', 'rc.order_order_id')
            ->where([
                ['order_order_id', $request->order_id],
                ["rs.stage_id", 5],

            ])
            ->where(function ($query) use ($driver) {
                $query->where('qo.quor_Pickupbr_id', $driver->br_id)->orWhere('qo.quor_Deliverybr_id', $driver->br_id);
            })
            ->first();
            if(!$order)
            {
                return new ErrorResponse("Order not available");
            }
            $this->updateLocation($request->location, [
                "order_id"  => $request->order_id,
                "event"     => "Poll Order",
                "driver"    => $driver
            ]);
            $this->updateFirebaseLog($driver, 2);
            $response = $this->markAsAccepted($request, $order, $driver);

            return $response;
        }
        catch(\Excepion $e)
        {
            info("pollOrder Error");info($e);
            return new ErrorResponse("Operation failed");
        }

    }

    public function denyPolledOrder($orderID)
    {
        try
        {
            $driver = auth_user();
            $order = QugeoOrder::where("quor_RefNo", $orderID)
            ->where(function ($query) use ($driver) {
                $query->where('quor_Pickupbr_id', $driver->br_id)->orWhere('quor_Deliverybr_id', $driver->br_id);
            })
            ->first();
            if($order)
            {
                $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
                $description = "Driver ID={$driver->d_ID}, Driver Name={$driver->d_Name} {$driver->l_Name}, Order ID={$orderID}, Branch ID={$order->quor_Pickupbr_id}, Action=Order Denied";
                $addLog = $this->dynamoClient->putItem([
                    'TableName' =>config('aws.prefix').'activitylogs',
                    'Item' =>  [
                        "uuid"          => ["S" => $uuid],
                        "tstamp"        => ["S" => date("Y-m-d H:i:s")],
                        "source"        => ["S" => "Grozeo Drive"],
                        "User"          => ["S" => "{$driver->d_Name} {$driver->l_Name}"],
                        "Description"   => ["S" => $description],
                        "storegroupid"  => ["N" => (string)@$order->branch->br_storeGroup]
                    ]
                ]);
                $this->updateFirebaseLog($driver, 3);
                return new SuccessResponse("Order Denied");
            }
            return new ErrorResponse("Operation failed");
        }
        catch(\Excepion $e)
        {
            return new ErrorResponse($e);
        }
    }

    private function updateFirebaseLog($driver, $status = 2)
    {
        FirebaseLog::where([
            ['rfir_StatusId', 1],
            ['rfir_token', $driver->gcmregstid]
        ])->update([
            'rfir_StatusId' => $status
        ]);        
    }
    private function markAsAccepted($request, $order, $driver)
    {
        $status = (@$order->drive->quor_TransferOrder_Type == 2) ? QugeoStatus::ORDER_PICKUP_FLAGGED_TOBR_DLS_ID : QugeoStatus::ORDER_PICKUP_FLAGGED_TODST_DLS_ID;

        $DDBOrderID = $this->checkDDB($order, $status, $driver);
        $updated = QugeoOrder::where('quor_id', $order->drive->quor_id)->update([
            'quor_Status'                   => $status,
            'quor_QugeoPickupDDBOrderId'    => $DDBOrderID,
            'quor_SchedulePickupTime'       => now(),
            'quor_PickupDriverId'           => $driver->d_ID,
            'quor_DeliveryDriverId'         => $driver->d_ID,
        ]);
        $order->status_id = CustomerOrderStatus::DELIVERY_ASSIGNED;
        $order->save();
        return ($updated) ? new SuccessResponse("Order Accepted") : new ErrorResponse("Operation failed");
    }
    private function checkDDB($order, $status, $driver)
    {
        $drive = $order->drive;
        // return $drive;
        $params = [
            "TableName"                 => config("aws.prefix")."QugeoOrderDetails",
            "IndexName"                 => "quor_id-index",
            "KeyConditionExpression"    => "quor_id = :quor_id",
            "ExpressionAttributeValues" => [
                ":quor_id"  => ["N" => (string)$drive->quor_id]
            ],
        ];
        $response = $this->dynamoClient->query($params);
        if($response['Count'] > 0)
        {
            $item = reset($response['Items']);
            $this->dynamoClient->updateItem([
                'TableName' =>config('aws.prefix') . 'QugeoOrderDetails',
                'Key' => [
                    'orderid' => $item['orderid'],
                ],
                'ExpressionAttributeValues' => [
                        ':OrderStatus'              => ['N' => (string)$drive->quor_Status]
                    ],
                'UpdateExpression'          => 'SET OrderStatus=:OrderStatus'
            ]);
            return $item['orderid']['S'];
        }
        else
        {
            $orderID = $this->createOrderEntry($order, $status);
            return $orderID;
        }
        return NULL;
    }

    private function createOrderEntry($order, $status)
    {
        $orderid = sha1(microtime(true) . mt_rand(10000, 90000));
        $validate = date("Ymd");
        $validatetime = date("YmdHis");
        $this->dynamoClient->putItem([
            'TableName' => config('aws.prefix').'QugeoOrderDetails',
            'Item'      => $this->qugeoOrderDetailsInsert($orderid, $validate, $order->drive, $status, $validatetime)
        ]);
        return $orderid;
    }

    private function qugeoOrderDetailsInsert($orderid, $validate, $qOrder, $status, $validatetime)
    {
        $qOrderDetails = [
            "detid"     => @$qOrder->details->detid,
            "RefNo"     => @$qOrder->details->RefNo,
            "Barcode"   => @$qOrder->details->Barcode,
        ];
        return [
            'orderid'                   => ['S' => (string)$orderid],
            'date'                      => ['S' => (string)$qOrder->quor_Date],
            'IsPickup'                  => ['N' => '1'],
            'ManuallyPushed'            => ['N' => '0'],
            'createddatetime'           => ['N' => (string)$validatetime],
            'updateddatetime'           => ['N' => (string)$validatetime],
            'createddate'               => ['N' => (string)$validate],
            'quor_id'                   => ['N' => (string)$qOrder->quor_id],
            'quor_RefNo'                => ['S' => (string)$qOrder->quor_RefNo],
            'IsMilestoneLock'           => ['N' => '0'],
            'MilestoneCovered'          => ['N' => '0'],
            'OrderStatus'               => ['N' => (string)$qOrder->quor_Status],
            'Acceptedapikey'            => ['S' => ' - '],
            'bkno'                      => ['S' => (string)$qOrder->quor_RefNo],
            'pickupmobile'              => ['S' => (string)$qOrder->quor_PickupPhone],
            'pickupname'                => ['S' => (string)$qOrder->quor_PickupName],
            'pickupaddress'             => ['S' => (string)$qOrder->quor_PickupAddress],
            'pickuplocation'            => ['S' => (string)$qOrder->quor_PickupLocation],
            'pickupOTP'                 => ['S' => (string)$qOrder->quor_PickupSMS],
            'pickupLat'                 => ['N' => (string)($qOrder->quor_PickupLat ?? 0)],
            'pickupLng'                 => ['N' => (string)($qOrder->quor_PickupLng ?? 0)],
            'pickupStage1Distance'      => ['N' => (string)$qOrder->quor_PickupStage1Distance],
            'pickupStage2Distance'      => ['N' => (string)$qOrder->quor_PickupStage2Distance],
            'deliverymobile'            => ['S' => (string)$qOrder->quor_DeliveryPhone],
            'deliveryname'              => ['S' => (string)$qOrder->quor_DeliveryName],
            'deliveryaddress'           => ['S' => (string)$qOrder->quor_DeliveryAddress],
            'deliverylocation'          => ['S' => (string)$qOrder->quor_DeliveryLocation],
            'deliveryOTP'               => ['S' => (string)$qOrder->quor_DeliverySMS],
            'deliveryLat'               => ['N' => (string)($qOrder->quor_DeliveryLat ?? 0)],
            'deliveryLng'               => ['N' => (string)($qOrder->quor_DeliveryLng ?? 0)],
            'deliveryStage1Distance'    => ['N' => (string)$qOrder->quor_DeliveryStage1Distance],
            'deliveryStage2Distance'    => ['N' => (string)$qOrder->quor_DeliveryStage2Distance],
            'FirstScheduleRun'          => ['S' => (string)$qOrder->quor_FirstScheduleRun],
            'TotalDistKM'               => ['N' => (string)$qOrder->quor_DistanceinKM],
            'pktcount'                  => ['S' => '0'],
            'totwt'                     => ['S' => '0'],
            'netamt'                    => ['S' => (string)$qOrder->quor_AmountCollectible],
            'Consignment'               => ['S' => json_encode($qOrderDetails)],
            'IsClosed'                  => ['N' => '0'],
            'PickupPincode'             => ['S' => (string)$qOrder->quor_PickupPincode],
            'DeliveryPincode'           => ['S' => (string)$qOrder->quor_DeliveryPincode],
            'Lat'                       => ['S' => (string)$qOrder->quor_PickupLat],
            'Lng'                       => ['S' => (string)$qOrder->quor_PickupLng],
            'DelAssetBucket'            => ['S' => ' - '],
            'ImgSignLocation'           => ['S' => ' - '],
            'ImgCustLocation'           => ['S' => ' - '],
            'Location'                  => ['S' => (string)$qOrder->quor_PickupLocation],
            'HandlingBranch'            => ['N' => (string)$qOrder->quor_Pickupbr_id],
            'AcceptedAsDirectDelivery'  => ['S' => '-1']
        ];
    }

   public function pollOrder1($request)
   {
        $apikey=auth_user()->d_apikey;
        $orderStatus = QugeoOrder::where('quor_QugeoPickupDDBOrderId', $request['orderid'])
                    ->orWhere('quor_QugeoDeliveryDDBOrderId', $request['orderid'])
                    ->value('quor_Status');
        $validStatuses = [22, 23, 24, 25, 27, 31];
        if (!in_array($orderStatus, $validStatuses)) {
            //  return new ErrorResponse("Order already accepted by another driver. Please select a different order.");
        }
        $extrainfo = [
            "event" => "pollreponse",
            "responsedetails" => [
                "orderid" => $request["orderid"],
                "hasaccepted" => $request['hasaccepted'],
                "msgid" => $request['msgid']
            ]
        ];
        
        $this->updateLocation($request['geocoords'],$extrainfo );

        $arrSession = [];
        // Define an empty array for next order details
        $nextorder = [];
        // Set initial values for next order details
        $nextorder['istriprerouted'] = false;
        $nextorder['mapdetails'] = [];
        $nextorder['nextorderdetails'] = [];

        if ($request['hasaccepted'] == 'true') {
            FirebaseLog::where('rfir_StatusId', 1)
                ->where('rfir_token', $request['fcm_token'])
                ->update(['rfir_StatusId' => 2]);
        } else {
            FirebaseLog::where('rfir_StatusId', 1)
                ->where('rfir_token', $request['fcm_token'])
                ->update(['rfir_StatusId' => 3]);
        }

        $udpatedorder = $this->UpdateOrderOnPoll(($request['hasaccepted'] == 'true' ? 1 : 2), $request['orderid'],$apikey, ($request['delivertobranch'] == 'true' ? true : false), $orderdetails);
      
        $udpatedorder = true; // Assuming $udpatedorder is defined and has a boolean value

    if ($request->has('hasaccepted') && $request->input('hasaccepted') == 'true' && $udpatedorder) {
        if ($udpatedorder) {
           
            $assigned = $this->AssignOrderToQugeoDriverSch($request->input('orderid'), $apikey, $orderdetails, $nextorder, $isnewroute);

          
            if ($isnewroute) {
                $arrSession['msg'] = 'New route reworked';
            } else {
                $arrSession['msg'] = 'No change in route';
            }
            $arrSession['success'] = true;
        } else {
            $arrSession['msg'] = 'Unable to assign order';
            throw new \Exception('Unable to assign order');
        }
    } else {
        $arrSession['success'] = true;
        $arrSession['msg'] = 'No changes in the route';
    }

  
    $arrSession['Data']['vehicle'] = $nextorder;
    return $arrSession;

   }
   public function UpdateOrderOnPoll($pollresponse, $orderid, $apikey, $delivertobr, &$orderdetails, $getOrderDetails = true)
   {      
   
         $valdate = date("YmdHis");
        if ($getOrderDetails) {
            $result = $this->dynamoClient->getItem([
                'TableName' =>config('aws.prefix') . 'QugeoOrderDetails',
                'Key' => [
                    'orderid' => ['S' => $orderid],
                ],
                /*'AttributesToGet' => [
                    'orderid', 'IsPickup', 'quor_id', 'Consignment', 'pickupLat',
                    'pickupLng', 'TotalDistKM', 'deliveryLat', 'deliveryLng', 'Lat', 'Lng', 'quor_RefNo',
                    'pickupmobile', 'deliverymobile', 'pickupname', 'pickupaddress', 'deliveryname',
                    'deliveryaddress', 'pickuplocation', 'deliverylocation', 'HandlingBranch', 'IsMilestoneLock',

                ]*/
            ]);

            // dd($result['Item']);
    
            if (isset($result) && count($result) > 0) {
                $lat = isset($result['Item']['Lat']['S']) ? $result['Item']['Lat']['S'] : $result['Item']['Lat']['N'];
                $lng = isset($result['Item']['Lng']['S']) ? $result['Item']['Lng']['S'] : $result['Item']['Lng']['N'];
                $consignment=isset($result['Item']['Consignment']['S']) ? $result['Item']['Consignment']['S'] : $result['Item']['Consignment']['L'];

                $orderdetails = [];
                $orderdetails['orderid'] =$result['Item']['orderid']['S'];
                $orderdetails['quor_id'] =$result['Item']['quor_id']['N'];
                $orderdetails['IsPickup'] =$result['Item']['IsPickup']['N'];
                $orderdetails['Consignment'] =$consignment;
                $orderdetails['pickupLat'] = (@$result['Item']['pickupLat']['N'] ? $result['Item']['pickupLat']['N'] : @$result['Item']['pickupLat']['S']);
                $orderdetails['pickupLng'] = (@$result['Item']['pickupLng']['N'] ? $result['Item']['pickupLng']['N'] : @$result['Item']['pickupLng']['S']);
                $orderdetails['deliveryLat'] = (@$result['Item']['deliveryLat']['N'] ? $result['Item']['deliveryLat']['N'] : @$result['Item']['deliveryLat']['S']);
                $orderdetails['DistSrcToBr'] = (@$result['Item']['TotalDistKM']['N'] ? $result['Item']['TotalDistKM']['N'] : @$result['Item']['TotalDistKM']['S']);
                $orderdetails['DistSrcToDst'] = (@$result['Item']['TotalDistKM']['N'] ? $result['Item']['TotalDistKM']['N'] : @$result['Item']['TotalDistKM']['S']);
                $orderdetails['deliveryLng'] = (@$result['Item']['deliveryLng']['N'] ? $result['Item']['deliveryLng']['N'] : @$result['Item']['deliveryLng']['S']);
                $orderdetails['BrLat'] =$lat;
                $orderdetails['BrLong'] =$lng;
                $orderdetails['quor_RefNo'] =$result['Item']['quor_RefNo']['S'];
                $orderdetails['Srcmobile'] =$result['Item']['pickupmobile']['S'] ;
                $orderdetails['Dstmobile'] =$result['Item']['deliverymobile']['S'] ;
                $orderdetails['Srcname'] =$result['Item']['pickupname']['S'] ;
                $orderdetails['Dstname'] = $result['Item']['deliveryname']['S'];
                $orderdetails['Srcaddress'] =$result['Item']['pickupaddress']['S'];
                $orderdetails['Dstaddress'] =$result['Item']['deliveryaddress']['S'];
                $orderdetails['pickuplocation'] =$result['Item']['pickuplocation']['S'];
                $orderdetails['deliverylocation'] =@$result['Item']['deliverylocation']['S'];
                $orderdetails['totwt'] = 0;
                $orderdetails['totvol'] = 0;
                $orderdetails['HandlingBranch'] =$result['Item']['HandlingBranch']['N'];
                $orderdetails['IsMilestoneLock'] = (@$result['Item']['IsMilestoneLock']['N'] ? $result['Item']['IsMilestoneLock']['N'] : @$result['Item']['IsMilestoneLock']['S']);

              
            } else {
                return false;
            }
           
        }

        $item= [
            'Acceptedapikey' => [
                'Action' => 'PUT',
                'Value' => ['S' => (string) $apikey]
            ],
            'AcceptedAt' => [
                'Action' => 'PUT',
                'Value' => ['N' => (string) $valdate]
            ],
            'updateddatetime' => [
                'Action' => 'PUT',
                'Value' => ['N' => (string) $valdate]
            ],
        ];

        
        if ($orderdetails['IsPickup'] == 1)
        {     
           
            if ($pollresponse == 1) {


            $orderQuorId = $orderdetails['quor_id'];
            $status = $delivertobr ? config('constant.ORDER_PICKUP_FLAGGED_TOBR_DLS_ID') : config('constant.ORDER_PICKUP_FLAGGED_TODST_DLS_ID');
            $pickupDDBOrderId = $orderid;
            $pickupDDBDriverId = $apikey;
            $schedulePickupTime = date("Y-m-d H:i:s", strtotime($valdate));

            QugeoOrder::where('quor_id', $orderQuorId)->update([
                'quor_Status' => $status,
                'quor_QugeoPickupDDBOrderId' => $orderid,
                'quor_QugeoPickupDDBDriverId' => $apikey,
                'quor_SchedulePickupTime' => $schedulePickupTime,
                'quor_PickupDriverId' => auth_user()->d_ID,
            ]);

            $updateurl = QugeoOrder::where('quor_id',$orderQuorId)
                        ->value('quor_StatusUpdateQry');

            $updateurl = $this->getQugeoParentStatusUpdated($updateurl, ($delivertobr == false ? config('constant.ORDER_PICKUP_FLAGGED_TODST_DLS_ID') : config('constant.ORDER_PICKUP_FLAGGED_TOBR_DLS_ID')));
            $updateurl = str_replace("###6", "1", $updateurl);
            $updateurl = str_replace("###2", "", $updateurl);
            $execQry = explode(";", $updateurl);
            $updateurl = QugeoOrder::where('quor_id', $orderdetails['quor_id'])->value('quor_TrackingUpdateQry');
            $TrackingUpdate = str_replace("###1", $orderid, config('constant.QUGEO_TRACKING_API_GATEWAY'));
            $TrackingUpdate = str_replace("###2", config('constant.AWSDYNAMODBTABLEPREFIX'), $TrackingUpdate);
            $updateurl = str_replace("###1", $TrackingUpdate, $updateurl);
            $updateurl = str_replace("###6", "1", $updateurl);
            $driver = QugeoDriver::where('d_ID', auth_user()->d_ID)->first();
            
            if ($driver) {
                $DriverName = $driver->d_Name;
                $DriverPhone = $driver->d_Ph1;
            } else {
                // Handle case where driver data is not found
                $DriverName = null;
                $DriverPhone = null;
            }
            $updateurl = str_replace("##10", addslashes($DriverName), $updateurl);
            $updateurl = str_replace("##11", addslashes($DriverPhone), $updateurl);
            $item['OrderStatus'] =  [
                'Action' => 'PUT',
                'Value' => ['N' => (string) ($delivertobr == false ? config('constant.ORDER_PICKUP_FLAGGED_TODST_DLS_ID') : config('constant.ORDER_PICKUP_FLAGGED_TOBR_DLS_ID'))]
            ];

            $item['AcceptedAsDirectDelivery'] =
            [
                'Action' => 'PUT',
                'Value' => ['S' => (string) ($delivertobr == false ? true : false)]
            ];
            
            }
            else
            {
                $status = ($pollresponse == 2) ? config('constant.ORDER_PICKUP_POLL_REJECTED_DLS_ID') : config('constant.ORDER_PICKUP_POLL_NORESP_DLS_ID');
                QugeoOrder::where('quor_id', $orderdetails['quor_id'])->update(['quor_Status' => $status]);
                $item['OrderStatus'] =[
                    'Action' => 'PUT',
                    'Value' => ['N' => (string) ($pollresponse == 2 ? config('constant.ORDER_PICKUP_POLL_REJECTED_DLS_ID') : config('constant.ORDER_PICKUP_POLL_NORESP_DLS_ID'))]
                ];
                
            }
        }
        else
        {
            

            QugeoOrder::where('quor_id', $orderdetails['quor_id'])
            ->update([
                'quor_Status' => $pollresponse == 1 ? config('constant.ORDER_DELIVERY_OUT_FOR_DELIVERY') : ($pollresponse == 2 ? config('constant.ORDER_DELIVERY_POLL_REJECTED_DLS_ID') : config('constant.ORDER_DELIVERY_POLL_NORESP_DLS_ID')),
                'quor_QugeoDeliveryDDBDriverId' => $apikey,
                'quor_QugeoDeliveryDDBOrderId' => $orderid,
                'quor_ScheduleDeliveryTime' => date("Y-m-d H:i:s", strtotime($valdate)),
                'quor_DeliveryDriverId' => auth_user()->d_ID
            ]);

            $item['OrderStatus'] = ['Action' => 'PUT', 'Value' => ['N' =>(string) ($pollresponse == 1 ? config('constant.ORDER_DELIVERY_OUT_FOR_DELIVERY') : ($pollresponse == 2 ? config('constant.ORDER_DELIVERY_POLL_REJECTED_DLS_ID') : config('constant.ORDER_DELIVERY_POLL_NORESP_DLS_ID')))]];
            $item['AcceptedAsDirectDelivery'] = ['Action' => 'PUT', 'Value' => ['S' => (string) -1]];
            
        }
       
    
        $this->dynamoClient->updateItem([
            'TableName' =>config('aws.prefix') . 'QugeoOrderDetails',
            'Key' => [
                'orderid' => ['S' => $orderid],
            ],

            'AttributeUpdates' =>$item
        ]);

      
       
        return true;

   }
   public function AssignOrderToQugeoDriverSch($orderid, $apikey, $orderdetails, &$nextorder, &$isnewroute, $getOrder = true)
   {
    
       $arrOrder = [];
       $rsno = $this->dynamoClient->getItem([
        'TableName' => config('aws.prefix') . 'QugeoLiveVehicles',
        'Key' => [
            'apikey' => ['S' =>  $apikey],
        ],
        'AttributesToGet' => [
            'Latitude', 'Longitude', 'Home_Latitude', 'Home_Longitude', 'AssignedLoadedWeight', 'AssignedLoadedVolume', 'CurrentLoadedVolume', 'CurrentLoadedWeight', 'TotalJobs', 'DriverName', 'mobno'

        ]
    ]);

    $AssignedLoadedWeight = floatval($rsno['Item']['AssignedLoadedWeight']['N']);
    $AssignedLoadedVolume = floatval($rsno['Item']['AssignedLoadedVolume']['N']);
    $CurrentLoadedVolume = floatval($rsno['Item']['CurrentLoadedVolume']['N']);
    $CurrentLoadedWeight = floatval($rsno['Item']['CurrentLoadedWeight']['N']);
    $TotJobs = intval($rsno['Item']['TotalJobs']['N']??0);
    $DriverName = $rsno['Item']['DriverName']['S'];
    $DriverMobNo = $rsno['Item']['mobno']['S'];

    

       
       if($orderid)
       {
       
        $rsno = $this->dynamoClient->getItem([
            'TableName' =>config('aws.prefix') . 'QugeoLiveVehicleOrders',
            'Key' => [
                'apikey' => ['S' => $apikey],
                'orderid' => ['S' => $orderid],
            ],
            'AttributesToGet' => [
                'orderid', 'order', 'Latitude', 'Longitude', 'IsClosed', 'IsLiveOrder', 'IsPickup', 'IsMilestoneLock'
            ]
        ]);
     
       }
     
        $order = [];
        $waypoints = '';
        $LiveOrder = '';
        $IsMilestoneLocked = 0;
        $NewOrderLat = ($orderdetails['IsPickup'] == 1 ? $orderdetails['pickupLat'] : $orderdetails['deliveryLat']);
        $NewOrderLong = ($orderdetails['IsPickup'] == 1 ? $orderdetails['pickupLng'] : $orderdetails['deliveryLng']);
        $CurrentOpenOrders = [];
        $isLiveOrder = 1;
        $isOnlyOrder = true;
        $NewLiveOrder = $orderdetails['orderid'];
        $CurrentOrders = [];

        array_push($CurrentOpenOrders, [
            'orderid' => $orderdetails['orderid'],
            'Latitude' => $NewOrderLat,
            'Longitude' => $NewOrderLong,
            'IsClosed' => 0,
            'IsLiveOrder' => 0,
            'IsPickup' => $orderdetails['IsPickup'],
            'order' => config('constant.QUGEO_LIVE_ORDER_INCREMENT_VALUE'),
        ]);

        $isnewroute = false;
        $nextorder = [];

     
       $isSignature = SysConfiguration::where('cfg_Name', 'IS_SIGNATURE')->value('cfg_Value');
        // If you need to convert the result to a boolean, you can use type casting
        $isSignature = (bool) $isSignature;

       $nextorder['isSignature'] = $isSignature;
       $nextorder['istriprerouted'] = false;
       $nextorder['mapdetails'] = array();
       $nextorder['nextorderdetails'] = array();

     
    
       foreach ($CurrentOpenOrders as $value) {
   
      
           $item = [];
           $valdate = date("Ymd");
           $valdatetime = date("YmdHis");
           
           if ($value['orderid'] == $orderid) {

            $item = [
                'apikey' => ['S' => (string) $apikey],
                'orderid' => ['S' => (string) $orderid],
                'IsLiveOrder' => ['S' => (string) ($isOnlyOrder || $NewLiveOrder == $value['orderid'] ? 1 : 0)],
                'order' => ['S' => (string) $value['order']],
                'createddatetime' => ['N' => (string) $valdatetime],
                'createddate' => ['N' => (string) $valdate],
                'IsPickup' => ['N' => (string) $value['IsPickup']],
                'IsClosed' => ['N' => '0'],
                'Latitude' => ['N' => (string) $NewOrderLat],
                'Longitude' => ['N' => (string) $NewOrderLong],
                'HandlingBranch' => ['N' => (string) $orderdetails['HandlingBranch']],
                'IsMilestoneLock' => ['N' => '0'],

            ];
            
           

            $NewOrder = $this->dynamoClient->putItem([
                'TableName' =>config('aws.prefix') . 'QugeoLiveVehicleOrders',
                'Item' =>  $item,
            ]);
           
            $this->dynamoClient->updateItem([
                'TableName' =>config('aws.prefix') . 'QugeoLiveVehicles',
                'Key' => [
                    'apikey' => ['S' => $apikey],
                ],
                'UpdateExpression' => 'SET IsEngaged = :isEngagedVal, TotalJobs = :totalJobsVal',
                'ExpressionAttributeValues' => [
                    ':isEngagedVal' => ['N' => '1'],
                    ':totalJobsVal' => ['N' => (string)($TotJobs + 1)],
                ]
            ]);

        } else {

         
               $item['IsLiveOrder'] = ['N' =>  (string) ($NewLiveOrder == $value['orderid'] ? 1 : 0)];
               $item['order'] = ['N' =>  (string) $value['order']];

               $this->dynamoClient->updateItem([
                'TableName' => config('aws.prefix') . 'QugeoLiveVehicleOrders',
                'Key' => [
                    'apikey' => ['S' => $apikey]
                ],
    
                'AttributeUpdates' =>$item
            ]);

           }
          
           if ($getOrder && $IsMilestoneLocked == 0) {
               if ($NewLiveOrder != $LiveOrder && $value['orderid'] == $NewLiveOrder) {
        
                   $rsno = $this->dynamoClient->getItem([
                    'TableName' => config('aws.prefix') . 'QugeoOrderDetails',
                    'Key' => [
                        'orderid' => ['S' =>  $NewLiveOrder],
                    ],
                    'AttributesToGet' => [
                        'pickupLat', 'pickupLng', 'deliveryLat', 'deliveryLng', 'BrLat', 'BrLong', 'pickupmobile', 'deliverymobile', 'AcceptedAsDirectDelivery', 'pickupname', 'deliveryname', 'pickupaddress', 'deliveryaddress', 'pickuplocation', 'deliverylocation', 'quor_RefNo', 'pktcount', 'chrgwt', 'netamt', 'DelAssetBucket', 'ImgSignLocation', 'ImgCustLocation', 'IsPickup', 'totwt', 'Consignment', 'pickupOTP', 'deliveryOTP', 'quor_id', 'HandlingBranch'
                    ]
                ]);
                

                   $isnewroute = true;
                   if ($rsno['Item']['IsPickup'] == '1') {
                       $lat = (@$rsno['Item']['pickupLat']['N'] ? $rsno['Item']['pickupLat']['N'] : @$rsno['Item']['pickupLat']['S']);
                       $long = (@$rsno['Item']['pickupLng']['N'] ? $rsno['Item']['pickupLng']['N'] : @$rsno['Item']['pickupLng']['S']);
                   } else {
                       $lat = (@$rsno['Item']['deliveryLat']['N'] ? $rsno['Item']['deliveryLat']['N'] : @$rsno['Item']['deliveryLat']['S']);
                       $long = (@$rsno['Item']['deliveryLng']['N'] ? $rsno['Item']['deliveryLng']['N'] : @$rsno['Item']['deliveryLng']['S']);
                   }
                   $nextorder['istriprerouted'] = true;

                   $quor_TransferOrder_Type = QugeoOrder::where('quor_RefNo', $rsno['Item']['quor_RefNo']['S'])->value('quor_TransferOrder_Type');

                   // If you expect the result to be a boolean, you can cast it accordingly
                   $quor_TransferOrder_Type = (bool) $quor_TransferOrder_Type;  

                   switch ($quor_TransferOrder_Type) {
                       case 1:
                           $orderPk = RetalineCustomerOrder::where('order_order_id', $rsno['Item']['quor_RefNo']['S'])->value('order_id');
                           break;
                       default:
                           $orderPk = 0;
                           break;
                   }
               
                   $nextorder['mapdetails'] = [
                    'latitude' => (float)$lat,
                    'longitude' =>(float) $long,
                    'zoomlevel' => '10',
                    'locationicon' => "https://maps.google.com/mapfiles/ms/icons/yellow-dot.png"
                ];
               
                $rsnoItemConsignment=isset($rsno['Item']['Consignment']['S']) ? $rsno['Item']['Consignment']['S'] : $rsno['Item']['Consignment']['L'];

                if (isset($rsno['Item']['Consignment']['L'])) {
                    $newArray = $rsnoItemConsignment;
                    $decodedJson = [];
                    foreach ($newArray as $item) {
                        $newItem = [];
                        foreach ($item['M'] as $key => $value) {
                            if (isset($value['S'])) {
                                // Check if the key is "detid" or "Barcode" and cast the value to integer
                                $newItem[$key] = in_array($key, ['detid', 'Barcode']) ? (int) $value['S'] : $value['S'];
                            }
                        }
                        $decodedJson[] = $newItem;
                    }
                } else {
                    $decodedJson = json_decode(strpos($rsnoItemConsignment, '[') !== false && strpos($rsnoItemConsignment, ']') !== false
                        ? $rsnoItemConsignment
                        : "[$rsnoItemConsignment]", true);
                }
                
                

                $totwt=isset($rsno['Item']['totwt']['S']) ? $rsno['Item']['totwt']['S'] : $rsno['Item']['totwt']['N'];
                $pktcount=isset($rsno['Item']['pktcount']['S']) ? $rsno['Item']['pktcount']['S'] : $rsno['Item']['pktcount']['N'];

                $nextorder['nextorderdetails'] = [
                    'ispickup' => ($rsno['Item']['IsPickup']['N'] == 1 ? true : false),
                    'order_id' => $orderPk,
                    'orderType' => $quor_TransferOrder_Type,
                    'orderno' => $rsno['Item']['quor_RefNo']['S'],
                    'customer' => ($rsno['Item']['IsPickup']['N'] == 1 ? $rsno['Item']['pickupname']['S'] : $rsno['Item']['deliveryname']['S']),
                    'address' => ($rsno['Item']['IsPickup']['N'] == 1 ? $rsno['Item']['pickupaddress']['S'] : $rsno['Item']['deliveryaddress']['S']),
                    'location' => ($rsno['Item']['IsPickup']['N'] == 1 ? $rsno['Item']['pickuplocation']['S'] : $rsno['Item']['deliverylocation']['S']),
                    'contph' => ($rsno['Item']['IsPickup']['N'] == 1 ? $rsno['Item']['pickupmobile']['S'] : $rsno['Item']['deliverymobile']['S']),
                    'noofboxes' => $pktcount,
                    'weight' => $totwt . 'kg',
                    'cashtobecollected' => $rsno['Item']['netamt']['S'],
                    'orderid' => $NewLiveOrder,
                    'orderdetails' => $decodedJson,
                    'otp' => ($rsno['Item']['IsPickup']['N'] == 1 ? $rsno['Item']['pickupOTP']['S'] : $rsno['Item']['deliveryOTP']['S'])
                ];

               
            
               
            
                   if ($rsno['Item']['IsPickup']['N'] == 0) {
                    $nextorder['nextorderdetails']['deluploadcreds'] = [
                        'S3BUCKET' => config('constant.QUGEO_S3_UPLOAD_BUCKET'),
                        'ImgSign' => $rsno['Item']['ImgSignLocation']['S'],
                        'ImgCust' => $rsno['Item']['ImgCustLocation']['S'],
                        'ACCESSKEY' => config('constant.QUGEO_S3_UPLOAD_ACCESS'),
                        'SECRETKEY' => config('constant.QUGEO_S3_UPLOAD_SECRET'),
                        'REGION' => config('constant.QUGEO_S3_UPLOAD_REGION'),
                        'ENDPOINT' => config('constant.QUGEO_S3_UPLOAD_ENDPOINT')
                    ];
                    
                       $qry = "Your " . config('constant.PROJECT_NAME') . " Order No." . $rsno['Item']['quor_RefNo']['S'] . " has been picked up by delivery partner " . $DriverName . ". Please check for items carefully while accepting the order";
                      
                       $templatedata['order_order_id'] = $rsno['Item']['quor_RefNo']['S'];
 
                    //    app(SmsSender::class)->fetchContentSendSms($templatedata, $rsno['deliverymobile'], 12);
                   } else {
                   
                   }
                   $rsnodeliveryLat = (@$rsno['Item']['deliveryLat']['N'] ? $rsno['Item']['deliveryLat']['N'] : @$rsno['Item']['deliveryLat']['S']);
                   $rsnodeliveryLng = (@$rsno['Item']['deliveryLng']['N'] ? $rsno['Item']['deliveryLng']['N'] : @$rsno['Item']['deliveryLng']['S']);
                   $this->UpdateReleasingLocation($apikey, $rsnodeliveryLat, $rsnodeliveryLng);
               }
           }
       }
   }

   public function UpdateReleasingLocation($apikey, $OnJobCompletionLatitude, $OnJobCompletionLongitude)
   {
    
    $latitudeValue = (float) $OnJobCompletionLatitude;
    $longitudeValue = (float) $OnJobCompletionLongitude;

    // Prepare the update item parameters
    $item = [
        'OnJobCompletionLatitude' => [
            'Action' => 'PUT',
            'Value' => ['N' => (string) $latitudeValue]
        ],
        'OnJobCompletionLongitude' => [
            'Action' => 'PUT',
            'Value' => ['N' => (string) $longitudeValue]
        ]
    ];

    // Perform the DynamoDB update operation
    $this->dynamoClient->updateItem([
        'TableName' =>config('aws.prefix') . 'QugeoLiveVehicles',
        'Key' => [
            'apikey' => ['S' => $apikey],
        ],
        'AttributeUpdates' => $item
    ]);
   }
}
