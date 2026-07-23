<?php

namespace BackOffice\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use BackOffice\Models\BoyOrder;
use BackOffice\Models\CpdOrder;
use BackOffice\Status\OrderType;
use Illuminate\Support\Facades\DB;
use BackOffice\Models\CpdOrderItems;
use BackOffice\Status\CpdOrderStatus;
use BackOffice\Models\BoyOrderRequest;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessWithData;
use BackOffice\Status\CustomerOrderStatus;
use BackOffice\Status\BoyOrderRequestStatus;
use BackOffice\Http\Resources\CpdOrderResource;
use BackOffice\Http\Requests\OrderAcceptRequest;
use BackOffice\Http\Resources\CpdOrderCollection;
use BackOffice\Http\Resources\CustomerOrderResource;
use BackOffice\Http\Resources\CustomerOrderCollection;
use BackOffice\Http\Resources\B2bOrderResource;
use BackOffice\Http\Resources\B2bOrderCollection;
use BackOffice\Models\B2bOrder;
use BackOffice\Models\B2bOrderItems;
use BackOffice\Status\B2bOrderStatus;
use Illuminate\Support\Facades\Log;




class OrderAcceptedController
{
    protected $order;
    protected $orderitem;
    protected $cpdorder;
    protected $cpdorderitem;
    protected $boyorder;
    protected $boyorderrequest;
    protected $b2border;
    protected $b2borderitem;
    
    protected const ACCEPTED = 1;
    protected const REJECTEDS = 3;
    protected const SUCCESS = 200;

    public function __construct(Order $order, OrderItem $orderitem, CpdOrder $cpdorder, CpdOrderItems $cpdorderitem,B2bOrder $b2border, B2bOrderItems $b2borderitem, BoyOrder $boyorder, BoyOrderRequest $boyorderrequest)
    {
        $this->order = $order;
        $this->orderitem = $orderitem;
        $this->cpdorder = $cpdorder;
        $this->cpdorderitem = $cpdorderitem;
        $this->b2border = $b2border;
        $this->b2borderitem = $b2borderitem;
        $this->boyorder = $boyorder;
        $this->boyorderrequest = $boyorderrequest;
    }

