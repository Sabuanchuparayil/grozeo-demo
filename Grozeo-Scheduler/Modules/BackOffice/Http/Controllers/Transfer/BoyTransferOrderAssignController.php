<?php

namespace BackOffice\Http\Controllers\Transfer;

use App\Models\Order;
use BackOffice\Models\QugeoOrder;
use BackOffice\Models\B2bOrder;
use BackOffice\Models\CpdOrder;
use BackOffice\Models\GodownBoy;
use BackOffice\Status\OrderType;
use App\Jobs\SendNotificationJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use BackOffice\Models\TransferOrder;
use App\Http\Responses\ErrorResponse;
use BackOffice\Status\B2bOrderStatus;
use BackOffice\Status\CpdOrderStatus;
use BackOffice\Models\BoyOrderRequest;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use BackOffice\Status\CustomerOrderStatus;
use BackOffice\Status\TransferOrderStatus;
use BackOffice\Traits\CanSendNotificationsToBoy;
use BackOffice\Http\Requests\BoyOrderAssignRequest;
use Carbon\carbon;
use BackOffice\Models\LaravelJobs;
use App\Models\Client;
use App\Models\OrderAddress;
use BackOffice\Models\Branch;
use BackOffice\Models\TransferRequest;
use App\Models\OrderItem;
use BackOffice\Models\ReturnPacking;
use BackOffice\Models\SalesOrder;
use App\Events\OrderHistory;

class BoyTransferOrderAssignController
{
    use CanSendNotificationsToBoy;
    
    protected $order;

    protected $boyOrderRequest;
    
    protected const REQUEST_SENT = 1;

    protected const TRANSFER_REQUEST = 0;

    protected const CUSTOMER_ORDER = 1;

    protected const B2B_ORDER = 2;

    protected const STOCK_RETURN = 3;

    public function __construct(TransferOrder $order, BoyOrderRequest $boyOrderRequest)
    {
        $this->order = $order;
        $this->boyOrderRequest = $boyOrderRequest;
    }

