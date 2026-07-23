<?php
namespace App\Http\Repositories;

use App\Models\{
    Order,
    QugeoOrder,
    OrderHistory,
    TransferOrder,
    OrderCancelled,
    CourierDelivery\ShippingConsignment,
    CourierDelivery\CancelConsignment,
};
use App\Status\{
    CustomerOrderStatus,
    TransferOrderStatus
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\PostingRepository;
use App\Status\DelayedOrderActions;
use App\Events\DelayedOrderActions as DelayedOrderEvent;

class PartnerOrderUpdateRepository
{
	public function __construct()
    {
        $this->postingRepo = new PostingRepository();
    }

	public function orderPickup($data)
	{
        $order = Order::where('order_order_id', $data['orderID'])->first();
        if($order)
        {
            $order->status_id = CustomerOrderStatus::DELIVERY_ASSIGNED;
            $order->save();
            QugeoOrder::where('quor_RefNo', $data['orderID'])->update([
                'quor_Status'   => 27,
                'quor_UpdateOn' => now()
            ]);
            $orderHistory = OrderHistory::create([
                'order_id'      => $order->order_id,
                'order_action'  => "Order to be Picked up via {$data['partner']} {$data['deliveryID']}",
                'order_status'  => CustomerOrderStatus::DELIVERY_ASSIGNED
            ]);
        }
	}
	public function orderPickupComplete($data)
	{
        $order = Order::where('order_order_id', $data['orderID'])->first();
        if($order)
        {
            $order->status_id = CustomerOrderStatus::PICKUP_CONSIGNMENT;
            $order->save();
            QugeoOrder::where('quor_RefNo', $data['orderID'])->update([
                'quor_Status'           => 9,
                'quor_UpdateOn'         => now()
            ]);
            $orderHistory = OrderHistory::create([
                'order_id'      => $order->order_id,
                'order_action'  => "Order Pickup Complete via {$data['partner']} {$data['deliveryID']}",
                'order_status'  => CustomerOrderStatus::PICKUP_CONSIGNMENT
            ]);
            $postReq = new Request();
            $postReq->setMethod('POST');
            $postReq->request->add([
                'order_id'              => @$order->order_id,
                'finascopEventRefId'    => config("event_master.pickupForDelivery"),
                'storegroup_id'         => (@$order->storegroup_id ?? 0)
            ]);
            $this->postingRepo->finascopPosting($postReq);

            event(new DelayedOrderEvent(@$order->order_id, 6));
        }
	}
	public function orderDelivered($data)
	{
        $order = Order::where([
            ['order_order_id', $data['orderID']],
            ['status_id', '!=', CustomerOrderStatus::DELIVERED]
        ])->first();
        if($order)
        {
            $qugeoOrderUpdate = QugeoOrder::where('quor_RefNo', $data['orderID'])->update([
                'quor_Type'             => 7,
                'quor_DeliveryConfTime' => $data['orderDate'],
                'quor_Status'           => 15,
                'quor_UpdateOn'         => now()
            ]);
            $order->status_id = CustomerOrderStatus::DELIVERED;
            $order->save();
            $qugeoOrder = QugeoOrder::where('quor_RefNo', $data['orderID'])->first();
            if($qugeoOrder)
            {
                $orderProcedure = DB::select("CALL UpdateDeliveryStatus({$qugeoOrder->quor_id}, {$order->order_id}, '{$data['orderDate']}')");
            }
            $orderHistory = OrderHistory::create([
                'order_id'      => $order->order_id,
                'order_action'  => "Order Delivered via {$data['partner']} {$data['deliveryID']}",
                'order_status'  => CustomerOrderStatus::DELIVERED
            ]);

            $postReq = new Request();
            $postReq->setMethod('POST');
            $postReq->request->add([
                'order_id'              => $order->order_id,
                'finascopEventRefId'    => config('event_master.deliveryConfirmation'),
                'storegroup_id'         => (@$order->storegroup_id ? $order->storegroup_id : 0)
            ]);
            $this->postingRepo->finascopPosting($postReq);
            
            event(new DelayedOrderEvent(@$order->order_id, 7));
        }
	}
    
    public function orderCancel($data)
    {
        $order = Order::where('order_order_id', $data['orderID'])->first();
        if($order)
        {
            $order->order_cancel_date = now();
            $order->status_id = CustomerOrderStatus::CANCELLED;
            $order->save();

            $cancellationData = OrderCancelled::where('order_id', $order->order_id)->value('reason');
            $orderHistory = OrderHistory::create([
                'order_id'      => $order->order_id,
                'order_action'  => @$cancellationData,
                'order_status'  => CustomerOrderStatus::CANCELLED
            ]);
            TransferOrder::where('fstr_id', $order->order_id)->update([
                'fsto_status'   => TransferOrderStatus::CANCELLED
            ]);

            $postReq = new Request();
            $postReq->setMethod('POST');
            $postReq->request->add([
                'order_id'              => $order->order_id,
                'finascopEventRefId'    => config('event_master.cancellation'),
                'storegroup_id'         => (@$order->storegroup_id ? $order->storegroup_id : 0)
            ]);
            $this->postingRepo->finascopPosting($postReq);
        }
    }
}