    public function __invoke(OrderAcceptRequest $request, $orderId)
    {
       
        $ordertype = $request->type;
        $orderaction = $request->action;
        $orderReqId = $request->order_request_id;
        $orderpkid = $request->order_pk_id;
        $reposts =false;

        if ($orderaction == 0) {
            $this->updateStatusForRejectedOrderActions($orderId, $ordertype, $orderReqId);
            return new SuccessResponse('Order Was Rejected');
        }

        if ($ordertype == 0 && $orderaction == 1) {
            $assignedorderdetails = $this->verifyOrderId($orderId);
            //$assignedorderdetails->
            if ($assignedorderdetails === null) {
                return new ErrorResponse('Order Already Assigned');
            }elseif($assignedorderdetails->order_status == CpdOrderStatus::MANUAL_QUEUED ){
                $reposts =false;
            }elseif($assignedorderdetails->order_status == CpdOrderStatus::GODOWN_BOY_POLLED && $assignedorderdetails->order_polled_boy == auth_user()->id  ){
                $reposts =false;
            }elseif($assignedorderdetails->order_status == CpdOrderStatus::ASSIGNED_GODOWN_BOY && $assignedorderdetails->order_assigned_boy == auth_user()->id  ) {
                $reposts =true;
            }elseif($assignedorderdetails->order_status > CpdOrderStatus::ASSIGNED_GODOWN_BOY && $assignedorderdetails->order_assigned_boy == auth_user()->id  ){
                return new ErrorResponse('Order Already Processed');
            }else{
                return new ErrorResponse('Order Already Polled/Assigned');
            }
        }

        if ($ordertype == 1 && $orderaction == 1) {
            $assignedorderdetails = $this->verifyCustomerOrderId($orderId);
            if ($assignedorderdetails === null) {
                return new ErrorResponse('Order Already Assigned');
            }elseif($assignedorderdetails->order_status == CustomerOrderStatus::SUCCESS ){
                $reposts =false;
            }elseif ($assignedorderdetails->order_status == CustomerOrderStatus::GODOWN_BOY_POLLED && $assignedorderdetails->order_polled_boy == auth_user()->id  ) {
                $reposts =true;
            }elseif ($assignedorderdetails->order_status == CustomerOrderStatus::ASSIGNED_GODOWN_BOY && $assignedorderdetails->order_assigned_boy == auth_user()->id  ) {
                $reposts =true;
            }elseif($assignedorderdetails->order_status > CustomerOrderStatus::ASSIGNED_GODOWN_BOY && $assignedorderdetails->order_assigned_boy == auth_user()->id  ){
                return new ErrorResponse('Order Already Processed');
            }else{
                return new ErrorResponse('Order Already Polled/Assigned');
            }
        }
        
        if ($ordertype == 2 && $orderaction == 1) {
            $assignedorderdetails = $this->verifyB2bOrderId($orderId);
            if ($assignedorderdetails === null) {
                return new ErrorResponse('Order Already Assigned');
            }elseif($assignedorderdetails->order_status == CustomerOrderStatus::SUCCESS ){
                $reposts =false;
            }elseif ($assignedorderdetails->order_status == CustomerOrderStatus::GODOWN_BOY_POLLED && $assignedorderdetails->bbso_polled_boy == auth_user()->id  ) {
                $reposts =true;
            }elseif ($assignedorderdetails->order_status == CustomerOrderStatus::ASSIGNED_GODOWN_BOY && $assignedorderdetails->order_assigned_boy == auth_user()->id  ) {
                $reposts =true;
            }elseif($assignedorderdetails->order_status > CustomerOrderStatus::ASSIGNED_GODOWN_BOY && $assignedorderdetails->order_assigned_boy == auth_user()->id  ){
                return new ErrorResponse('Order Already Processed');
            }else{
                return new ErrorResponse('Order Already Polled/Assigned');
            }            
        }

        if ($ordertype == 0) {
            $order = $this->cpdorder
                ->select('order_id', 'order_no')
                ->where('order_no', $orderId)
                ->first();

            if ($order === null) {
                return new ErrorResponse('Order Was Not Exist, Choose a right Order with Type.');
            }

            $order_id = $order->order_id;

            $neworderdetails = $order->orderItems()
                ->with('item:stit_ID,stit_SKU')
                ->with('price:stit_ID,selling_price')
                ->with('image:product_id,image_thumb_url')
                ->get(['stit_ID', 'bcod_Count']);

            $boyOrder = null;
            if($reposts){
                $boyOrder = BoyOrder::where('order_pk_id', $order_id)
                ->where('is_cpd', 1)
                ->where('boy_id', auth_user()->id)
                ->orderBy('id', 'desc')
                ->first(['id']);
            }else{
            DB::transaction(function () use ($orderId, $ordertype, $order, &$boyOrder, $orderReqId,$order_id) {
                $boyOrder = $this->assignOrderToBoy($orderId, $ordertype, $orderReqId,$order_id);
                $this->updateOrderStatus($order, $ordertype, $orderReqId,auth_user()->id);
            });
        }
            return new SuccessWithData(
                new CpdOrderCollection($neworderdetails, $boyOrder->id)
            );
        }


        if ($ordertype == 1) {

            $order = $this->order
                ->select('order_id', 'order_order_id')
                ->where('order_order_id', $orderId)
                ->first();

            if ($order === null) {
                return new ErrorResponse('Order Was Not Exist, Choose a right Order with Type.');
            }

            $order_id = $order->order_id;

            $neworderdetails = $order->orderItems()
                ->with('item:stit_ID,stit_SKU')
                ->with('price:stit_ID,selling_price')
                ->with('image:product_id,image_thumb_url')
                ->get(['item_product_id', 'item_order_qty']);

            $boyOrder = null;
            if( $reposts){
                $boyOrder = BoyOrder::where('order_pk_id', $order_id)
                ->where('is_cpd', 1)
                ->where('boy_id', auth_user()->id)
                ->orderBy('id', 'desc')
                ->first(['id']);
            }else{
            DB::transaction(function () use ($orderId, $ordertype, $order, &$boyOrder, $orderReqId,$order_id) {
                $boyOrder = $this->assignOrderToBoy($orderId, $ordertype, $orderReqId,$order_id);
                $this->updateOrderStatus($order, $ordertype, $orderReqId,auth_user()->id);
            });
        }
            
            return new SuccessWithData(
                new CustomerOrderCollection($neworderdetails, $boyOrder->id)
            );
        }
        // B2bOrder
        if ($ordertype == 2) {
            $order = $this->b2border
                ->select('bbso_id', 'bbso_SONumber')
                ->where('bbso_SONumber', $orderId)
                ->first();

            if ($order === null) {
                return new ErrorResponse('Order Was Not Exist, Choose a right Order with Type.');
            }

            $order_id = $order->bbso_id;
            $neworderdetails = $order->orderItems()->get(['b2bso_itemid', 'b2bso_netamount','b2bso_itemqty','b2bso_itemname']);

            $boyOrder = null;
            if( $reposts){
                $boyOrder = BoyOrder::where('order_pk_id', $order_id)
                ->where('is_cpd', 1)
                ->where('boy_id', auth_user()->id)
                ->orderBy('id', 'desc')
                ->first(['id']);
            }else{
                DB::transaction(function () use ($orderId, $ordertype, $order, &$boyOrder, $orderReqId,$order_id) {

                $boyOrder = $this->assignOrderToBoy($orderId, $ordertype, $orderReqId,$order_id);
                $this->updateOrderStatus($order, $ordertype, $orderReqId,auth_user()->id);
            });
            }
            return new SuccessWithData(
                new B2bOrderCollection($neworderdetails, $boyOrder->id)
            ); 
        }
    }


