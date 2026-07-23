<?php

namespace App\Schedulers;

use App\Models\{
    Branch,
    ProcessLock,
    TransferOrder
};
use Illuminate\Support\Facades\DB;
use App\Events\DelayedOrderActions as DelayedOrderEvent;

class CreateExpressConsignment
{
    public function __invoke()
    {
        try
        {
            $partner = config('expresspartners.default');
            $packages = TransferOrder::select('fsto_id', 'fsto_uid', 'fstr_id', 'fsto_ordertype',
            'fsto_source')->where([
                ['fsto_status', 10],
                ['fsto_hasShipmentCreated', 0]
            ])->with('order:order_id,order_order_id,order_method,delivery_rule_id,order_branch_id,delivery_rule_type')
            ->whereHas('order', function($q) {
                $q->where('order_method', 1);
                $q->whereIn('payment_mode', [2, 3, 5]);
                $q->where('status_id', 9);
            })->get();
            foreach($packages as $package)
            {
                $outs = NULL;
                switch ($package->order->delivery_rule_type)
                {
                    case 1:
                        $outs = $this->runDefault($partner, $package);
                        break;
                    case 3:
                        $outs = $this->runStoreRule($package);
                        break;
                }
            }
            ProcessLock::updateColData("BizAPI_CreateExpressConsignment", 0);
        }
        catch (\Exception $e)
        {
            info("CreateExpressConsignment ERROR => ".$e);
            ProcessLock::updateColData("BizAPI_CreateExpressConsignment", 0);
        }
    }

    private function runStoreRule($package)
    {
        $outs = false;
        $branchData = $branchData = Branch::where('br_ID', $package->order->order_branch_id)->first();
        if(@$branchData->settings)
        {
            $settings = @$branchData->settings->toArray();
            $packingData = array_values(
                array_filter($settings, function($item) {
                    return ($item['type'] == 2 && $item['tp_type'] == "status" && $item['tp_value'] == 1);
                })
            );
            $type = @$packingData[0]['tp_name'];
            if($type)
            {
                event(new DelayedOrderEvent(@$package->fstr_id, 4, 5));
                $shipping = config("expresspartners.{$type}.sClass");
                $shipper = new $shipping();

                $outs = $shipper->createNewConsignment($package);
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
        return $outs;
    }
    private function runDefault($partner = "", $package)
    {
        if(@$partner != "")
        {
            event(new DelayedOrderEvent(@$package->fstr_id, 4, 5));
            $shipping = config("expresspartners.{$partner}.sClass");
            $shipper = new $shipping();
            $outs = $shipper->createNewConsignment($package);
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
}
