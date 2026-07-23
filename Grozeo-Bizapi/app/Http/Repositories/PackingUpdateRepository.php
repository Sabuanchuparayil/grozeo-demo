<?php
namespace App\Http\Repositories;

use App\Models\{
    Order,
    OrderHistory
};
use Illuminate\Support\Facades\DB;
use BackOffice\Models\TransferOrder;

use BackOffice\Models\Inventory;
use BackOffice\Models\BranchInventory;
use BackOffice\Actions\Inventory\QugeoProcessor;
use BackOffice\Actions\Inventory\RecordItemHistory;
use BackOffice\Actions\Inventory\ProcessInventoryItems;
use BackOffice\Http\Requests\TransferOrderProceedNoBarcodeRequest;
use BackOffice\Http\Controllers\Transfer\TransferOrderNoBarcodeProceedController;

class PackingUpdateRepository
{
    public function __construct() {}

    public function accepted($orderID, $type = "")
    {
        $order = Order::where('order_order_id', $orderID)->first();
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
        $order = Order::where('order_order_id', $orderID)->first();
        if($order)
        {
            $this->relatedPackedActions($order);
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
        $order = Order::where('order_order_id', $orderID)->first();
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

    private function relatedPackedActions($order)
    {
        $transferOrder = TransferOrder::where('fstr_id', $order->order_id)->first();

        $items = [];
        foreach ($order->orderItems as $item)
        {
            $items[] = [
                'item_id'           => $item->item_product_id,
                'count'             => $item->item_order_qty,
                'fsto_stockValue'   => 0
            ];
        }

        $request = new TransferOrderProceedNoBarcodeRequest();
        $request->setMethod('POST');
        $request->request->add([
            'boy_order_id'  => -10,
            'number_bags'   => 1,
            'invoicedate'   => date('Y-m-d'),
            'invoiceno'     => rand(1000, 100000),
            'invoiceamt'    => $order->order_amount_payable,
            'is_incomplete' => false,
            'items'         => $items,
            'ismanual'      => true
        ]);
        $control = new TransferOrderNoBarcodeProceedController(new Inventory, new ProcessInventoryItems, new RecordItemHistory, new QugeoProcessor, new BranchInventory);
        $data = $control->__invoke($request, $transferOrder->fsto_uid);
    }
}
