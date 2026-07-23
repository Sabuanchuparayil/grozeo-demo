<?php

namespace BackOffice\Http\Controllers\Transfer;

use Carbon\Carbon;
use App\Models\Order;
use App\Events\OrderHistory;
use BackOffice\Models\B2bOrder;
use BackOffice\Models\BoyOrder;
use BackOffice\Models\CpdOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use BackOffice\Models\ReturnPacking;
use BackOffice\Models\TransferOrder;
use App\Http\Responses\ErrorResponse;
use BackOffice\Status\B2bOrderStatus;
use BackOffice\Models\BoyOrderRequest;
use BackOffice\Models\TransferRequest;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use BackOffice\Status\CustomerOrderStatus;
use BackOffice\Status\TransferOrderStatus;
use BackOffice\Models\TransferOrderDetails;
use BackOffice\Status\BoyOrderRequestStatus;
use BackOffice\Http\Requests\TransferOrderAcceptedRequest;
use BackOffice\Http\Requests\OrderAcceptRequest;
use BackOffice\Http\Resources\TransferOrderCollection;

class TransferOrderAcceptedController
{

    protected $boyorder;
    protected $boyorderrequest;
    protected $transferorder;
    protected $transferorderdetails;
    protected $relatedorder;
    
    protected const ACCEPTED = 1;
    protected const REJECTEDS = 3;
    protected const SUCCESS = 200;

    protected const TRANSFER_REQUEST = 0;

    protected const CUSTOMER_ORDER = 1;

    protected const B2B_ORDER = 2;

    protected const STOCK_RETURN = 3;

    public function __construct(TransferOrder $transferorder, TransferOrderDetails $transferorderdetails, BoyOrder $boyorder, BoyOrderRequest $boyorderrequest)
    {
        $this->transferorder = $transferorder;
        $this->transferorderdetails = $transferorderdetails;
        $this->boyorder = $boyorder;
        $this->boyorderrequest = $boyorderrequest;
    }

    public function __invoke(TransferOrderAcceptedRequest $request)
    {
        try
        {
            $transferOrder = TransferOrder::where('fsto_id', $request->order_pk_id)
            ->whereIn('fsto_status', [
                TransferOrderStatus::CREATED,
                TransferOrderStatus::GODOWN_BOY_POLLED,
                TransferOrderStatus::POLL_NO_RESPONSE,
                TransferOrderStatus::TO_MANUALLY_ASSIGN,
                TransferOrderStatus::POLL_REJECTED
            ])
            ->first();
            if($transferOrder)
            {
                $transferOrder->fsto_status = TransferOrderStatus::ASSIGNED_GODOWN_BOY;
                $transferOrder->save();
                $fcmID = auth_user()->fcm_id;
                DB::statement("UPDATE retaline_firebase_log SET rfir_StatusId = 2 where rfir_StatusId = 1 AND rfir_token = '{$fcmID}'");
                return new SuccessResponse('Order Accepted');
            }
            return new ErrorResponse("Order status changed");
        }
        catch (\Exception $e)
        {
            // info("TransferOrderAcceptedController  ERROR---");info($e);
            return new ErrorResponse("Operation failed"); 
        }
    }


