<?php

namespace BackOffice\Tasks;

use Illuminate\Support\Facades\DB;
use App\CourierPartners\Shipyaari\Shipyaari;
use App\CourierPartners\WorldOptions\WorldOptions;

class ConsignmentTrackingUpdate
{
    public function __invoke()
    {
        try{
        $wo = new WorldOptions;
        $sy = new Shipyaari;
        $outs = [];
        $shipments = DB::table('shipping_consignment')->where('consignment_status', '<', 3)->get();
        foreach ($shipments as $ship)
        {
            if($ship->shipping_type == 'worldoptions')
            {
                $outs[] = $wo->completeTrackingStatus($ship->tracking_id);
            }
            if($ship->shipping_type == 'shipyaari')
            {
                $outs[] = $sy->completeTrackingStatus($ship->order_id);
            }
        }
        return response()->json($outs);
    }catch (\Exception $e)
    {
        info("ConsignmentTrackingUpdate ERROR => ".$e->getMessage());
    }
    }
}