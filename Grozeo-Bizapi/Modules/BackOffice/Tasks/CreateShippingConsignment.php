<?php

namespace BackOffice\Tasks;

use Illuminate\Support\Facades\DB;
use BackOffice\Models\TransferOrder;
use App\CourierPartners\Shipyaari\Shipyaari;
use App\CourierPartners\WorldOptions\WorldOptions;

class CreateShippingConsignment
{
    public function __invoke()
    {
        try{
        $shippingType = config('courierpartners.default');
        $wo = new WorldOptions;
        $sy = new Shipyaari;
        $outs = [];

        $packages = TransferOrder::where([
            ['fsto_status', 10],
            ['fsto_hasShipmentCreated', 0]
        ])->get();
        foreach($packages as $package)
        {
            if($shippingType == 'worldoptions')
            {
                $outs[] = $wo->generateShipment($package->fsto_id);
            }
            if($shippingType == 'shipyaari')
            {
                $outs[] = $sy->createConsignment($package->fsto_id);
            }
        }
        return response()->json($outs);
    }catch (\Exception $e)
    {
        info("create shippment ERROR => ".$e->getMessage());
    }
    }
}