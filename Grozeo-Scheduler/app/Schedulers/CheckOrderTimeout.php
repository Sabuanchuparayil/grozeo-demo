<?php
namespace App\Schedulers;

use App\Models\{
    Order,
    ProcessLock,
    OrderHistory
};
use Illuminate\Support\Facades\DB;
use App\Status\CustomerOrderStatus;

class CheckOrderTimeout
{
    public function __invoke()
    {
        try
        {
            $timer = config('app.order_timeout_timer') ?? 360;
            DB::transaction(function () use ($timer) {
                $timedOutOrders = Order::select('order_id')
                    ->where([
                        ['status_id', 1],
                        [DB::raw('TIMESTAMPDIFF(SECOND, order_payment_initiate_time, NOW())'), '>', $timer]
                    ])
                    ->whereIn('payment_mode', [2, 5])
                    ->lockForUpdate()
                    ->get();

                $orderIds = $timedOutOrders->pluck('order_id')->toArray();
                if (empty($orderIds)) {
                    return;
                }

                OrderHistory::insertUsing(
                    [
                        "order_id",
                        "order_action",
                        "order_status",
                        "created_at",
                        "updated_at"
                    ],
                    Order::select(
                        'order_id',
                        DB::raw('"Payment timed out" as order_action'),
                        DB::raw(CustomerOrderStatus::PAYMENT_TIMEDOUT.' as order_status'),
                        DB::raw('NOW() as created_at'),
                        DB::raw('NOW() as updated_at')
                    )
                    ->whereIn('order_id', $orderIds)
                    ->where('status_id', 1)
                );

                Order::whereIn('order_id', $orderIds)
                    ->where('status_id', 1)
                    ->update([
                        'status_id'                             => CustomerOrderStatus::PAYMENT_TIMEDOUT,
                        'updated_at'                            => now()
                    ]);
            });
            ProcessLock::updateColData("BizAPI_CheckOrderTimeout", 1);
        }
        catch (\Exception $e)
        {
            info("CheckOrderTimeout SCHEDULER => {$e->getMessage()}");
            info($e);
            ProcessLock::updateColData("BizAPI_CheckOrderTimeout", 0);
        }
    }
}
