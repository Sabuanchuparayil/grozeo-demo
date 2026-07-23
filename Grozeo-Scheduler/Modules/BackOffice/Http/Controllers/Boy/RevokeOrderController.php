<?php

namespace BackOffice\Http\Controllers\Boy;

use Exception;
use App\Models\Order;
use BackOffice\Models\B2bOrder;
use BackOffice\Models\BoyOrder;
use BackOffice\Models\CpdOrder;
use App\Exceptions\MsgException;
use BackOffice\Models\GodownBoy;
use BackOffice\Status\OrderType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use BackOffice\Status\B2bOrderStatus;
use BackOffice\Status\BoyOrderStatus;
use BackOffice\Status\CpdOrderStatus;
use App\Http\Responses\SuccessResponse;
use BackOffice\Status\CustomerOrderStatus;
use BackOffice\Http\Requests\RevokeOrderRequest;
use BackOffice\Traits\CanSendNotificationsToBoy;
use BackOffice\Http\Requests\BoyOrderAssignRequest;

class RevokeOrderController
{
    use CanSendNotificationsToBoy;
    
    protected $order;

    protected $boyOrder;

    protected $model;

    protected $orderField;

    protected $boy;

    public function __construct(CpdOrder $order, BoyOrder $boyOrder, GodownBoy $boy)
    {
        $this->order = $order;
        $this->boyOrder = $boyOrder;
        $this->boy = $boy;
    }

    public function __invoke(RevokeOrderRequest $request)
    {
        $boyOrder = $this->boyOrder
            ->where('boy_id', $request->boy_id)
            ->where('order_id', $request->order_id)
            ->where('is_cpd', $request->is_cpd)
            ->where('is_b2border', $request->is_b2border)
            ->latest()
            ->first();

        if (is_null($boyOrder)) {
            throw new MsgException('No order found for boy');
        }

        $boy = $this->boy->find($request->boy_id, ['id', 'fcm_id']);
        $order = $this->getOrder($request->order_id, $request->is_cpd,$request->is_b2border);

        DB::transaction(function () use ($order, $request, $boyOrder, $boy) {
            $this->updateOrder($order, $request->is_cpd,$request->is_b2border);
            $this->updateBoyOrder($boyOrder);
            $this->releaseBoy($boy);
        });
        if($request->is_cpd == 1){
			 $orderType =  OrderType::CPD_ORDER;
		}else{
			$orderType =   OrderType::CUSTOMER_ORDER;
		}
        //$orderType = $request->is_cpd ? OrderType::CPD_ORDER : OrderType::CUSTOMER_ORDER;
        
        $this->sendNotificationToBoy(
            $request->order_id,
            $boy->fcm_id,
            -1,
            $boyOrder->id,
            true,
            $request->orderautoId
        );

        return new SuccessResponse('Order revoked successfully');
    }

    /**
     * Get the order by order id
     *
     * @param string $orderId
     * @param int $isCpd
     * @return void
     */
    protected function getOrder($orderId, $isCpd,$isB2border)
    {
		 if($isCpd){
			 $this->model = new CpdOrder;
			 $this->orderField =  'order_no';
		 }else{
			 if($isB2border){
			  $this->model = new b2border;
			   $this->orderField =  'bbso_SONumber';
			 }else{
			  $this->model = new Order;
			   $this->orderField =  'order_order_id';
			 }
		 }
        /*$this->model = $isCpd ? new CpdOrder : new Order;
        $this->model= $isB2border? new b2border : $this->model;

        $this->orderField = $isCpd ? 'order_no' :($isB2border)? 'bbso_SONumber' : 'order_order_id';
	*/
        return $this->model
            ->where($this->orderField, $orderId)
            ->first();
    }

    /**
     * Update the order status.
     *
     * @param CpdOrder|Order $order
     * @param bool $isCpd
     * @return void
     */
    protected function updateOrder($order, $isCpd,$isB2border)
    {
         if($isCpd){
			 $statusField = 'order_status';
			  $status = CpdOrderStatus::MANUAL_QUEUED;
		 }else{
			 if($isB2border){
			 $statusField = 'status_id';
			  $status = B2bOrderStatus::MANUAL_ASSIGNMENT;
			 }else{
			 $statusField = 'status_id';
			  $status = CustomerOrderStatus::MANUAL_ASSIGNMENT;
			 }
		 }
        /*$statusField = $isCpd
            ? 'order_status'
            : 'status_id';

        $status = $isCpd
            ? CpdOrderStatus::MANUAL_QUEUED
            : ($isB2border)?B2bOrderStatus::MANUAL_ASSIGNMENT:CustomerOrderStatus::MANUAL_ASSIGNMENT;
		*/
        $order->update([$statusField => $status]);
    }

    /**
     * Update boy order status.
     *
     * @param BoyOrder $order
     * @return void
     */
    protected function updateBoyOrder($order)
    {
        $order->update(['status' => BoyOrderStatus::REVOKED]);
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
}
