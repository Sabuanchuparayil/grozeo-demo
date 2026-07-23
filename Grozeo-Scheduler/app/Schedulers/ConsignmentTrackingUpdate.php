<?php

namespace App\Schedulers;

use App\Factories\PartnerFactory;
use Illuminate\Support\Facades\DB;
use App\Models\ProcessLock;

class ConsignmentTrackingUpdate
{
    public function __invoke()
    {
        try
        {
            $country = config('app.operatingcountry');
            $partner = config('courierpartners.default');
            if($partner)
            {
                $shipping = config("courierpartners.{$partner}.sClass");
                $shipper = new $shipping();
                $shipments = DB::table('shipping_consignment')->where([
                    ['order_method', 3],
                    ['consignment_status', '<', 3]
                ])->get();
                foreach ($shipments as $ship)
                {
                    if($country == "INDIA")
                    {
                        $courier = PartnerFactory::make('courier', $country);
                        $outs = $courier->trackShipment($ship->order_id);
                    }
                    else
                    {
                        $outs = $shipper->completeTrackingStatus($ship);
                    }
                }
                ProcessLock::updateColData("BizAPI_ConsignmentTrackingUpdate", 0);
            }
        }
        catch (\Exception $e)
        {
            info("ConsignmentTrackingUpdate ERROR => ".$e->getMessage());
            ProcessLock::updateColData("BizAPI_ConsignmentTrackingUpdate", 0);
        }
    }
}
