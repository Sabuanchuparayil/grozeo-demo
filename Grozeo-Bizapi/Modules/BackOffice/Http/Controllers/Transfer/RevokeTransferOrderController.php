<?php

namespace BackOffice\Http\Controllers\Transfer;

use Exception;
use BackOffice\Models\BoyOrder;
use App\Exceptions\MsgException;
use BackOffice\Models\GodownBoy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use BackOffice\Models\TransferOrder;
use BackOffice\Status\BoyOrderStatus;
use App\Http\Responses\{
    ErrorResponse,
    SuccessResponse
};
use BackOffice\Status\TransferOrderStatus;
use BackOffice\Http\Requests\RevokeOrderRequest;
use BackOffice\Traits\CanSendNotificationsToBoy;
use BackOffice\Status\CustomerOrderStatus;
use BackOffice\Status\B2bOrderStatus;
use BackOffice\Models\TransferRequest;
use BackOffice\Models\ReturnPacking;
use App\Models\Order;
use App\Events\OrderHistory;
use BackOffice\Models\B2bOrder;

class RevokeTransferOrderController
{
    use CanSendNotificationsToBoy;
    
    protected $order;

    protected $boyOrder;

    protected $model;

    protected $orderField;

    protected $boy;
    
    protected $relatedorder;
    
    protected const TRANSFER_REQUEST = 0;

    protected const CUSTOMER_ORDER = 1;

    protected const B2B_ORDER = 2;

    protected const STOCK_RETURN = 3;

    public function __construct(TransferOrder $order, BoyOrder $boyOrder, GodownBoy $boy)
    {
        $this->order = $order;
        $this->boyOrder = $boyOrder;
        $this->boy = $boy;
    }

    public function __invoke(RevokeOrderRequest $request)
    {
        try
        {
            $boyID = ($request->type == "picker-request") ? auth_user()->id : $request->boy_id;
            $transferOrder = TransferOrder::join('retaline_godown_boy', 'branch_id', 'fsto_source')
            ->where([
                ['fsto_id', $request->order_pk_id],
                ['retaline_godown_boy.id', $boyID]
            ])
            ->with('boy:id,fcm_id')
            ->with('boyOrder:id,order_pk_id')
            ->first();
            if($transferOrder)
            {
                $boy = GodownBoy::find($boyID, ['id', 'fcm_id']);
                $boyOrder = $this->boyOrder
                ->where('order_pk_id', $request->order_pk_id)->latest()->first();
                DB::transaction(function () use ($transferOrder, $request, $boyOrder, $boy)
                {
                    $this->updateOrder($transferOrder);
                    $this->relatedactionsOnRevoke($transferOrder,$transferOrder->fsto_ordertype);
                    $this->updateBoyOrder($boyOrder);
                    $this->releaseBoy($boy);
                });
                if($request->type == "picker-request")
                {
                    if(@$transferOrder->boy->id)
                    {
                        $this->sendNotificationToBoy($transferOrder->fsto_uid, $transferOrder->boy->fcm_id, -1, $transferOrder->boyOrder->id, true, $request->order_pk_id);
                    }
                }
                else
                {
                    $this->sendNotificationToBoy($transferOrder->fsto_uid, $boy->fcm_id, -1, $boyOrder->id, true, $request->order_pk_id);
                }
                return new SuccessResponse('Order revoked successfully');
            }
            return new ErrorResponse("Order not found");
        }
        catch (\Exception $e)
        {
            // info("RevokeTransferOrderController ERROR---");info($e);
            return new ErrorResponse("Operation failed"); 
        }

    }
    public function __invoke1(RevokeOrderRequest $request)
    {
        $boy = null;
        if(isset($request->type) && $request->type=="picker-request"){
            $actionbypicker = true;
            $boy = GodownBoy::find(auth_user()->id, ['id', 'fcm_id']);         
        }else{
            $actionbypicker = false;
            $boy = GodownBoy::find($request->boy_id, ['id', 'fcm_id']);
        }

        if(!isset($boy->id) || intval($boy->id)==0){         
            return new ErrorResponse('Invalid Order picker');
        }

        $boyOrder = $this->boyOrder
            ->where('boy_id', $boy->id)
            ->where('order_pk_id', $request->order_pk_id)
            ->latest()
            ->first();

        if (is_null($boyOrder) ) {
            throw new MsgException('Not a valid Order');
        }

       
        $order = $this->getOrder($request->order_pk_id);

        DB::transaction(function () use ($order, $request, $boyOrder, $boy) {
            if($order->fsto_assigned_boy ==  $boy->id){
                $this->updateOrder($order);
                $this->relatedactionsOnRevoke($order,$order->fsto_ordertype);
            }
            $this->updateBoyOrder($boyOrder);
            $this->releaseBoy($boy);
        });
        
        if(!$actionbypicker){
            $this->sendNotificationToBoy(
                $order->fsto_uid,
                $boy->fcm_id,        
                -1,
                $boyOrder->id,
                true,
                $request->order_pk_id
            );
        }
        return new SuccessResponse('Order revoked successfully');
    }

    /**
     * Get the order by order id
     *
     * @param string $orderId
     * @param int $isCpd
     * @return void
     */
    protected function getOrder($orderId)
    {
        $this->model = new TransferOrder;

        return $this->model
            ->where('fsto_id', $orderId)
            ->first();
    }

    /**
     * Update the order status.
     *
     * @param CpdOrder|Order $order
     * @param bool $isCpd
     * @return void
     */
    protected function updateOrder($order)
    {
        $order->update([
            "fsto_status"           => TransferOrderStatus::ASSIGNED_GODOWN_BOY,
            "fsto_assigned_boy"     => 0,
            "fsto_isalreadypacked"  => 0,
            "fsto_pickingNumber"    => ""
        ]);
    }

    /**
     * Update boy order status.
     *
     * @param BoyOrder $order
     * @return void
     */
    protected function updateBoyOrder($order)
    {
        if($order)
        {
            $order->update(['status' => BoyOrderStatus::REVOKED]);
        }
    }

    /**
     * Release the godown boy from the accepted order.
     *
     * @param GodownBoy $boy
     * @return void
     */
    protected function releaseBoy(GodownBoy $boy)
    {
        $boy->update(['has_open_orders' => 0]);
    }
    
    protected function relatedactionsOnRevoke($assignedorderdetails, int $type)
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
            $status= B2bOrderStatus::MANUALLY_ASSIGNED_ORDER_PICKER;
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
}