    public function updateStatusForRejectedOrderActions($orderId, int $orderType, $orderReqId)
    {
        DB::transaction(function () use ($orderId, $orderType, $orderReqId) {
            BoyOrderRequest::where('id', $orderReqId)
                ->update(['status' => static::REJECTEDS]);
				
				
			if ($orderType === 0) {
				$orderField ='order_no';
				$order =  $this->cpdorder;
				$statusField = 'order_status';
				$status = CpdOrderStatus::MANUAL_QUEUED;
			}else{
				$statusField = 'status_id';
				if ($orderType === 1) {
					$order =  $this->order;
					 $orderField ='order_order_id';
					 $status = CustomerOrderStatus::POLL_REJECTED;
				}else{
					$order =  $this->b2border;
					 $orderField ='bbso_SONumber';
					 $status = B2bOrderStatus::MANUAL_ASSIGNMENT;
				}
			}

            /*$order = $orderType === 0 ? $this->cpdorder : ($orderType === 1 )?
            $this->order:$this->b2border;

            $orderField = $orderType === 0
                ? 'order_no'
                : $orderType == 1 ? 'order_order_id':'bbso_SONumber' ;

            $statusField = $orderType === 0
                ? 'order_status'
                : 'status_id';

            $status = $orderType === 0
                ? CpdOrderStatus::MANUAL_QUEUED
                : $orderType == 1 ? CustomerOrderStatus::POLL_REJECTED: B2bOrderStatus::MANUAL_ASSIGNMENT  ;
			*/
            $order->where($orderField, $orderId)->update([$statusField => $status]);
        });
    }


