<?php
namespace App\Schedulers\PostingScheduler;

use Illuminate\Support\Facades\DB;
use App\Models\{
    Order,
    FinanceAutopostingValues
};

class AutopostingCalculations
{
    public function __construct() {}

    public function addAutopostingDetails($order_id, $finascopEventRefId)
    {
        try
        {
            /* if($finascopEventRefId == "078025ad-38d7-11ee-9967-065723bafb24")
            {
                $autopostings = FinanceAutopostingValues::where('order_id', $order_id)->first();
                $order = Order::where('order_id', $order_id)->first();
                if($autopostings && $order)
                {
                    $updateValues = FinanceAutopostingValues::where('order_id', $order_id)->update([
                        'is_cancelled'          => '1',
                        'order_payment_mode'    => $order->payment_mode
                    ]);
                    $data = [];
                    if(($order->payment_mode == 1) || ($order->payment_mode == 4))
                    {
                        $data["ODC_TODC_POD_Refund60s"] = @$autopostings->OrderSubTotal_General + @$autopostings->OrderSubTotal_RestaurantSales;
                        $data["RetailItems_POD_Refund60s"] = @$autopostings->OrderSubTotal_General + @$autopostings->OrderSubTotal_RestaurantSales;
                        $data["SellerAccount_RetailItems_PODCancelled60s"] = $autopostings->OrderSubTotal_General + @$autopostings->OrderSubTotal_RestaurantSales;
                        $data["TSOPOD_Cancelled"] = $autopostings->OrderSubTotal_General + @$autopostings->OrderSubTotal_RestaurantSales;
                    }
                    else
                    {
                        $data["RetailItems_Refund60s"] = $autopostings->OrderSubTotal_General + @$autopostings->OrderSubTotal_RestaurantSales;
                        $data["ODC_TODC_Refund60s"] = $autopostings->ODCTotal + @$autopostings->TODCTotal;
                        $data["SellerAccount_RetailItems_Cancelled60s"] = $autopostings->OrderSubTotal_General + @$autopostings->OrderSubTotal_RestaurantSales;
                        $data["TenantSaleOrder_Cancelled"] = $autopostings->OrderSubTotal_General + @$autopostings->OrderSubTotal_RestaurantSales;
                    }
                    if(count($data) > 0)
                    {
                        $updateValues = FinanceAutopostingValues::where('order_id', $order_id)->update($data);
                    }
                }
            } */
        }
        catch (\Exception $e)
        {
            info("RESTAURANT VALUES ERROR => ".$e->getMessage());
        }
    }
}
