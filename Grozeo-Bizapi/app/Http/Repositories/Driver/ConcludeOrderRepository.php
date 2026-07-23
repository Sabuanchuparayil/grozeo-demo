<?php

namespace App\Http\Repositories\Driver;
use App\Models\Drivers\QugeoOrder;
use App\Models\Drivers\QugeoDriver;
use App\Models\Drivers\RetalineCustomerOrder;
use App\Models\Drivers\RetalineCustomer;
use App\Models\Drivers\QugeoDeliveryStatus;
use App\Models\Drivers\FinanceAutoPostingValue;
use App\Models\Drivers\FinanceEventMaster;
use App\Models\Drivers\FinascopStockTransferOrder;
use App\Models\Drivers\FinascopBranch;
use App\Models\Drivers\SysConfiguration;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
use Aws\DynamoDb\DynamoDbClient;
use App\Traits\Driver\LocationTrait;
use App\Traits\Driver\StatusUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Http\Repositories\Driver\PullPendingOrderRepository;
use App\Http\Repositories\Driver\PolledOrderRepository;
use App\Sms\SmsSender;
use Illuminate\Support\Facades\Log;
use App\Status\DelayedOrderActions;
use BackOffice\Models\TransferOrder;
use App\Http\Repositories\PostingRepository;
use Illuminate\Http\Request;
use App\Events\DelayedOrderActions as DelayedOrderEvent;

class ConcludeOrderRepository
{
    use LocationTrait;
    use StatusUpdate;
    public function __construct()
    {
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
    }

