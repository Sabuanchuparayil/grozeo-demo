<?php
namespace App\Http\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\{
    Order,
    OrderHistory,
    TransferOrder
};

class PackingUpdateRepository
{
	public function __construct() {}

    public function accepted($orderID, $type = "")
    {
        $order = Order::where('order_order_id', $orderID)->frst();
        if($order)
        {
            $now = now();
            TransferOrder::where('fstr_id', $order->order_id)->update([
                'fsto_status'   => 2
            ]);
            $order->status_id = 5;
            $order->save();
            OrderHistory::create([
                'order_id'      => $order->order_id,
                'order_action'  => "Order packing accepted via {$type} on {$now}",
                'order_status'  => 5
            ]);
        }
    }
    public function packing($orderID, $type = "")
    {
    }
    public function packed($orderID, $type = "")
    {
        $order = Order::where('order_order_id', $orderID)->frst();
        if($order)
        {
            $now = now();
            TransferOrder::where('fstr_id', $order->order_id)->update([
                'fsto_status'   => 10
            ]);
            $order->status_id = 9;
            $order->save();
            OrderHistory::create([
                'order_id'      => $order->order_id,
                'order_action'  => "Order packing completed via {$type} on {$now}",
                'order_status'  => 9
            ]);
        }
    }
    public function cancelled($orderID, $type = "")
    {
        $order = Order::where('order_order_id', $orderID)->frst();
        if($order)
        {
            $now = now();
            TransferOrder::where('fstr_id', $order->order_id)->update([
                'fsto_status'   => 6
            ]);
            $order->status_id = 7;
            $order->save();
            OrderHistory::create([
                'order_id'      => $order->order_id,
                'order_action'  => "Order packing cancelled via {$type} on {$now}",
                'order_status'  => 7
            ]);
        }
    }
}
