<?php

namespace App\Schedulers;

use App\Models\{
    Branch,
    ProcessLock,
    TransferOrder
};
use App\Factories\PartnerFactory;
use Illuminate\Support\Facades\DB;
use App\Events\DelayedOrderActions as DelayedOrderEvent;

class CreateShippingConsignment
{
    public function __invoke()
    {
        try
        {
            if(config('app.is_courier_enabled') == 1)
            {
                $country = config('app.operatingcountry');
                $partner = config('courierpartners.default');
                $shipping = config("courierpartners.{$partner}.sClass");
                $shipper = new $shipping();
                $outs = [];
                $packages = TransferOrder::select('fsto_id', 'fsto_uid', 'fstr_id', 'fsto_ordertype',
                'fsto_source')->where([
                    ['fsto_status', 10],
                    ['fsto_hasShipmentCreated', 0]
                ])
                ->where(DB::raw('TIMESTAMPDIFF(MINUTE,fsto_updateon,NOW())'), '>=', 10)
                ->with('order:order_id,order_order_id,order_method')
                ->whereHas('order', function($q) {
                    $q->where('order_method', 3);
                    $q->where('status_id', 9);
                })->get();
                foreach($packages as $package)
                {
                    event(new DelayedOrderEvent(@$package->fstr_id, 4, 5));
                    if($country == "INDIA")
                    {
                        $courier = PartnerFactory::make('courier', $country);
                        $outs = $courier->bookShipment($package->fsto_id);
                    }
                    else
                    {
                        $outs = $shipper->generateShipment($package->fsto_id);
                    }
                    if($outs)
                    {
                        TransferOrder::where('fsto_id', $package->fsto_id)->update(['fsto_hasShipmentCreated' => 2]);
                        event(new DelayedOrderEvent(@$package->fstr_id, 5));
                    }
                    else
                    {
                        event(new DelayedOrderEvent(@$package->fstr_id, 4));
                    }
                }
            }
            ProcessLock::updateColData("BizAPI_CreateShippingConsignment", 0);
        }
        catch (\Exception $e)
        {
            info("CreateShippingConsignment ERROR => ".$e->getMessage());
            ProcessLock::updateColData("BizAPI_CreateShippingConsignment", 0);
        }
    }
    
    
    
    public function __invoke1()
    {
        try
        {
            $partner = config('courierpartners.default');
            $shipping = config("courierpartners.{$partner}.sClass");
            $shipper = new $shipping();
            $outs = [];
            $packages = TransferOrder::select('fsto_id', 'fsto_uid', 'fstr_id', 'fsto_ordertype',
            'fsto_source')->where([
                ['fsto_status', 10],
                ['fsto_hasShipmentCreated', 0]
            ])->with('order:order_id,order_order_id,order_method')
            ->whereHas('order', function($q) {
                $q->where('order_method', 3);
            })->get();
            foreach($packages as $package)
            {
                $checkCourier = $this->checkCourierRuleAvailable($package);
                if($checkCourier)
                {
                    if(config('app.is_courier_enabled') == 1)
                    {
                        TransferOrder::where('fsto_id', $package->fsto_id)->update(['fsto_hasShipmentCreated' => 3]);
                    }
                }
                else
                {
                    TransferOrder::where('fsto_id', $package->fsto_id)->update(['fsto_hasShipmentCreated' => 2]);
                    $outs = $shipper->generateShipment($package->fsto_id);
                }
            }
            ProcessLock::updateColData("BizAPI_CreateShippingConsignment", 0);
        }
        catch (\Exception $e)
        {
            info("CreateShippingConsignment ERROR => ".$e->getMessage());
            ProcessLock::updateColData("BizAPI_CreateShippingConsignment", 0);
        }
    }

    private function checkCourierRuleAvailable($package)
    {
        if($package->fsto_ordertype == 1)
        {
            $getBranchData = Branch::where('br_ID', $package->fsto_source)->value('br_rdrIdCourier');
            if(@$getBranchData > 0)
            {
                return true;
            }
        }
        return false;
    }
}
