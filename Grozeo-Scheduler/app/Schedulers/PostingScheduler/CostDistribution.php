<?php
namespace App\Schedulers\PostingScheduler;

use Illuminate\Support\Facades\DB;
use App\Models\Order;

class CostDistribution
{
    public function __construct() {}

    public function costDistribution($order_id, $finascopEventRefId, $storeGroupId)
    {
        try
        {
            $eventDetails = DB::table('finance_event_master')->where('event_ref_id', $finascopEventRefId)->first();

            $order = Order::where('order_id', $order_id)->first();
            if($order)
            {
                $saleType = ($order->storegroup_id == 0) ? 0 : 1;
                $deliveryType = ($order->order_method == 3) ? 0 : 1;
                $paymentType = ($order->payment_mode == 1) ? 1 : 0;

                $getAreaType = DB::select("SELECT t.id FROM finance_area_type t INNER JOIN  business_associate ba ON ba.baType=t.baType AND ba.baMode=t.baMode INNER JOIN area_entries a ON a.areaBusinessAssociate = ba.id INNER JOIN finascop_branch b ON b.areaId=a.id WHERE b.br_ID={$order->order_branch_id}");
                if(@$getAreaType[0]->id)
                {
                    $areaType =  $getAreaType[0]->id;
                }
                else
                {
                    $brDetails = DB::table('finascop_branch')->select('br_Lat','br_Lng')->where('br_ID', $order->order_branch_id)->first();
                    $areaIdByBranch = DB::select("SELECT id FROM (SELECT id, areaSpan, calcDistance('{$brDetails->br_Lat}', '{$brDetails->br_Lng}', areaLatitude, areaLongitude) AS distance FROM area_entries HAVING distance <= areaSpan ORDER BY distance LIMIT 1) AS area_entry");
                    $areaType = NULL;
                    if(@$areaIdByBranch[0]->id)
                    {
                        $getAreaType = DB::select("SELECT t.id FROM finance_area_type t INNER JOIN  business_associate ba ON ba.baType=t.baType AND ba.baMode=t.baMode INNER JOIN area_entries a ON a.areaBusinessAssociate = ba.id WHERE a.id={$areaIdByBranch[0]->id}");
                        $areaType = @$getAreaType[0]->id ? $getAreaType[0]->id : NULL;
                    }
                }

                $cdFunctions = DB::table('cost_distribution_function')->where([
                    ['event_master_id', $eventDetails->id],
                    ['sale_type_id', $saleType],
                    ['payment_type_id', $paymentType],
                    ['delivery_type_id', $deliveryType],
                    ['area_type_id', $areaType]
                ])->get();
                foreach($cdFunctions as $cdf)
                {
                    $posting = $this->costDistributionPosting($cdf, $order);

                    if(!empty($posting))
                    {
                        $this->saveCostDistributionValues($posting);
                    }
                }
            }
        }
        catch (\Exception $e)
        {
            info("COST DISTRIBUTION ERROR => ".$e->getMessage());
        }
    }
    private function costDistributionPosting($cdf, $order)
    {
        try
        {
            $outs = [];
            $getRuleList = DB::table('cost_distribution_rule_new')->where('cost_distribution_id', $cdf->id)->get();
            $sourceHead = DB::table('finance_calculation_heads')->where('id', $cdf->item_value_head_id)->first();
            $sourceVal = DB::table('finance_autoposting_values')->where('order_id', $order->order_id)->value($sourceHead->column_name);
            $totalVal = 0;
            $reserveName = '';
            foreach($getRuleList as $rule)
            {
                $ovHead = DB::table('finance_calculation_heads')->where('id', $rule->orderValueHead_id)->first();
                if($ovHead)
                {
                    $ovName = $ovHead->column_name;
                    if($rule->allocation > 0)
                    {
                        $allocatedAmount = ($rule->allocation / 100) * $sourceVal;
                        $allocatedAmount = floor($allocatedAmount * 100) / 100;
                        $totalVal += $allocatedAmount;
                        $outs[] = [
                            'target_column'     => $ovName,
                            'allocation_amount' => $allocatedAmount,
                            'order_id'          => $order->order_id
                        ];
                    }
                    else
                    {
                        $reserveName = $ovName;
                    }
                }
            }
            if($reserveName != '')
            {
                $balance = $sourceVal - $totalVal;
                $outs[] = [
                    'target_column'     => $reserveName,
                    'allocation_amount' => $balance,
                    'order_id'          => $order->order_id
                ];
            }
            return $outs;
        }
        catch (\Exception $e)
        {
            info("COST DISTRIBUTION POSTING ERROR => ".$e->getMessage());
            return [];
        }
    }
    private function saveCostDistributionValues($posting)
    {
        foreach($posting as $post)
        {
            DB::table('finance_autoposting_values')->where('order_id', $post['order_id'])->update([
                $post['target_column']  => $post['allocation_amount']
            ]);
        }
    }
}