    public function __invoke1(OrderAcceptRequest $request, $orderId)
    {
        
        $orderaction = $request->action;
        $orderReqId = $request->order_request_id;
        $orderpkid = $request->order_pk_id;
        $fcm_token = $request->fcm_token;
        $reposts =false;

        if(intval($orderReqId)==0){
            return new ErrorResponse('Invalid request Id');
        }
        $assignedorderdetails = $this->verifyTransferOrderId($orderpkid);

        if ($orderaction == 0) {
            $this->updateStatusForRejectedOrderActions( $request->order_pk_id,  $orderReqId,$assignedorderdetails);
            DB::statement("UPDATE retaline_firebase_log SET rfir_StatusId=3 where rfir_StatusId=1 AND rfir_token = '".$fcm_token."'");
            return new SuccessResponse('Order Was Rejected');
        }
        
        
        //$assignedorderdetails->
        if ($assignedorderdetails === null) {
            return new ErrorResponse('Transfer Order Already Assigned');
        }elseif($assignedorderdetails->fsto_status == TransferOrderStatus::CREATED ){ //First Time
            $reposts =false;
        }elseif($assignedorderdetails->fsto_status == TransferOrderStatus::CREATED && $assignedorderdetails->fsto_polled_request_id == $orderReqId){ //The status is polled            
                $reposts =false;
        /*}elseif($assignedorderdetails->fsto_status == TransferOrderStatus::GODOWN_BOY_POLLED && $assignedorderdetails->fsto_polled_request_id == $orderReqId){ //The status is polled            
                $reposts =false; //The boy has the latest request id           
        }elseif($assignedorderdetails->fsto_status == TransferOrderStatus::ASSIGNED_GODOWN_BOY && $assignedorderdetails->fsto_assigned_request_id == $orderReqId  ) {
            $reposts =true;
        }elseif($assignedorderdetails->fsto_status > TransferOrderStatus::ASSIGNED_GODOWN_BOY && $assignedorderdetails->fsto_assigned_request_id == $orderReqId  ){           
            return new ErrorResponse('Transfer Order Already Processed');
        }else{
            return new ErrorResponse('Transfer Order Already Polled/Assigned');*/
        }

        /*$order = $this->transferorder
        ->select('fsto_id', 'fsto_uid')
        ->where('fsto_id', $orderpkid)
        ->first();*/

        if ($assignedorderdetails === null) {
            return new ErrorResponse('Order does Not Exist, Choose a right Order with Type.');
        }

        $order_id = $assignedorderdetails->fsto_id;
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;
        $orderData = Order::select('order_id', 'order_method', 'order_notes', 'created_at', 'order_confirmed_on')->with('salesOrder:customer_order_id,SONumber,SODate')->where('order_id', $assignedorderdetails->fstr_id)->first();
        $orderMethod = (@$orderData->order_method == 3) ? 1 : 0;
        $orderCreated = @$orderData->created_at ?? "";
        $orderConfirmed = @$orderData->order_confirmed_on ?? "";
        $salesOrderNo = @$orderData->salesOrder->SONumber;
        $salesOrderDate = @$orderData->salesOrder->SODate;
        /*if($assignedorderdetails->fsto_isalreadypacked == '1'){           
            $neworderdetails = $assignedorderdetails->packedtransferorderDetails()
                ->with('item:stit_ID,stit_SKU,stit_ConvertCalcMode,stit_ConvertCalcRate')
                ->with('price:stit_ID,selling_price')
                ->with('image:product_id,image_thumb_url')
                ->selectraw('fsto_ItemId , fsto_ItemQty, fsto_pkdQty , fstro_ItemMRP, fstro_ItemSPincTax')
                ->get();
        }else{*/
            $neworderdetails = $assignedorderdetails->transferorderDetails()
                ->with('item:stit_ID,stit_SKU,stit_ConvertCalcMode,stit_ConvertCalcRate,product_category')
                ->with('price:stit_ID,selling_price')
                ->with('image:product_id,image_url')
                ->selectraw('fsto_ItemId ,  fsto_ItemQty , fsto_pkdQty, fstro_ItemMRP, fstro_ItemSPincTax ')
                ->get();
        //}
                $restCatCheck = 0;
        foreach($neworderdetails as &$neworderdetail)
        {
            $neworderdetail->item->stit_SKU = $neworderdetail->item->stit_SKU; 
            $neworderdetail->item->mrp = $neworderdetail->fstro_ItemMRP;
            $neworderdetail->item->erpID = DB::table('finascop_stock_itemmaster_product_codes')->where('fsipc_stit_id', $neworderdetail->item->stit_ID)->value('fsipc_code');
            if(@$neworderdetail->image)
            {
                $neworderdetail->image->image_url =  $domain.'thumbnail-'.$neworderdetail->image->image_url;
            }
            if(@$neworderdetail->item->productCategory->hasRestaurantService == 1)
            {
                $restCatCheck++;
            }
        }
        $boyOrder = null;
        if($reposts){
            $boyOrder = BoyOrder::where('order_pk_id', $order_id)           
            ->where('boy_id', auth_user()->id)
            ->orderBy('id', 'desc')
            ->first(['id']);
        }else{
        DB::transaction(function () use ($orderId, $assignedorderdetails, &$boyOrder, $orderReqId,$order_id) {
            // $boyOrder = $this->assignOrderToBoy($orderId, $orderReqId,$order_id,$assignedorderdetails);
            $this->relatedactionsOnAccept($assignedorderdetails, $assignedorderdetails->fsto_ordertype);
            $this->updateOrderStatus($assignedorderdetails, $orderReqId,auth_user()->id);
        });
        }    
        DB::statement("UPDATE retaline_firebase_log SET rfir_StatusId=2 where rfir_StatusId=1 AND rfir_token = '".$fcm_token."'");
        
        return new SuccessWithData(
            new TransferOrderCollection($neworderdetails, @$boyOrder->id, $assignedorderdetails, $orderMethod, $restCatCheck, @$orderData->order_notes, $orderCreated, $orderConfirmed, $salesOrderNo, $salesOrderDate)
        );

    }