    public function __invoke(BoyOrderAssignRequest $request)
    {
        
        try
        { 
            $boy = null;
            if(isset($request->type) && $request->type=="picker-request"){
    
                $boy = GodownBoy::find(auth_user()->id, ['id', 'fcm_id']);         
            }else{

                $boy = GodownBoy::find($request->boy_id, ['id', 'fcm_id']);
            }

            if(!isset($boy->id) || intval($boy->id)==0){         
                return new ErrorResponse('Invalid Order picker');
            }
            
            $orderRequest = null;
            $transorder = TransferOrder::where('fsto_id', $request->order_pk_id)
                ->select('fsto_status','fsto_uid', 'fsto_id', 'fsto_source','fstr_id','fsto_assigned_boy')
                ->first(); 
            /* if(($transorder->fsto_status == TransferOrderStatus::GODOWN_BOY_POLLED) || ($transorder->fsto_status == TransferOrderStatus::ASSIGNED_GODOWN_BOY))
            {
                return new ErrorResponse('The order has already been assigned.');
            } */
            if(@$transorder->fsto_status == TransferOrderStatus::PACKED_NOT_BOXED)
            {
                $packingList = $this->getPackDataForNotBoxed($transorder);
                return new SuccessWithData($packingList);
            }
            if($transorder->fsto_ordertype !=static::TRANSFER_REQUEST && $transorder->fsto_ordertype !=static::CUSTOMER_ORDER && $transorder->fsto_ordertype !=static::B2B_ORDER){  
                return new ErrorResponse('This Packing order is not supported by Packsure application');
            }    

            if($transorder->fsto_status !=TransferOrderStatus::TO_MANUALLY_ASSIGN && $transorder->fsto_status !=TransferOrderStatus::CANCELLED_AFTER_PACKING && $transorder->fsto_status !=TransferOrderStatus::INCOMPLETE_ORDER && $transorder->fsto_status !=TransferOrderStatus::HOLD_FOR_CUSTOMER_APPROVAL && $transorder->fsto_status !=TransferOrderStatus::ASSIGNED_GODOWN_BOY && $transorder->fsto_status !=TransferOrderStatus::PACKED_NOT_BOXED && $transorder->fsto_status != TransferOrderStatus::PICKER_APPROVED){
                return new ErrorResponse('The order cannot be assigned as the status has been changed');
            }
            DB::transaction(function () use ($request, &$orderRequest, $boy, $transorder) {
                if($transorder->fsto_status == TransferOrderStatus::CANCELLED_AFTER_PACKING){
                    TransferOrder::where('fsto_id', $request->order_pk_id)
                    ->update(['fsto_iscancelunpacked' => 1, 'fsto_cancelunpackeddby' => $boy->id, 'fsto_cancelunpackedon' => now()]);
                }else{
                    $orderRequest = $this->boyOrderRequest->firstOrCreate(
                        [
                            'order_id'      => $transorder->fsto_uid,
                            'order_pk_id'   => $request->order_pk_id,
                            'branch_id'     => $request->branch_id,
                            'boy_id'        => $boy->id,
                        ],
                        [
                            'boy_id'        => $boy->id,
                            'branch_id'     => $request->branch_id,
                            'order_id'      => $transorder->fsto_uid,
                            'status'        => static::REQUEST_SENT,
                            //'is_cpd'      => $request->is_cpd,
                            //'is_b2border' =>$request->is_b2border,
                            'order_pk_id'   =>$request->order_pk_id
                        ]
                    );
                    if(@$orderRequest->boy_id != $boy->id)
                    {
                        return new ErrorResponse('Another order picker already assigned.');
                    }
                    $this->updateOrder($request,$boy->id,$orderRequest->id,$transorder);

                }
    
            });
            $this->boyOrderRequest->where([
                ['order_id', $transorder->fsto_uid],
                ['order_pk_id', $request->order_pk_id],
                ['branch_id', $request->branch_id],
				['boy_id',$boy->id],
            ])->update([
                'status'     => static::REQUEST_SENT
            ]);
            if(@$request->from == 'app')
            {
                $returns = $this->pendingOrderResponse(
                    $transorder->fsto_uid,      
                    $orderRequest->id,
                    -1,
                    false,
                    $request->order_pk_id
                );
                return new SuccessWithData($returns);
            }
            else
            {
                $start = Carbon::now();

                if(isset($request->type) && $request->type=="picker-request"){
                if($transorder->fsto_status == TransferOrderStatus::CANCELLED_AFTER_PACKING){
                    $strmsg = 'Packed items are unpacked and restored to the rack';
                }else{
                    $strmsg = 'Packing Order Pushed to Order Picker';
                    $notificationData=array(
                        "orderId"=>$transorder->fsto_uid,
                        "fcmId"=>$boy->fcm_id, 
                        "orderReqId"=>$orderRequest->id,
                        "boyOrderId"=>-1,
                        "isRevoked"=>false,
                        "order_pk_id"=>$request->order_pk_id );
                
                    $this->sendNotificationToBoy(
                        $transorder->fsto_uid, 
                        $boy->fcm_id,         
                        $orderRequest->id,
                        -1,
                        false,
                        $request->order_pk_id
                    );
                }
                }else{
                    if($transorder->fsto_status == TransferOrderStatus::INCOMPLETE_ORDER || $transorder->fsto_status == TransferOrderStatus::HOLD_FOR_CUSTOMER_APPROVAL || $transorder->fsto_status == TransferOrderStatus::CANCELLED){
                        $strmsg = 'Picked Order Pushed to Order Picker for Replenish';
                    $this->sendNotificationToBoy(
                        $transorder->fsto_uid, 
                        $boy->fcm_id,         
                        $orderRequest->id,
                        -1,
                        false,
                        $request->order_pk_id,0,true
                    );
                    }else{
                    $strmsg = 'Packing Order Pushed to Order Picker';
                    $this->sendNotificationToBoy(
                        $transorder->fsto_uid, 
                        $boy->fcm_id,         
                        $orderRequest->id,
                        -1,
                        false,
                        $request->order_pk_id
                    );    
                }
                
                }

                return new SuccessResponse($strmsg);
            }
        }
        catch (\Exception $e)
        {
            // info("BoyTransferOrderAssignController pendingOrderAddinfo ERROR => ".$e->getMessage());
        } 
    }