    public function assignOrderToBoy($orderId, int $orderType, $orderRequestId, int $orderpkid)
    {
		  $is_b2border = 0;
			
		if($orderType === OrderType::CPD_ORDER){
          $model = $this->cpdorder;
            $orderField = 'order_no';
			
        }else{
            if($orderType === OrderType::B2B_ORDER){
               $model = $this->b2border;
                 $orderField = 'bbso_SONumber';
				 
				  $is_b2border = 1;
            }else{
				$model = $this->order;
				  $orderField = 'order_order_id';
				 
				  
            }
        }

       
		$order = $model->where($orderField, $orderId)->first();
		if($orderType === OrderType::CPD_ORDER){

			 $branchId =  $order->cpd_id ;
        }else{
            if($orderType === OrderType::B2B_ORDER){
				  $branchId =  $order->br_ID ;
				  $is_b2border = 1;
            }else{
				   $branchId =  $order->order_branch_id ;				  
            }
        }
       /* $model = $orderType === OrderType::CPD_ORDER ? $this->cpdorder : $this->order;
        $model = $orderType === OrderType::B2B_ORDER ? $this->b2border:$model;
       
        $orderField = $orderType === OrderType::CPD_ORDER
            ? 'order_no'
            : 'order_order_id';

         $orderField = $orderType === OrderType::B2B_ORDER
            ? 'bbso_SONumber'
            : $orderField;
		*/


        /*$branchId = $orderType == OrderType::CPD_ORDER 
            ? $order->branch_id 
            : $order->order_branch_id;
        $branchId = $orderType === OrderType::B2B_ORDER
            ? $order->br_ID
            : $branchId;
		*/
        $boyOrder = BoyOrder::create([
            'status' => static::ACCEPTED,
            'is_cpd' => $orderType == 0 ? 1 : 0,
            'order_id' => $orderId,
            'accepted_time' => now(),
            'boy_id' => auth_user()->id,
            'branch_id' => $branchId,
            'bgor_id' => $orderRequestId,
            'is_b2border' => $is_b2border,
            'order_pk_id' => $orderpkid,

        ]);

        auth_user()->update(['has_open_orders' => 1]);

        return $boyOrder;
    }

    public function verifyOrderId($orderId)
    {
        return CpdOrder::where([['order_no', '=', $orderId]])->first();
    }


    public function verifyCustomerOrderId($orderId)
    {
        return Order::where([['order_order_id', '=', $orderId]])->first();
    }
     public function verifyB2bOrderId($orderId)
    {
        return B2bOrder::where([['bbso_SONumber', '=', $orderId]])->first();
    }

    protected function updateOrderStatus($order, int $orderType, int $orderReqId, int $assignedboyId)
    {
		if($orderType === 0){
         $statusField = 'order_status';
         $status = CpdOrderStatus::ASSIGNED_GODOWN_BOY;
         $assignedboyField = 'order_assigned_boy';
        }else{
			 $statusField = 'status_id';
            if($orderType === 1){
                $status = CustomerOrderStatus::ASSIGNED_GODOWN_BOY;
                $assignedboyField = 'order_assigned_boy';
            }else{
				$status = B2bOrderStatus::ASSIGNED_GODOWN_BOY;
                $assignedboyField = 'bbso_assigned_boy';  
            }
        }
       /* $statusField = $orderType === 0
            ? 'order_status'
            : 'status_id';

        $status = $orderType === 0
            ? CpdOrderStatus::ASSIGNED_GODOWN_BOY
            : $orderType === 1 ? CustomerOrderStatus::ASSIGNED_GODOWN_BOY: B2bOrderStatus::ASSIGNED_GODOWN_BOY;
		*/
        $order->update([$statusField => $status,$assignedboyField =>$assignedboyId]);

        $this->boyorderrequest
            ->where('id', $orderReqId)
            ->update(['status' => BoyOrderRequestStatus::ACCEPTED]);

    }
}
