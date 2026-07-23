<?php
namespace BackOffice\Tasks\PostingScheduler;

use Illuminate\Support\Facades\DB;
use App\Models\{
    Order,
    FinanceAutopostingValues
};

class RestaurantAutoposting
{
    public function __construct() {}

    public function addAutopostingDetails($order_id, $finascopEventRefId)
    {
        try
        {
            if($finascopEventRefId == config("event_master.orderPlacing"))
            {
                $order = Order::where('order_id', $order_id)->first();
                if($order)
                {
                    $data = [
                        'RetailSalePrice_Restaurant'    => 0,
                        'IGSTonRSP_Restaurant'          => 0,
                        'CGSTonRSP_Restaurant'          => 0,
                        'SGSTonRSP_Restaurant'          => 0,
                        'UTGSTonRSP_Restaurant'         => 0,
                        'CConRSP_Restaurant'            => 0,
                        'GSTonRSP_RestaurantTotal'      => 0,
                        'RetailSalePrice'               => 0,
                        'CConRSP_Final'                 => 0,
                        'IGSTonRSP_Final'               => 0,
                        'CGSTonRSP_Final'               => 0,
                        'SGSTonRSP_Final'               => 0,
                        'UTGSTonRSP_Final'              => 0,
                        'TCSIGST'                       => 0,
                        'TCSCGST'                       => 0,
                        'TCSSGST'                       => 0,
                        'TCSUTGST'                      => 0,
                        'TCSGSTTotal'                   => 0,
                    ];
                    $gstOthers = 0;
                    foreach ($order->orderItems as $item)
                    {
                        if(@$item->item->productCategory->hasRestaurantService == 1)
                        {
                            $data['RetailSalePrice_Restaurant'] += ($item->order_item_mrp_et - $item->order_item_seller_discount);
                            $data['IGSTonRSP_Restaurant'] += $item->order_item_igst;
                            $data['CGSTonRSP_Restaurant'] += $item->order_item_cgst;
                            $data['SGSTonRSP_Restaurant'] += $item->order_item_sgst;
                            $data['UTGSTonRSP_Restaurant'] += $item->order_item_ugst;
                            $data['CConRSP_Restaurant'] += $item->order_item_cess;
                            $data['GSTonRSP_RestaurantTotal'] += $item->order_item_gst;
                        }
                        else
                        {
                            $data['RetailSalePrice'] += ($item->order_item_mrp_et - $item->order_item_seller_discount);
                            $data['IGSTonRSP_Final'] += $item->order_item_igst;
                            $data['CGSTonRSP_Final'] += $item->order_item_cgst;
                            $data['SGSTonRSP_Final'] += $item->order_item_sgst;
                            $data['UTGSTonRSP_Final'] += $item->order_item_ugst;
                            $data['CConRSP_Final'] += $item->order_item_cess;
                            $data['TCSIGST'] += $item->order_item_tcs_igst;
                            $data['TCSCGST'] += $item->order_item_tcs_cgst;
                            $data['TCSSGST'] += $item->order_item_tcs_sgst;
                            $data['TCSUTGST'] += $item->order_item_tcs_utgst;
                            $data['TCSGSTTotal'] += $item->order_item_tcs_gst;
                            $gstOthers += $item->order_item_gst;

                        }
                    }
                    if($data['RetailSalePrice_Restaurant'] > 0)
                    {
                        $restTot = $data['RetailSalePrice_Restaurant'] + $data['GSTonRSP_RestaurantTotal'];
                        $roundRestTot = round($restTot);
                        $restTotRound = round(($restTot - $roundRestTot), 2);
                        $restTotRoundABS = abs($restTotRound);
                        if($restTotRound > 0)
                        {
                            $data['RoundDown_RestaurantSales'] = abs($restTotRound);
                            $data['RoundUp_RestaurantSales'] = 0;
                        }
                        if($restTotRound < 0)
                        {
                            $data['RoundUp_RestaurantSales'] = abs($restTotRound);
                            $data['RoundDown_RestaurantSales'] = 0;
                        }
                    }
                    if($data['RetailSalePrice'] > 0)
                    {
                        $otherTot = $data['RetailSalePrice'] + $gstOthers + $data['CConRSP_Final'];
                        $roundOtherTot = round($otherTot);
                        $otherTotRound = round(($otherTot - $roundOtherTot), 2);
                        $otherTotRoundABS = abs($otherTotRound);
                        if($otherTotRound > 0)
                        {
                            $data['RoundDown_General'] = abs($otherTotRound);
                            $data['RoundUp_General'] = 0;
                        }
                        if($otherTotRound < 0)
                        {
                            $data['RoundUp_General'] = abs($otherTotRound);
                            $data['RoundDown_General'] = 0;
                        }
                    }
                    $updateValues = FinanceAutopostingValues::where('order_id', $order_id)->update($data);
                }
            }
        }
        catch (\Exception $e)
        {
            info("RESTAURANT VALUES ERROR => ".$e->getMessage());
        }
    }
}