    public function concludeOrder($request)
    {
        $polled = new PolledOrderRepository();
        $apikey=auth_user()->d_apikey;
        $extrainfo = [
            "event" => "conclude",
            "responsedetails" => [
                "orderid" => $request["orderid"]
            ]
        ];
        
        $this->updateLocation($request['geocoords'],$extrainfo );
       
        try{

        $nors = $this->dynamoClient->getItem([
            'TableName' =>config('aws.prefix') . 'QugeoOrderDetails',
            'Key' => [
                'orderid' => ['S' => $request['orderid']],
            ],
            'AttributesToGet' => [
                'IsPickup', 'HasReCalculatedCharges', 'ReCalculatedCharges', 'quor_id', 'HasDirectDeliveryPickUp', 'totwt', 'totvol', 'AcceptedAsDirectDelivery', 'HandlingBranch', 'quor_RefNo', 'deliverymobile'
            ]
        ]);

        $totwt =isset($nors['Item']['totwt']['S']) ?floatval($nors['Item']['totwt']['S']):$nors['Item']['totwt']['N'];
        $totvol =0;

        $item = [];
        $valdatetime = date("YmdHis");

        $item['IsClosed'] =['Action' => 'PUT', 'Value' => ['N' => (string) 1]];
        $item['ClosedAt'] = ['Action' => 'PUT', 'Value' => ['N' => (string)$valdatetime]];
        $item['updateddatetime'] = ['Action' => 'PUT', 'Value' => ['N' => (string)$valdatetime]];
        
        
        $WasAdirectDeliveryPickup = false;
        $AssignedLoadedWeight = 0;
        $AssignedLoadedVolume = 0;
        $AssOper = '';
        $CurrentLoadedVolume = 0;
        $CurrentLoadedWeight = 0;
        $CurOper = '';

        if ($nors != false)
        {
            
           
            $JobNo = $nors['Item']['quor_RefNo']['S'];
            $deliverymobile = $nors['Item']['deliverymobile']['S'];
           
           
            if ($nors['Item']['IsPickup']['N'] == 1||$nors['Item']['IsPickup']['N'] == '1') {
                
          
                if ($request['failed'] == 'true') {

                   
                
                    $item['OrderStatus'] =  ['Action' => 'PUT', 'Value' => ['N' => (string)$request['failedreasonid']]];
                    $item['FailedReasonID'] = ['Action' => 'PUT', 'Value' => ['N' => (string)$request['failedreasonid']]];
                    QugeoOrder::where('quor_id', $nors['Item']['quor_id']['N'])
                    ->update(['quor_Status' => $request['failedreasonid']]);

                   

                    $deliveryStatus =QugeoDeliveryStatus::select('dls_DelStatus')->where('dls_ID',$request['failedreasonid'])->first();
                    // Check if the delivery status is found
                    if ($deliveryStatus) {
                        $delreason = $deliveryStatus->dls_DelStatus;
                    } else {
                        // Handle case where delivery status is not found
                        $delreason = null; // Or any default value you want to assign
                    }

                  
                    $order = QugeoOrder::where('quor_id', $nors['Item']['quor_id']['N'])->first();
                    // Check if the order is found
                    if ($order) {
                        $updateurl = $order->quor_StatusUpdateQry;
                    } else {
                        // Handle case where order is not found
                        $updateurl = null; // Or any default value you want to assign
                    }

                    $updateurl =$this->getQugeoParentStatusUpdated($updateurl, $request['failedreasonid']);
                 
                   
                   
                    $updateurl = str_replace("###6", "1", $updateurl);
                    $updateurl = str_replace("###2", $delreason, $updateurl);
                   
                    $execQry = explode(";", $updateurl);

                   
            
                     //FOR TRACKING - CLEAR
                     $updateurl =QugeoOrder::where('quor_id',$nors['Item']['quor_id']['N'])->value('quor_TrackingUpdateQry');

                    
                    //  $TrackingUpdate = str_replace("###1", $NewDeliveryOrderId, config('constant.QUGEO_TRACKING_API_GATEWAY'));
                    //  $TrackingUpdate = str_replace("###2", config('constant.AWSDYNAMODBTABLEPREFIX'), $TrackingUpdate);
                     $updateurl = str_replace("###1", "", $updateurl);
                     $updateurl = str_replace("###6", "1", $updateurl);

                    

                     $updateurl = QugeoOrder::where('quor_id',$nors['Item']['quor_id']['N'])->value('quor_TrackingHistory');
                     $updateurl = str_replace("##12", config('constant.QUGEO_TO_B2C_ORDER_STATUS_PICKUP_FAILED'), $updateurl);
                     $AssignedLoadedWeight = $totwt;
                     $AssignedLoadedVolume = $totvol;
                    
                }
                else
                {
               
                    $confirmationdetails = json_decode($request['confirmationdetails'], true);
                  
                    if ($nors['Item']['AcceptedAsDirectDelivery']['S'] == 1) {

                        $item['OrderStatus'] = ['Action' => 'PUT', 'Value' => ['N' => (string) config('constant.ORDER_PICKUP_PICKEDUP_TODST_DLS_ID')]];
                       
                        QugeoOrder::where('quor_id', $nors['Item']['quor_id']['N'])
                        ->update([
                            'quor_Status' => config('constant.ORDER_PICKUP_PICKEDUP_TODST_DLS_ID'),
                            'quor_PickedupTime' => date("Y-m-d H:i:s", strtotime($valdatetime))
                        ]);
                        $updateurl =QugeoOrder::where('quor_id',$nors['Item']['quor_id']['N'])->value('quor_StatusUpdateQry');
                        $updateurl = $this->getQugeoParentStatusUpdated($updateurl,config('constant.ORDER_PICKUP_PICKEDUP_TODST_DLS_ID'));
                        $updateurl = str_replace("###6", "1", $updateurl);
                        $updateurl = str_replace("###2", "", $updateurl);
                        $execQry = explode(";", $updateurl);
                        $orderArr = [];
                        array_push($orderArr, $nors['Item']['quor_id']['N']);
                        $directdelivery = new PullPendingOrderRepository();
                       
                        $NewDeliveryOrderId = $directdelivery->scheduleADeliverySchJobs($nors['Item']['quor_id']['N'], $orderdetails, false, '', true, true, $nors['HandlingBranch'], $orderArr, 1, 1, 'Nor');
                        
                     
                       

                        $updateurl = QugeoOrder::where('quor_id',$nors['Item']['quor_id']['N'])->value('quor_TrackingUpdateQry');
                         
                        $DriverName = QugeoDriver::where('d_ID',auth_user()->d_ID)->value('d_Name');
                        $DriverPhone = QugeoDriver::where('d_ID',auth_user()->d_ID)->value('d_Ph1');
                        
                        $TrackingUpdate = str_replace("###1", $NewDeliveryOrderId, config('constant.QUGEO_TRACKING_API_GATEWAY'));
                        $TrackingUpdate = str_replace("###2", config('constant.AWSDYNAMODBTABLEPREFIX'), $TrackingUpdate);
                        $updateurl = str_replace("###1", $TrackingUpdate, $updateurl);
                        $updateurl = str_replace("###6", "1", $updateurl);
                        $updateurl = str_replace("##10", addslashes($DriverName), $updateurl);
                        $updateurl = str_replace("##11", addslashes($DriverPhone), $updateurl);

                        $updateurl = QugeoOrder::where('quor_id',$nors['Item']['quor_id']['N'])->value('quor_TrackingUpdateQry');
                        $updateurl = str_replace("##12", config('constant.QUGEO_TO_B2C_ORDER_STATUS_OUT_FOR_DELIVERY'), $updateurl);
                     
                        $WasAdirectDeliveryPickup = true;
                    ;

                    }
                    else
                    {
                
                        $order = QugeoOrder::where('quor_id', $nors['Item']['quor_id']['N'])->first();

                        if ($order) {
                            $order->quor_Status =config('constant.ORDER_PICKUP_PICKEDUP_TOBR_DLS_ID');
                            $order->quor_PickedupTime = date("Y-m-d H:i:s", strtotime($valdatetime));
                            $order->save();
                        }
                    
                        $updateurl = QugeoOrder::where('quor_id',$nors['Item']['quor_id']['N'])
                        ->value('quor_TrackingUpdateQry');
                        $updateurl = $this->getQugeoParentStatusUpdated($updateurl, config('constant.ORDER_PICKUP_PICKEDUP_TOBR_DLS_ID'));
                        $updateurl = str_replace("###6", "1", $updateurl);
                        $updateurl = str_replace("###2", "", $updateurl);
                        $execQry = explode(";", $updateurl);
                        

                        //FOR TRACKING - CLEAR
                        $updateurl = QugeoOrder::where('quor_id',$nors['Item']['quor_id']['N'])->value('quor_TrackingUpdateQry');
                        // $TrackingUpdate = str_replace("###1", $NewDeliveryOrderId, config('constant.QUGEO_TRACKING_API_GATEWAY'));
                        // $TrackingUpdate = str_replace("###2", config('constant.AWSDYNAMODBTABLEPREFIX'), $TrackingUpdate);
                        $updateurl = str_replace("###1", "", $updateurl);
                        $updateurl = str_replace("###6", "1", $updateurl);
                       
                        $item['OrderStatus'] = ['Action' => 'PUT', 'Value' => ['N' => (string)config('constant.ORDER_PICKUP_PICKEDUP_TOBR_DLS_ID')]];

                    }

                    $quor_TransferOrder_id=QugeoOrder::where('quor_id',$nors['Item']['quor_id']['N'])->value('quor_TransferOrder_id');
                
                    $this->DeliveryPickupVoucher($quor_TransferOrder_id);
                 
                    $CurrentLoadedVolume = $totvol;
                    $CurrentLoadedWeight = $totwt;
                    $CurOper = '+';
                    if ($nors['HasReCalculatedCharges'] == 1) {
                        $item['ReCalculcationPaymentType'] = ['Action' => 'PUT', 'Value' => ['N' => (string)$confirmationdetails['paymenttypeid']]];
                    }


                }
               
            }
            else{
               
                
                if ($request['failed'] == 'true') {
                 
                    $delreason=QugeoDeliveryStatus::where('dls_ID',$request['failedreasonid'])->value('dls_DelStatus');
                    $updateurl = QugeoOrder::where('quor_id',$nors['Item']['quor_id']['N'])->value('quor_StatusUpdateQry');
                    $temDetails =QugeoOrder::where('quor_id',$nors['Item']['quor_id']['N'])->value('quor_ItemDetails');
                    $temDetailsarr = json_decode($temDetails);
                    $barcodearr = array_column($temDetailsarr, 'barcodes');
                    $barcodes = json_encode($barcodearr);
                    $barcodes = "[" . str_replace("]", "", str_replace("[", "", $barcodes)) . "]";
                    QugeoOrder::where('quor_id',$nors['Item']['quor_id']['N'])
                    ->update([
                        'quor_Status'=>$request['failedreasonid'],
                        'quor_ItemReturned'=>$barcodes

                    ]);

                    

                    $item['OrderStatus'] =  ['Action' => 'PUT', 'Value' => ['N' => (string) $request['failedreasonid']]];
                    $item['return_items'] = ['Action' => 'PUT', 'Value' => ['N' => (string) '0']];
                    
                    $updateurl = $this->getQugeoParentStatusUpdated($updateurl, $request['failedreasonid']);
                    $updateurl = str_replace("###6", "1", $updateurl);
                    $updateurl = str_replace("###2", $delreason, $updateurl);
                    $execQry = explode(";", $updateurl);

                    $updateurl = QugeoOrder::where('quor_id',$nors['quor_id'])->value('quor_TrackingHistory');
                    $updateurl = str_replace("##12", config('constant.QUGEO_TO_B2C_ORDER_STATUS_DELIVERY_FAILED'), $updateurl);
              

                   
                }
                else
                {
                   
                    $request['return_items'] = str_replace('"', '', $request['return_items']);
                    $request['return_items'] = str_replace('\'', '', $request['return_items']);
                    if($request['confirmDelivery'] == 1)
                    {
                     
                        $item['OrderStatus'] = ['Action' => 'PUT', 'Value' => ['N' => (string) config('constant.ORDER_DELIVERY_MARKED_DLS_ID')]];
                        $item['return_items'] = ['Action' => 'PUT', 'Value' => ['N' =>(string) "0"]];

                        QugeoOrder::where('quor_id',$nors['Item']['quor_id']['N'])
                        ->update([
                            'quor_Type'=>1,
                            'quor_Status'=>config('constant.ORDER_DELIVERY_MARKED_DLS_ID'),
                            'quor_DeliveredTime'=>date("Y-m-d H:i:s", strtotime($valdatetime)),
                            'quor_ItemReturned' =>$request['return_items']

                        ]);

                        $updatedetails = QugeoOrder::select('quor_StatusUpdateQry', 'quor_AmountCollectible', 'quor_Deliverybr_id', 'quor_Paymode', 'quor_TransferOrder_Type')
                            ->where('quor_id', $nors['Item']['quor_id']['N'])
                            ->first();

                        $updateurl = $updatedetails['quor_StatusUpdateQry'];
                        $updateurl = $this->getQugeoParentStatusUpdated($updateurl, config('constant.ORDER_DELIVERY_MARKED_DLS_ID'));
                        
                        if ($updatedetails['quor_Paymode'] == 'Paid Online' || $updatedetails['quor_Paymode'] == 'Paid with Wallet') {

                        QugeoOrder::where('quor_id',$nors['Item']['quor_id']['N'])
                        ->Update([
                            'quor_Status'=>config('constant.ORDER_DELIVERY_COMPLETED_DLS_ID'),
                            'quor_DeliveredTime'=>date("Y-m-d H:i:s", strtotime($valdatetime)),
                            'quor_ItemReturned'=> $request['return_items']
                        ]);

                        $updateurl = str_replace("status_id = '17'", "status_id = '18'", $updateurl);

                        $updateurl = str_replace("###2", "", $updateurl);
                        $updateurl = str_replace("###6", (intval($request['ondel_payment_mode']) == 1 ? 7 : 6), $updateurl);
                        $updateurl = str_replace("###7", (intval($request['ondel_payment_mode']) == 1 ? "" : $request['ondel_refer_id']), $updateurl);
                        $execQry = explode(";", $updateurl);
                        $updateurl =QugeoOrder::where('quor_id',$nors['Item']['quor_id']['N'])->value('quor_TrackingHistory');

                        if ($updatedetails['quor_Paymode'] == 'Paid Online' || $updatedetails['quor_Paymode'] == 'Paid with Wallet') {
                            $updateurl = str_replace("##12", config('constant.QUGEO_TO_B2C_ORDER_STATUS_DELIVERY_CONFIRMED'), $updateurl);
                        } else {
                            $updateurl = str_replace("##12", config('contant.QUGEO_TO_B2C_ORDER_STATUS_DELIVERED_NOT_CONFIRMED'), $updateurl);
                        }

                        $CurrentLoadedVolume = $totvol;
                        $CurrentLoadedWeight = $totwt;
                        $CurOper = '-';
                        $DriverName =QugeoDriver::where('d_ID',auth_user()->d_ID)->value('d_Name');
                        $qry = "Our delivery partner " . $DriverName . " has delivered your Order No." . $JobNo . " successfully. Thank you for selecting." . config('constant.PROJECT_NAME');
                        $templatedata['order_order_id'] = $JobNo;
                       // app(SmsSender::class)->FfetchContentSendSms(($templatedata, $deliverymobile, 11);
                        $quor_TransferOrder_id =QugeoOrder::where('quor_id',$nors['Item']['quor_id']['N'])->value('quor_TransferOrder_id');
                        if ($updatedetails['quor_AmountCollectible'] == 0) {
                            if($updatedetails['quor_TransferOrder_Type'] == 1){
                                $custOrderId=FinascopStockTransferOrder::where('fsto_id',$quor_TransferOrder_id)->value('fstr_id');
                                RetalineCustomerOrder::where('order_order_id', $nors['Item']['quor_RefNo']['S'])->update([
                                    "status_id" => 18
                                ]);
                                $quor_id = $nors['Item']['quor_id']['N'];
                                // Using the query builder to execute the stored procedure
                                DB::select('CALL UpdateDeliveryStatus(?, ?, NULL)', [$quor_id, $custOrderId]);
                            }
                        }
                        
                        $this->DeliveryConfirmationVoucher($quor_TransferOrder_id);
                        $this->DeliveryEmail($quor_TransferOrder_id);
                        }
                        
                    }
                }
                //FOR TRACKING - CLEAR
                $updateurl =QugeoOrder::where('quor_id',$nors['Item']['quor_id']['N'])->value('quor_TrackingUpdateQry');
                $updateurl = str_replace("###1", "", $updateurl);
                $updateurl = str_replace("###6", "1", $updateurl);

            }

        

            try{

                $this->dynamoClient->updateItem([
                    'TableName' =>config('aws.prefix') . 'QugeoOrderDetails',
                    'Key' => [
                        'orderid' => ['S' => $request['orderid']],
                    ],
                    'AttributeUpdates' =>$item
                ]);
            }
            catch(Exception $e)
            {
                Log::info('concludeOrder ERROR', [$e]);
            }
           

            
           


            $item=[];
            $item['IsClosed'] = ['Action' => 'PUT', 'Value' => ['N' => (string)1]];
            $item['IsLiveOrder'] = ['Action' => 'PUT', 'Value' => ['N' => (string)0]];

            $NewOrder = $this->dynamoClient->updateItem([
                'TableName' =>config('aws.prefix') . 'QugeoLiveVehicleOrders',
                'Key' => [
                    'apikey' => ['S' => (string) auth_user()->d_apikey],
                    'orderid' => ['S' => $request['orderid']]
                ],
                'AttributeUpdates' =>$item
            ]);

          
        
            if ($AssOper != '' || $CurOper != '') {
                $arrLiveVehicle = [];
                if ($AssOper != '') {
                    $item['AssignedLoadedWeight'] = ['Action' => 'PUT', 'Value' => ['N' => (string) $AssignedLoadedWeight]];
                    $item['AssignedLoadedVolume'] = ['Action' => 'PUT', 'Value' => ['N' => (string) $AssignedLoadedVolume]];
                }
                if ($CurOper != '') {
                    $item['CurrentLoadedVolume'] = ['Action' => 'PUT', 'Value' => ['N' => (string) $CurrentLoadedVolume]];
                    $item['CurrentLoadedWeight'] = ['Action' => 'PUT', 'Value' => ['N' => (string) $CurrentLoadedWeight]];
                }
                $this->dynamoClient->updateItem([
                    'TableName' =>config('aws.prefix') . 'QugeoLiveVehicles',
                    'Key' => [
                        'apikey' => ['S' => (string) auth_user()->d_apikey],
                    ],
                    'AttributeUpdates' =>$item
                ]);
               
                
            }
            
             if ($WasAdirectDeliveryPickup) {
             
                    $udpatedorder = $polled->UpdateOrderOnPoll(1, $NewDeliveryOrderId, $apikey, true, $orderdetails);
                 
                    $polled->AssignOrderToQugeoDriverSch($NewDeliveryOrderId, $apikey, $orderdetails, $nextorder, $dummy2, true);
                 
                  
                    $hasorder = true;

                } else {
                 
                    $nextorder = [];
                    $hasorder = $this->GetNextOrder($apikey, $nextorder,false,$request['orderid']);
                }
                if ($hasorder) {
                   
                    $arrSession['msg'] = 'Has new order';
                } else {
					if($request['confirmDelivery'] == 0){
						$arrSession['msg'] = 'Has new order';
					}else{
                    $arrSession['msg'] = 'No new order';

             
                    $polled->UpdateReleasingLocation($apikey, 0, 0);
					}
                }
                $arrSession['success'] = true;
        }
        else
        {
                $arrSession['success'] = false;
                $arrSession['msg'] = 'Invalid Order Id';
            
        }

        // dd($nextorder);
      
        $arrSession['Data']['vehicle'] = $nextorder;
        
    } catch (Exception $e) {
        $arrSession['success'] = false;
        $arrSession['msg'] = $e->getMessage();
    }
    
        return $arrSession;
    }
    public function  deliveryPickupVoucher($order_id)
    {
        $orderDetails = RetalineCustomerOrder::join('finascop_stock_transfer_order', 'retaline_customer_order.order_id', '=', 'finascop_stock_transfer_order.fstr_id')
        ->where('finascop_stock_transfer_order.fsto_id', $order_id)
        ->first();
        if($orderDetails)
        {
            try
            {
                $postReq = new Request();
                $postReq->setMethod('POST');
                $postReq->request->add([
                    'order_id'              => $orderDetails->order_id,
                    'finascopEventRefId'    => config("event_master.pickupForDelivery"),
                    'storegroup_id'         => (@$orderDetails->storegroup_id ? $orderDetails->storegroup_id : 0)
                ]);
                (new PostingRepository)->finascopPosting($postReq);

                event(new DelayedOrderEvent($orderDetails->order_id, 6));
            }
            catch (\Exception $e) { }
        }
        /* $delVoucherItems = RetalineCustomerOrder::select('fsto_id', 'order_delivery_charge', 'storegroup_id', 'order_courier_charge', 'order_delivery_charge_gst',
        'order_order_id', 'order_total_amount', 'order_roundoff', 'order_id', 'order_branch_id', 'order_tcs_utgst',
        'order_tds', 'order_tcs', 'order_tcs_cgst', 'order_tcs_sgst', 'order_tcs_igst', 'order_total_utgst', 'order_delivery_charge_utgst',
        'order_total_sgst', 'order_total_cgst', 'order_delivery_charge_igst', 'order_total_igst', 'order_delivery_charge_cgst', 'order_delivery_charge_sgst')
        ->join('finascop_stock_transfer_order', 'retaline_customer_order.order_id', '=', 'finascop_stock_transfer_order.fstr_id')
        ->where('finascop_stock_transfer_order.fsto_id', $orderId)
        ->first();

        if (!$delVoucherItems) {
            return false; 
        }
    
        $pickupsData = FinanceAutoPostingValue::where('order_id', $delVoucherItems->order_id)
        ->first();
            
        if (!$pickupsData) {
            return false; 
        }
        
        $pickupsUpdateData = [
            'RoundUp_ForSettlement' => $pickupsData->RoundUp,
            'RoundDown_ForSettlement' => $pickupsData->RoundDown,
        ];

        $filteredPickupsUpdateData = array_filter($pickupsUpdateData);
        if (!empty($filteredPickupsUpdateData)) {
            FinanceAutoPostingValue::where('order_id', $delVoucherItems->order_id)
                ->update($filteredPickupsUpdateData);
        }
        $orderData = [
            'order_id' => $delVoucherItems->order_id,
            'finascopEventRefId' => FinanceEventMaster::where('name', 'Order Pickup')
                ->value('event_ref_id'),
            'storeGroupId' => $delVoucherItems->order_branch_id > 0 ?
                FinascopBranch::where('br_ID', $delVoucherItems->order_branch_id)
                    ->value('br_storeGroup') :
                -1,
        ];
        $finascopPostingServiceUrl = SysConfiguration::where('cfg_Name', 'FINANCEAUTOPOSTING')
        ->value('cfg_Value');

        // Make HTTP request using Laravel's HTTP client
        // $response = Http::post($finascopPostingServiceUrl, $orderData);

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $finascopPostingServiceUrl,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $orderData,
        ));
    
        $response = curl_exec($curl);
    
        curl_close($curl);

        return true; */
 
    }
    public function getNextOrder($apikey, &$nextorder, $readonly = false, $currentOrderId = '')
    {

   
      
        $nors = $this->dynamoClient->getItem([
            'TableName' => config('aws.prefix') . 'QugeoLiveVehicleOrders',
            'Key' => [
                'apikey' => ['S' => $apikey],
                'orderid' =>['S' => $currentOrderId],
            ],
            'AttributesToGet' => [
                'orderid', 'order','IsLiveOrder'
            ]
        ]);


     
        if (isset($nors) && count($nors) > 0)
        {

            $currentOrders = [];
            $hasLiveOrder = false;
            $order = [];   

          
            if (isset($nors['Item']['orderid']['S']) && isset($nors['Item']['order']['S'])) {
                $currentOrders[] = [
                    'orderid' => $nors['Item']['orderid']['S'],
                    'order' => $nors['Item']['order']['S']
                ];
            }

       

            if ($nors['Item']['IsLiveOrder']['N'] == '1'||$nors['Item']['IsLiveOrder']['N'] == 1) {
               
                $hasLiveOrder = true;
                $orderId =$nors['Item']['orderid']['S'];
            }

         
        
            if (!$hasLiveOrder) {
            
                $currentOrdersCollection = collect($currentOrders);
                $sortedOrders = $currentOrdersCollection->sortBy('order');
                $firstOrder = $sortedOrders->first();
                
                if ($firstOrder !== null) {
                    $orderId = $firstOrder['orderid'];
                } else {
                    // Handle the case where $currentOrders is empty or sorting didn't produce any results
                    $orderId = null; // or any default value you want to assign
                }
            }

            if (!empty($currentOrderId)) {
                $orderId = $currentOrderId;
            }

            $rsno = $this->dynamoClient->getItem([
                'TableName' =>config('aws.prefix') . 'QugeoOrderDetails',
                'Key' => [
                    'orderid' => ['S' => $currentOrderId],
                ],
                'AttributesToGet' => [
                    'pickupLat', 'pickupLng', 'deliveryLat', 'deliveryLng', 'BrLat', 'BrLong', 'pickupmobile', 'deliverymobile', 'AcceptedAsDirectDelivery', 'pickupname', 'deliveryname', 'pickupaddress', 'deliveryaddress', 'pickuplocation', 'deliverylocation', 'quor_RefNo', 'pktcount', 'chrgwt', 'netamt', 'DelAssetBucket', 'ImgSignLocation', 'ImgCustLocation', 'IsPickup', 'totwt', 'Consignment', 'pickupOTP', 'deliveryOTP'
                ]
            ]);

          

            if ($rsno['Item']['IsPickup']['N'] == '1'||$rsno['Item']['IsPickup']['N'] == 1) {
                $lat = (@$rsno['Item']['pickupLat']['N'] ? $rsno['Item']['pickupLat']['N'] : @$rsno['Item']['pickupLat']['S']);
                $long = (@$rsno['Item']['pickupLng']['N'] ? $rsno['Item']['pickupLng']['N'] : @$rsno['Item']['pickupLng']['S']);
            } else {

                $lat = (@$rsno['Item']['deliveryLat']['N'] ? $rsno['Item']['deliveryLat']['N'] : @$rsno['Item']['deliveryLat']['S']);
                $long = (@$rsno['Item']['deliveryLng']['N'] ? $rsno['Item']['deliveryLng']['N'] : @$rsno['Item']['deliveryLng']['S']);
            }

            if ($readonly == false) {
                $item = [];
                $item['IsLiveOrder'] = ['Action' => 'PUT', 'Value' => ['N' => '1']];
                $NewOrder = $this->dynamoClient->updateItem([
                    'TableName' =>config('aws.prefix') . 'QugeoLiveVehicleOrders',
                    'Key' => [
                        'apikey' => ['S' => (string) auth_user()->d_apikey],
                        'orderid' => ['S' => $currentOrderId]
                    ],
                    'AttributeUpdates' =>$item
                ]);
            }
            $quor_TransferOrder_Type = QugeoOrder::where('quor_RefNo',$rsno['Item']['quor_RefNo']['S'])->value('quor_TransferOrder_Type');
            switch ($quor_TransferOrder_Type) {
                case 1:
                    $orderPk =RetalineCustomerOrder::where('order_order_id',$rsno['Item']['quor_RefNo']['S'])->value('order_id');
                    break;
                default:
                    $orderPk = 0;
                    break;
            }

            $nextorder['istriprerouted'] = true;
            $nextorder['mapdetails'] = [
                'latitude' =>(float) $lat,
                'longitude' =>(float) $long,
                'zoomlevel' => '10',
                'locationicon' => "https://maps.google.com/mapfiles/ms/icons/yellow-dot.png",
            ];

            
            // $rsnoItemConsignment = $rsno['Item']['Consignment']['S'];
            // if (strpos($rsnoItemConsignment, '[') !== false && strpos($rsnoItemConsignment, ']') !== false) {
            //     $decodedJson = json_decode($rsnoItemConsignment, true);
            // } else {
            //     $decodedJson = json_decode("[$rsnoItemConsignment]", true);
            // }
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
                'ispickup' => ($rsno['Item']['IsPickup']['N'] == 1||$rsno['Item']['IsPickup']['N'] == "1" ? true : false),
                'order_id' => $orderPk,
                'orderType' => $quor_TransferOrder_Type,
                'orderno' => $rsno['Item']['quor_RefNo']['S'],
                'customer' => ($rsno['Item']['IsPickup']['N'] == 1 ? $rsno['Item']['pickupname']['S']: $rsno['Item']['deliveryname']['S']),
                'address' => ($rsno['Item']['IsPickup']['N'] == 1 ? $rsno['Item']['pickupaddress']['S'] : $rsno['Item']['deliveryaddress']['S']),
                'location' => ($rsno['Item']['IsPickup']['N'] == 1 ? $rsno['Item']['pickuplocation'] ['S']: $rsno['Item']['deliverylocation']['S']),
                'contph' => ($rsno['Item']['IsPickup']['N'] == 1 ? $rsno['Item']['pickupmobile']['S'] : $rsno['Item']['deliverymobile']['S']),
                'noofboxes' => $pktcount,
                'weight' => $totwt . 'kg',
                'cashtobecollected' => $rsno['Item']['netamt']['S'],
                'orderid' => $currentOrderId,
                'orderdetails' => $decodedJson,
                'otp' => ($rsno['Item']['IsPickup'] == 1 ? $rsno['Item']['pickupOTP']['S'] : $rsno['Item']['deliveryOTP']['S']),
            ];

       

            if (($rsno['Item']['IsPickup']['N'] == 0) || ($rsno['Item']['IsPickup']['N'] == '0')) {
                $nextorder['nextorderdetails']['deluploadcreds'] = [
                    'S3BUCKET' => config('constant.QUGEO_S3_UPLOAD_BUCKET'),
                    'ImgSign' => $rsno['Item']['ImgSignLocation']['S'],
                    'ImgCust' => $rsno['Item']['ImgCustLocation']['S'],
                    'ACCESSKEY' => config('constant.QUGEO_S3_UPLOAD_ACCESS'),
                    'SECRETKEY' => config('constant.QUGEO_S3_UPLOAD_SECRET'),
                    'REGION' => config('constant.QUGEO_S3_UPLOAD_REGION'),
                    'ENDPOINT' => config('constant.QUGEO_S3_UPLOAD_ENDPOINT'),
                ];
            }
       

            return true;
        }
        else
        {
          
            $nextorder['istriprerouted'] = false;
            $item = [];
            $item['IsEngaged'] = ['Action' => 'PUT', 'Value' => ['N' => '0']];
            $nors = $this->dynamoClient->updateItem([
                'TableName' =>config('aws.prefix') . 'QugeoLiveVehicles',
                'Key' => [
                    'apikey' => ['S' => (string) auth_user()->d_apikey],
                ],
                'AttributeUpdates' =>$item
            ]);
            return false;
        }

    }
    public function deliveryConfirmationVoucher($order_id)
    {
        $orderDetails = RetalineCustomerOrder::join('finascop_stock_transfer_order', 'retaline_customer_order.order_id', '=', 'finascop_stock_transfer_order.fstr_id')
        ->where('finascop_stock_transfer_order.fsto_id', $order_id)
        ->first();
        if($orderDetails)
        {
            try
            {
                $postReq = new Request();
                $postReq->setMethod('POST');
                $postReq->request->add([
                    'order_id'              => $orderDetails->order_id,
                    'finascopEventRefId'    => config("event_master.deliveryConfirmation"),
                    'storegroup_id'         => (@$orderDetails->storegroup_id ? $orderDetails->storegroup_id : 0)
                ]);
                (new PostingRepository)->finascopPosting($postReq);
                event(new DelayedOrderEvent(@$orderDetails->order_id, 7));
            }
            catch (\Exception $e) { }
        }
        /* $delVoucherItems = RetalineCustomerOrder::select(
            'fsto_id', 'order_delivery_charge', 'storegroup_id', 'order_courier_charge', 'order_delivery_charge_gst',
            'order_order_id AS orders', 'order_total_amount AS amount_before_tax', 'order_roundoff', 'order_id', 'order_branch_id', 'order_tcs_utgst',
            'order_tds', 'order_tcs', 'order_tcs_cgst', 'order_tcs_sgst', 'order_tcs_igst', 'order_total_utgst', 'order_delivery_charge_utgst', 'order_method', 'payment_mode',
            'order_total_sgst AS sgst', 'order_total_cgst AS cgst', 'order_delivery_charge_igst', 'order_total_igst', 'order_delivery_charge_cgst', 'order_delivery_charge_sgst',
            'quor_AmountCollectible', 'quor_Paymode', 'quor_DeliveryDriverId'
             )
            ->join('finascop_stock_transfer_order', 'retaline_customer_order.order_id', '=', 'finascop_stock_transfer_order.fstr_id')
            ->join('qugeo_order', 'qugeo_order.quor_TransferOrder_id', '=', 'finascop_stock_transfer_order.fsto_id')
            ->where('finascop_stock_transfer_order.fsto_ordertype', 1)
            ->where('qugeo_order.quor_TransferOrder_Type', 1)
            ->where('finascop_stock_transfer_order.fsto_id', $order_id)
            ->first();
        
        $operating_company=config('app.operating_country')?config('app.operating_country'):'INDIA';
        $confUpdateData=[];
        switch ($operating_company) {
            case 'INDIA':
                $confirmData = FinanceAutoPostingValue::where('order_id', $delVoucherItems['order_id'])->first();
    
                if ($delVoucherItems['quor_AmountCollectible'] > 0) {
                    switch ($delVoucherItems['order_method']) {
                        case 1:
                            if ($delVoucherItems['quor_DeliveryDriverId'] > 0) {
                                $driverDetails = QugeoDriver::where('d_ID', $delVoucherItems['quor_DeliveryDriverId'])->first();
                                if ($driverDetails && $driverDetails->createdBy == 1) {
                                    $confUpdateData['TSOPOD_CashPayment'] = $delVoucherItems['quor_AmountCollectible'];
                                    $confUpdateData['TenantCollection_COD'] = $delVoucherItems['quor_AmountCollectible'];
                                    $confUpdateData['CourierCollection_COD'] = 0;
                                    $confUpdateData['GrozeoLogisticsPartnerCollection_COD'] = 0;
                                } else {
                                    $confUpdateData['TSOPOD_CashPayment'] = $delVoucherItems['quor_AmountCollectible'];
                                    $confUpdateData['TenantCollection_COD'] = 0;
                                    $confUpdateData['CourierCollection_COD'] = 0;
                                    $confUpdateData['GrozeoLogisticsPartnerCollection_COD'] = $delVoucherItems['quor_AmountCollectible'];
                                }
                            }
                            break;
                        case 3:
                            $confUpdateData['TSOPOD_CashPayment'] = $delVoucherItems['quor_AmountCollectible'];
                            $confUpdateData['TenantCollection_COD'] = 0;
                            $confUpdateData['CourierCollection_COD'] = $delVoucherItems['quor_AmountCollectible'];
                            $confUpdateData['GrozeoLogisticsPartnerCollection_COD'] = 0;
                            break;
                    }
                }
                break;
            case 'UK':
                $confirmData = FinanceAutoPostingValue::where('order_id', $delVoucherItems['order_id'])->first();
    
                if ($delVoucherItems['quor_AmountCollectible'] > 0) {
                    switch ($delVoucherItems['order_method']) {
                        case 1:
                            if ($delVoucherItems['quor_DeliveryDriverId'] > 0) {
                                $driverDetails = QugeoDriver::where('d_ID', $delVoucherItems['quor_DeliveryDriverId'])->first();
                                if ($driverDetails && $driverDetails->createdBy == 1) {
                                    $confUpdateData['DeliveryAgent_PODCashinHand'] = $delVoucherItems['quor_AmountCollectible'];
                                    $confUpdateData['TSOPOD_CashPayment'] = $delVoucherItems['quor_AmountCollectible'];
                                    $confUpdateData['TenantCollection_COD'] = $delVoucherItems['quor_AmountCollectible'];
                                    $confUpdateData['CourierCollection_COD'] = 0;
                                    $confUpdateData['GrozeoLogisticsPartnerCollection_COD'] = 0;
                                    $confUpdateData['POD_CashSettledbyDA'] = $delVoucherItems['quor_AmountCollectible'];
                                } else {
                                    $confUpdateData['DeliveryAgent_PODCashinHand'] = $delVoucherItems['quor_AmountCollectible'];
                                    $confUpdateData['TSOPOD_CashPayment'] = $delVoucherItems['quor_AmountCollectible'];
                                    $confUpdateData['TenantCollection_COD'] = 0;
                                    $confUpdateData['CourierCollection_COD'] = 0;
                                    $confUpdateData['GrozeoLogisticsPartnerCollection_COD'] = $delVoucherItems['quor_AmountCollectible'];
                                    $confUpdateData['POD_CashSettledbyDA'] = $delVoucherItems['quor_AmountCollectible'];
                                }
                            }
                            break;
                        case 3:
                            $confUpdateData['TSOPOD_CashPayment'] = $delVoucherItems['quor_AmountCollectible'];
                            $confUpdateData['TenantCollection_COD'] = 0;
                            $confUpdateData['CourierCollection_COD'] = $delVoucherItems['quor_AmountCollectible'];
                            $confUpdateData['GrozeoLogisticsPartnerCollection_COD'] = 0;
                            $confUpdateData['POD_CashSettledbyLSP'] = $delVoucherItems['quor_AmountCollectible'];
    
                            break;
                    }
                }
                break;
        }
        $confUpdateData = array_filter($confUpdateData);
        if (!empty($confUpdateData)) {
            FinanceAutoPostingValue::where('order_id', $delVoucherItems['order_id'])->update($confUpdateData);
        }
    
        $orderData['order_id'] = $delVoucherItems['order_id'];
        $orderData['finascopEventRefId'] = FinanceEventMaster::where('name', 'Delivery Confirmation')->value('event_ref_id');
    
        if ($delVoucherItems['order_branch_id'] > 0) {
            $orderBranchId = $delVoucherItems['order_branch_id'];
            $orderBranchsg = FinascopBranch::where('br_ID', $orderBranchId)->value('br_storeGroup');
    
            $orderData['storeGroupId'] = $orderBranchsg;
        } else {
            $orderData['storeGroupId'] = -1;
        }
    
        $finascopPostingServiceUrl = SysConfiguration::where('cfg_Name', 'FINANCEAUTOPOSTING')->value('cfg_Value');
    
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $finascopPostingServiceUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $orderData,
        ));

        $response = curl_exec($curl);
        curl_close($curl);
    
        return true; */
    }
    public function deliveryEmail($order_id)
    {
        $delVoucherItems = RetalineCustomerOrder::select('order_order_id', 'order_customer_id', 'order_branch_id')
            ->join('finascop_stock_transfer_order', 'retaline_customer_order.order_id', '=', 'finascop_stock_transfer_order.fstr_id')
            ->where('finascop_stock_transfer_order.fsto_ordertype', 1)
            ->where('finascop_stock_transfer_order.fsto_id', $order_id)
            ->first();

        $customerDetails = RetalineCustomer::select('cust_mobile', 'cust_email', 'cust_customer_name')
            ->where('cust_id', $delVoucherItems['order_customer_id'])
            ->first();

        $orderData['fullname'] = $customerDetails->cust_customer_name;
        $orderData['email'] = $customerDetails->cust_email;
        $orderData['storename'] = FinascopBranch::where('br_ID', $delVoucherItems['order_branch_id'])->value('br_Name');
        $orderData['ordernum'] = $delVoucherItems->order_order_id;

        $url = SysConfiguration::where('cfg_Name', 'DELIVERYEMAIL')->value('cfg_Value');
        $fields_string = json_encode($orderData);

        $opts = array(
            CURLOPT_URL => $url,
            CURLINFO_CONTENT_TYPE => "application/json",
            CURLOPT_BINARYTRANSFER => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => count($orderData),
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
          );
      
          $ch = curl_init();
          curl_setopt_array($ch, $opts);
          $data = curl_exec($ch);
          $info = curl_getinfo($ch);
          curl_close($ch);

        return true;
    }

}