    public function updateStatusForRejectedOrderActions($orderId,  $orderReqId, $assignedorderdetails)
    {
        DB::transaction(function () use ($orderId,  $orderReqId,$assignedorderdetails) {
            BoyOrderRequest::where('id', $orderReqId)
                ->update(['status' => static::REJECTEDS]);  
                $orderField ='fsto_id';
				$order =  $this->transferorder;
                $statusField = 'fsto_status';

            $orderGraceTime = Carbon::parse($assignedorderdetails->fsto_openingtime)
                ->addMinutes(config('app.manual_assignment_grace_time'));
            if (now()->gt($orderGraceTime)) {
                $status = TransferOrderStatus::TO_MANUALLY_ASSIGN;	
            }else{
                $status = TransferOrderStatus::CREATED;	
            }
			
            $order->where($orderField, $orderId)->update([$statusField => $status]);
            TransferOrder::reverseStatusUpdate($orderId,TransferOrderStatus::POLL_REJECTED);
            $transorder =  TransferOrder::where('fsto_id', $orderId)
            ->select('fsto_ordertype','fstr_id')
            ->first() ;
            if($transorder->fsto_ordertype == 1){
                Order::where('order_id', $transorder->fstr_id)                       
                    ->update(['status_id' => 7]);
            }
        });

    }


    public function assignOrderToBoy($orderId, $orderRequestId, int $orderpkid,$model)
    {
        
        $orderField = 'fsto_id';			
        $branchId =  $model->fsto_source;
        
       
		$order = $model->where($orderField, $orderId)->first();

        $boyOrder = BoyOrder::create([
            'status' => static::ACCEPTED,          
            'order_id' => $orderId,
            'accepted_time' => now(),
            'boy_id' => auth_user()->id,
            'branch_id' => $branchId,
            'bgor_id' => $orderRequestId,     
            'order_pk_id' => $orderpkid,
        ]);

        auth_user()->update(['has_open_orders' => 1]);


        return $boyOrder;
    }

    protected function relatedactionsOnAccept($assignedorderdetails, int $type)
    {
        if($type === static::TRANSFER_REQUEST){
            $this->relatedorder = new TransferRequest;
            return;
       }else{
           if($type === static::CUSTOMER_ORDER){
            $this->relatedorder =new Order;
            $orderField = 'order_id';
            $statusField = 'status_id';
            $status= CustomerOrderStatus::ASSIGNED_GODOWN_BOY;
           }elseif($type === static::B2B_ORDER){
            $this->relatedorder =new B2bOrder;
            $orderField = 'bbso_id';
            $statusField = 'status_id';
            $status= B2bOrderStatus::ASSIGNED_GODOWN_BOY;
           }elseif($type === static::STOCK_RETURN){
            $this->relatedorder =new ReturnPacking;
            $orderField = 'frrp_id';
            $statusField = 'frrp_status';
            $status= 1;
           }       
       } 
   
        if ($type == static::CUSTOMER_ORDER) {
            $this->relatedorder
                ->where($orderField, $assignedorderdetails->fstr_id)
                ->update([$statusField => $status]);
                event(new OrderHistory($assignedorderdetails->fstr_id, CustomerOrderStatus::ASSIGNED_GODOWN_BOY));
        } else {
            $this->relatedorder
                ->where($orderField, $assignedorderdetails->fstr_id)
                ->update([$statusField => $status]);
        }
    }

    public function verifyTransferOrderId($orderId)
    {
        return TransferOrder::where([
            ['fsto_id', '=', $orderId],
            // ['fsto_polled_boy', '=', auth_user()->id],
        ])->first();
    }

    protected function updateOrderStatus($order, int $orderReqId, int $assignedboyId)
    {
        if($order->fsto_status == TransferOrderStatus::PICKER_APPROVED)
        {
            return false;
        }
        $statusField = 'fsto_status';
        $status = TransferOrderStatus::ASSIGNED_GODOWN_BOY;
        $assignedboyField = 'fsto_assigned_boy';
        $assignedrequestfield = 'fsto_assigned_request_id';

        $order->update([$statusField => $status,$assignedboyField =>$assignedboyId,$assignedrequestfield => $orderReqId]);

        $this->boyorderrequest
            ->where('id', $orderReqId)
            ->update(['status' => BoyOrderRequestStatus::ACCEPTED]);

        TransferOrder::reverseStatusUpdate($order->fsto_id,TransferOrderStatus::ASSIGNED_GODOWN_BOY);
    }
}
