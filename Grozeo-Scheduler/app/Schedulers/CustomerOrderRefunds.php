<?php

namespace App\Schedulers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\{
    Order,
    ProcessLock,
    OrderRefunds
};
use App\Events\OrderHistory;
use App\Status\CustomerOrderStatus;

class CustomerOrderRefunds
{
    public function __invoke()
    {
        try
        {
            $refundData = OrderRefunds::where('status', '0')->with('order')->get();
            foreach ($refundData as $ref)
            {
                $order = $ref->order;
                $pg = $ref->payment_gateway;
                $amount = $ref->amount;
                if(($pg != "") && ($amount > 0))
                {
                    $pgClass = config("paymentgateway.{$pg}.class");
                    $pgObj = new $pgClass();
                    $ref->status = '3';
                    $ref->save();

                    $refundUpdate = $pgObj->cancellationRefunds($order->order_group_id, $amount);
                    if($refundUpdate && is_array($refundUpdate) && !empty($refundUpdate['id']))
                    {
                        $ref->status = '1';
                        $ref->payment_gateway_id = $refundUpdate["id"];
                        $ref->request = $refundUpdate["request"];
                        $ref->response = $refundUpdate["response"];
                        $ref->save();
                        Order::where('order_id', $order->order_id)->update([
                            'status_id' => CustomerOrderStatus::REFUND_COMPLETED
                        ]);
                        event(new OrderHistory($order->order_id, CustomerOrderStatus::REFUND_COMPLETED));
                    }
                    else
                    {
                        $ref->status = '0';
                        $ref->save();
                        Log::error("CustomerOrderRefunds: gateway refund failed for order_id {$order->order_id}, refund_id {$ref->id}");
                    }
                }
                else
                {
                    Order::where('order_id', $order->order_id)->update([
                        'status_id' => CustomerOrderStatus::REFUND_CANCELLED
                    ]);
                    event(new OrderHistory($order->order_id, CustomerOrderStatus::REFUND_CANCELLED));
                    $ref->status = '2';
                    $ref->save();
                }
            }
            ProcessLock::updateColData("BizAPI_CustomerOrderRefunds", 0);
        }
        catch (\Exception $e)
        {
            Log::error("CustomerOrderRefunds ERROR => " . $e->getMessage(), ['exception' => $e]);
            ProcessLock::updateColData("BizAPI_CustomerOrderRefunds", 0);
        }
    }
}
