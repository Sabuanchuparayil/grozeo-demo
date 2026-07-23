<?php

namespace App\Schedulers;

use App\Models\{
    Branch,
    ProcessLock,
    TransferOrder,
    Packing\OrderPacking
};
use Illuminate\Support\Facades\DB;

class CreatePacking
{
    public function __invoke()
    {
        try
        {
            $packages = TransferOrder::select('fsto_id', 'fsto_uid', 'fstr_id', 'fsto_ordertype',
            'fsto_source')
            ->where('fsto_status', 23)
            ->with('order:order_id,order_order_id,order_branch_id')
            ->get();
            foreach($packages as $package)
            {
                $hasPack = OrderPacking::where('order_id', $package->order->order_id)
                    ->where('packing_status', '>=', 0)
                    ->exists();
                if(!$hasPack)
                {
                    $branchData = Branch::where('br_ID', $package->order->order_branch_id)->first();
                    if(@$branchData->settings)
                    {
                        $settings = @$branchData->settings->toArray();
                        $packingData = array_values(
                            array_filter($settings, function($item) {
                                return ($item['type'] == 1 && $item['tp_type'] == "status" && $item['tp_value'] == 1);
                            })
                        );
                        $type = @$packingData[0]['tp_name'];
                        if($type)
                        {
                            $packingClass = config("packingpartners.{$type}.sClass");
                            $packer = new $packingClass();

                            $packer->orderPacking($package->order->order_id);
                        }
                    }
                }
            }
            ProcessLock::updateColData("BizAPI_CreatePacking", 0);
        }
        catch (\Exception $e)
        {
            info("CreatePacking ERROR");info($e);
            ProcessLock::updateColData("BizAPI_CreatePacking", 0);
        }
    }
}