    /**
     * Update the order status.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function updateOrder($request,$order_polled_boy,$requestid,$transorder)
    {
        if($transorder->fsto_status == TransferOrderStatus::PICKER_APPROVED)
        {
            return false;
        }
        $model = new TransferOrder;
        $orderField = 'fsto_id';
        $statusField = 'fsto_status';
        $status = TransferOrderStatus::GODOWN_BOY_POLLED;
        $polledboyField = 'fsto_polled_boy';
        $polledboyrequestField = 'fsto_polled_request_id';
        $model
            ->where($orderField, $request->order_pk_id)
            ->update([$statusField => $status, $polledboyField => $order_polled_boy,$polledboyrequestField => $requestid  ]);
            event(new OrderHistory($transorder->fstr_id, CustomerOrderStatus::GODOWN_BOY_POLLED));
        TransferOrder::reverseStatusUpdate($request->order_pk_id,TransferOrderStatus::GODOWN_BOY_POLLED);
      
    }

    /**
     * get packed not boxed order details.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    private function getPackDataForNotBoxed($transorder)
    {
        $packinglist = [
            'packingNumber' => [],
            'BoxDetails'    => []
        ];
        $qugeoOrder = QugeoOrder::select('quor_PacketCount', 'quor_DeliveryMethodsAllowed', 'quor_TransferOrder_Type', 'quor_TransferOrder_id', 'quor_RefNo')->where('quor_TransferOrder_id', $transorder->fsto_id)->first();
        if($qugeoOrder)
        {
            for($i=1; $i <= $qugeoOrder->quor_PacketCount; $i++)
            { 
                $packinglist['packingNumber'][] = "{$qugeoOrder->quor_RefNo}/{$qugeoOrder->quor_PacketCount}/{$i}";
            }
            $branchData = Branch::where('br_ID', $transorder->fsto_source)->first();
            $boxDetails = DB::table('retaline_package_master')
            ->select('rpckm_id', 'rpckm_name', 'rpckm_length', 'rpckm_breadth', 'rpckm_height')
            ->where('rpckm_status', 1)
            ->whereRaw(DB::raw("store_group_id = {$branchData->br_storeGroup} OR (FIND_IN_SET({$branchData->br_ID}, branchId))"));
            if($qugeoOrder->quor_DeliveryMethodsAllowed == 8)
            {
                $boxDetails->where('rpckm_type', 2);
            }
            else
            {
                $boxDetails->where('rpckm_type', 1);
            }
            $packinglist['BoxDetails'] = $boxDetails->get();
            $packinglist['fstoId'] = $qugeoOrder->quor_TransferOrder_id;
            $packinglist['fstoOrderType'] = $qugeoOrder->quor_TransferOrder_Type;
        }
        return $packinglist;
    }
    

    /**
     * pending order response.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    private function pendingOrderResponse($orderId, int $orderReqId = -1, $boyOrderId = -1, $isRevoked = false, $order_pk_id, $forcelogout=0, $isReplenish = false)
    {
        $message = $isRevoked ? 'Order Revoked' : 'New order request received';
        $message = $isReplenish ? 'Replenish Order' : $message;
        $addInfo = $this->pendingOrderAddinfo($order_pk_id, $orderId);
        $response = [
            'message'           => $message,
            'orderId'           => $orderId,             
            'boyOrderId'        => $boyOrderId,
            'orderReqId'        => $orderReqId,
            'isRevoked'         => $isRevoked ? 'true' : 'false',
            'isReplenish'       => $isReplenish ? 'true' : 'false',
            'order_pk_id'       => $order_pk_id,
            'isLogout'          => $forcelogout,
            'no_item_barcode'   => (env('NO_ITEM_BARCODE', false) ? 'true' : 'false'),
            'is_package'        => (env('IS_PACKAGE', false) ? 'true' : 'false'),
            'orgno'             => $addInfo['orgno'],
            'name'              => $addInfo['name'],
            'phone'             => $addInfo['phone'],
            'type'              => $addInfo['type'],
            'amount'            => $addInfo['amount'],
            'payment'           => $addInfo['payment'],
            'delcharge'         => $addInfo['delcharge'],                
            'is_invoice'        => $addInfo['isinvoice'],
            'SONumber'          => $addInfo['SONumber'],
            'pickingNo'         => $addInfo['pickingNumber']
        ];
        return $response;
    }


    /**
     * add info on pending order
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    private function pendingOrderAddinfo($id)
    {
        $transferOrder = TransferOrder::select('fsto_id', 'fstr_id', 'fsto_ordertype', 'fsto_isalreadypacked', 'fsto_pickingNumber')
        ->where('fsto_id', $id)      
        ->first();
        $boyOrdervalue = array();
        $boyOrdervalue['type'] = $transferOrder->fsto_ordertype;  
        $boyOrdervalue['alreadypacked'] = $transferOrder->fsto_isalreadypacked;
        $boyOrdervalue['pickingNumber'] = $transferOrder->fsto_pickingNumber;      
        if($transferOrder->fsto_ordertype == '0')
        {
            $transReq = TransferRequest::select('fstr_destination','fstr_uid')->where('fstr_id', $transferOrder->fstr_id)->first();
            $boyOrdervalue['orgno']  = $transReq->fstr_uid;
            $branchdets = Branch::select('br_name','br_Phone')
            ->where('br_id', $transReq->fstr_destination)
            ->first();
            $boyOrdervalue['payment']  = '' ;
            $boyOrdervalue['delcharge']  = 0 ;
            $boyOrdervalue['name']  = $branchdets->br_name;
            $boyOrdervalue['phone']  = $branchdets->br_Phone;
            $boyOrdervalue['amount']  = 0;   
            $boyOrdervalue['isinvoice']  = 0;
            $boyOrdervalue['SONumber']  ='';
        }
        elseif($transferOrder->fsto_ordertype == '1')
        {           
            $order = Order::where('order_id', $transferOrder->fstr_id)
                        ->select('order_order_id','payment_mode','order_delivery_charge','total','order_branch_id', 'total_afterpacking')
                        ->first();
            $salesOrder = SalesOrder::where('customer_order_id', $transferOrder->fstr_id)
                        ->select('SONumber','id')
                        ->first();
            $delinfo = OrderAddress::where('customer_order_id',$transferOrder->fstr_id)
                        ->select('order_customer_name','order_contact_no')
                        ->first();  
            $branchdets = Branch::select('br_name','br_Phone','br_ownInvoice')
            ->where('br_id', $order->order_branch_id)
            ->first();
            $orderItems = OrderItem::where('customer_order_id', $transferOrder->fstr_id)->pluck('is_restaurant')->toArray();
            $itemRestCount = array_count_values($orderItems);
            $restCount = (@$itemRestCount[1]) ? $itemRestCount[1] : 0;
            $hasInvoice = (count($orderItems) == $restCount) ? 0 : 1;
            $PaidStatus = "";
            if($order->payment_mode > 0)
            {                       
                $PaidStatus = (($order->payment_mode == 1 || $order->payment_mode == 4) ? 'UNPAID' : 'PAID');              
            }
            $boyOrdervalue['payment']  = $PaidStatus;
            $boyOrdervalue['delcharge']  = $order->order_delivery_charge;
            $boyOrdervalue['orgno']  = $order->order_order_id;
            $boyOrdervalue['name']  = $delinfo->order_customer_name;
            $boyOrdervalue['phone']  = $delinfo->order_contact_no;    
            $boyOrdervalue['amount']  = (@$order->total_afterpacking > 0) ? $order->total_afterpacking : $order->total;                     
            $boyOrdervalue['isinvoice']  = $hasInvoice;
            $boyOrdervalue['SONumber']  = $salesOrder->SONumber;
        }
        elseif($transferOrder->fsto_ordertype == '2')
        {
            $order = B2bOrder::where('bbso_id', $transferOrder->fstr_id)
                        ->select('b2b_Customer_ID','bbso_SONumber','bbso_HandlingCharges','bbso_InvValAtax')
                        ->first();
            $delinfo = Client::where('b2b_Customer_ID',$order->b2b_Customer_ID)
                        ->select('b2b_Customer_Name','b2b_Customer_Phone')
                        ->first();  
            $boyOrdervalue['payment']  =  'Credit';
            $boyOrdervalue['delcharge']  = $order->bbso_HandlingCharges;
            $boyOrdervalue['orgno']  = $order->bbso_SONumber; 
            $boyOrdervalue['name']  = $delinfo->b2b_Customer_Name;
            $boyOrdervalue['phone']  = $delinfo->b2b_Customer_Phone; 
            $boyOrdervalue['amount']  = $order->bbso_InvValAtax;   
            $boyOrdervalue['isinvoice']  = 0;
            $boyOrdervalue['SONumber']  ='';
        }
        elseif($transferOrder->fsto_ordertype == '3')
        {
            $order = ReturnPacking::where('frrp_id', $transferOrder->fstr_id)
                        ->select('frrp_source','frrp_uid')
                        ->first();
            $branchdets = Branch::select('br_name','br_Phone')
                        ->where('br_id', $order->frrp_source)
                        ->first();
            $boyOrdervalue['payment']  =  ' '; 
            $boyOrdervalue['delcharge']  = 0; 
            $boyOrdervalue['orgno']  = $order->frrp_uid; 
            $boyOrdervalue['name']  = $branchdets->br_name;
            $boyOrdervalue['phone']  = $branchdets->br_Phone; 
            $boyOrdervalue['amount']  = 0;   
            $boyOrdervalue['isinvoice']  = 0;
            $boyOrdervalue['SONumber']  ='';
        }
        return $boyOrdervalue;
        
    }
}