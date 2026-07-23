<?php

namespace App\Schedulers\Supports;

use Illuminate\Support\Facades\DB;
use App\Models\Supports\{
    OutboundJobs,
    OrderOutboundJobs
};
use App\Models\ProcessLock;
use App\Helpers\HttpCurlCalls;


class PackingDelayManualCalls
{
    public function __invoke()
    {
        try {
            $ivrCallData = OrderOutboundJobs::where('status', '2')
                ->get();
            foreach ($ivrCallData as $od) {
                $jobscompleted = DB::table('finascop_stock_transfer_order')
                ->join('retaline_customer_order', 'retaline_customer_order.order_id', '=', 'finascop_stock_transfer_order.fstr_id')
                    ->where("finascop_stock_transfer_order.fsto_status", "<", 9)
                    ->where("finascop_stock_transfer_order.fsto_ordertype", "=", 1)
                    ->where('retaline_customer_order.order_order_id', $od->orderRefrenceId)
                    ->count();
                if ($jobscompleted == 0) {
                    OrderOutboundJobs::where('id', $od->id)
                        ->update(['status' => 3,'completedOn' =>now()]);
                } else {
                    $existingJob = OutboundJobs::where([
                        ['orderRefrenceId', $od->orderRefrenceId],
                        ['eventId', 4],
                    ])->exists();
                    if (!$existingJob) {
                        OutboundJobs::create([
                            'eventId'       => 4,
                            'orderRefrenceId' => $od->orderRefrenceId,
                            'jobTitle'      => $od->jobTitle,
                            'calleeId'      => $od->calleeId,
                            'calleeName'    => $od->calleeName,
                            'calleeMobile'  => $od->calleeMobile,
                            'calleeType'    => 2,
                            'eventRank'     => 1,
                            'status'        => 1
                        ]);
                    }

                    OrderOutboundJobs::where('id', $od->id)
                        ->update(['status' => 3,'completedOn' =>now()]);
                }
            }

            ProcessLock::updateColData("BizAPI_PackingDelayManualCalls", 0);
        } catch (\Exception $e) {
            info("PackingDelayManualCalls ERROR => " . $e->getMessage());
            ProcessLock::updateColData("BizAPI_PackingDelayManualCalls", 1);
        }
    }
}
