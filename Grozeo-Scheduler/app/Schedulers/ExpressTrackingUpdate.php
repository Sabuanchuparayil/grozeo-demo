<?php

namespace App\Schedulers;

use Illuminate\Support\Facades\DB;
use App\Models\ProcessLock;

class ExpressTrackingUpdate
{
    public function __invoke()
    {
        try
        {
            $partner = config('expresspartners.default');
            if($partner != "")
            {
                $shipments = DB::table('shipping_consignment')->where([
                    ['order_method', 1],
                    ['consignment_status', '<', 3]
                ])->get();
                foreach ($shipments as $ship)
                {

                    $shipping = config("expresspartners.{$ship->shipping_type}.sClass");
                    $shipper = new $shipping();
                    $outs = $shipper->checkDeliveryStatus($ship);
                }
                ProcessLock::updateColData("BizAPI_ExpressTrackingUpdate", 0);
            }
        }
        catch (\Exception $e)
        {
            info("ExpressTrackingUpdate ERROR => ".$e->getMessage());
            ProcessLock::updateColData("BizAPI_ExpressTrackingUpdate", 0);
        }
    }
}
