<?php

namespace App\Http\Repositories\Order;

use App\Models\Order;
use App\Models\OrderItem;
use BackOffice\Models\Item;
use BackOffice\Models\BranchInventory;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\PostingRepository;
use Illuminate\Http\Request;

class OrderRepository implements OrderRepositoryInterface
{
    protected $order;

    protected $orderItem;

    public function __construct(Order $order, OrderItem $orderItem)
    {
        $this->order = $order;
        $this->orderItem = $orderItem;
    }

    /**
     * Retrieve all items in the cart.
     *
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {
        return auth_user()->orders()
                ->select('order_order_id', 'order_customer_id', 'order_total_amount', 'order_status', 'order_confirm_date', 'order_cancel_date')
                ->with(['orderItems' => function($query){
                    $query->select('item_id', 'item_order_id', 'item_product_id', 'item_order_qty', 'item_price')
                        ->with('product:product_id,product_name');
                }])
                ->get();
    }

    /**
     * Get all order id of a user.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getOrderId()
    {
        return $this->order
                    ->select('order_order_id')
                    ->where('order_customer_id', auth_user()->cust_id)
                    ->get()
                    ->pluck('order_order_id');
    }
    public static function getKfc()
    {
        return (int) config('kfc.kfc_percentage') ?? 0;
    }
    public function nearestEvenDecimal($decimalValue)
    {
        $numArr = explode('.', $decimalValue);
        if (@$numArr[1]) {
            $num_length = strlen((string)$numArr[1]); //to cover .01, .001
            if ($num_length == 1) {
                $numArr[1] = $numArr[1] * 10;
            }
            $remainder = $numArr[1] % 2;
            if ($remainder == 0) {
                return $decimalValue;
            } else {
                return $decimalValue + 0.01;
            }
        } else {
            return $decimalValue;
        }
    }
    public function updateOrderItemsCoupon($items, $discountAmount, $coupon, $process = true)
    {
        $itemsArray = json_decode($items, true);
        $total_gst = $total_kfc = $total_after_coupon = $totalCess = 0;
        $total_seller_discount = $total_discount = $total_selling = 0;
        $item_discount_total = 0;
        $kfc = $this->getKfc();
        $totalItemMrps = array_sum(array_map(function ($itemOrder) {
            return $itemOrder['order_item_mrp'] * $itemOrder['item_order_qty'];
        }, $itemsArray));
        $totalItemSps = array_sum(array_map(function ($itemOrder) use ($kfc) {

            $branchInventory = BranchInventory::select("id", "stit_id", "branch_id", "taxValue", "cessValue")->where([
                ["branch_id", $itemOrder['order_branch_id']],
                ["stit_id", $itemOrder['item_product_id']]
            ])->first();
            // Fetch item details
            $itemmaster = $branchInventory->item;
        
            // Fetch tax values
            /*$taxValues = DB::table("hsn_value")->where("id", $itemmaster->taxValueId)->first();
            $tax_percentage = @$taxValues->hsnGst ?: 0;
            $itemcess = @$taxValues->hsnCess ?: 0;*/
            $tax_percentage = @$branchInventory->taxValue ?? 0;
            $itemcess = @$branchInventory->cessValue ?? 0;
        
            // Calculate tax value
            $tax_val = $tax_percentage + $kfc;
        
            // Calculate item sales price excluding taxes (SPEt)
            $itemSPEt = $itemOrder['item_sales_price'] * (1 - (($itemcess + $tax_val) / (100 + $tax_val + $itemcess)));
            $itemSPEt = floor($itemSPEt * 100) / 100;
        
            // Return the calculated value multiplied by quantity
            return $itemSPEt * $itemOrder['item_order_qty'];
        }, $itemsArray));
        
        foreach ($items as $item) {
            /*$itemmaster = Item::select("stit_ID", "taxValueId", "product_category", "stit_courierWt")->where("stit_ID", $item['item_product_id'])->with('productCategory')->first();
            $taxValues = DB::table("hsn_value")->where("id", $itemmaster->taxValueId)->first();
            $tax_percentage = @$taxValues->hsnGst ? $taxValues->hsnGst : 0;
            $itemcess = @$taxValues->hsnCess ? $taxValues->hsnCess : 0;*/
            $branchInventory = BranchInventory::select("id", "stit_id", "branch_id", "taxValue", "cessValue")->where([
                ["branch_id", $item['order_branch_id']],
                ["stit_id", $item['item_product_id']]
            ])->first();
            $itemmaster = $branchInventory->item;
            $tax_percentage = @$branchInventory->taxValue ?? 0;
            $itemcess = @$branchInventory->cessValue ?? 0;

            $tax_val = $tax_percentage + $kfc;

            $itemMrp = $item['order_item_mrp'] * $item['item_order_qty'];
            $itemMrpEt = $itemMrp * (1 - (($itemcess + $tax_val) / (100 + ($tax_val + $itemcess))));
            $itemMrpEt = floor($itemMrpEt * 100) / 100;
            if ($itemMrpEt > 0) {
                $itemMrpEt = $this->nearestEvenDecimal($itemMrpEt);
            }
            $itemSP = $item['item_sales_price'] * $item['item_order_qty'];
            $itemSPEt = $itemSP * (1 - (($itemcess + $tax_val) / (100 + ($tax_val + $itemcess))));
            $itemSPEt = floor($itemSPEt * 100) / 100;
            if ($itemSPEt > 0) {
                $itemSPEt = $this->nearestEvenDecimal($itemSPEt);
            }
            $item_sales_price = $item['item_sales_price'];
            //$seller_discount = round($itemMrpEt - $itemSPEt, 2);

            if ($process == true) {
                $coupondata["item_type"] = 1;
                $coupondata["item_type_offer"] = 1;
                $coupondata["item_coupon_code"] = $coupon->bom_offerCode;
                //$coupondata["item_discount"] = round($discountAmount, 2);
                $coupondata["bom_id"] = $coupon->bom_id;
                $orginal_sales_price = $item['item_sales_price'];
                $itemDiscountValue = round(($discountAmount * $itemSPEt) / $totalItemSps, 2);
                $coupondata["item_discount"] = $itemDiscountValue;
                $itemDiscountedSP = $itemSP - $itemDiscountValue;
                $item_sales_price = round($itemDiscountedSP/$item['item_order_qty'] , 2);

                $itemDiscountedSPEt = $itemSPEt - $itemDiscountValue;
                $itemDiscountedSPEt = floor($itemDiscountedSPEt * 100) / 100;
                if ($itemDiscountedSPEt > 0) {
                    $itemDiscountedSPEt = $this->nearestEvenDecimal($itemDiscountedSPEt);
                }
            } else {
                $coupondata["item_type"] = 0;
                $coupondata["item_type_offer"] = 0;
                $coupondata["item_coupon_code"] = '';
                $coupondata["item_discount"] = 0;
                $coupondata["bom_id"] = 0;
                $orginal_sales_price = $item['orginal_sales_price'];
                $item_sales_price = $item['orginal_sales_price'];
                $itemDiscountValue = 0;

                $itemDiscountedSP = $item['orginal_sales_price'] * $item['item_order_qty'];
                $itemDiscountedSPEt = $itemDiscountedSP * (1 - (($itemcess + $tax_val) / (100 + ($tax_val + $itemcess))));
                $itemDiscountedSPEt = floor($itemDiscountedSPEt * 100) / 100;
                if ($itemDiscountedSPEt > 0) {
                    $itemDiscountedSPEt = $this->nearestEvenDecimal($itemDiscountedSPEt);
                }
            }
            
            $seller_discount = round($itemMrpEt - $itemDiscountedSPEt, 2);
            $productCess = $itemDiscountedSPEt * $itemcess / 100;
            $productCess = floor($productCess * 100) / 100;
            if ($productCess > 0) {
                $productCess = $this->nearestEvenDecimal($productCess);
            }
            
            $new_price_tax = $itemDiscountedSP - ($itemDiscountedSPEt + $productCess);
            if ($new_price_tax > 0) {
                $new_price_tax = $this->nearestEvenDecimal($new_price_tax);
            }
            
            $partialtax = $new_price_tax / 2;
            $kfc_val = ($kfc > 0) ? ($new_price_tax * $kfc) / $tax_val : 0;
            $taxValue = ($kfc > 0) ? ($new_price_tax - $kfc_val) : $new_price_tax;

            $tcsVal = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TCS' limit 1");
            $partTcsVal = round($tcsVal[0]->cfg_Value / 2, 2);
            $parTtcsGst =  round($itemDiscountedSPEt * $partTcsVal / 100, 2);
            $tcsGst = $parTtcsGst * 2;

            $tcs_igst = ($item['order_item_tcs_igst'] > 0) ? $tcsGst : 0.00;
            $tcs_cgst =  ($item['order_item_tcs_cgst'] > 0) ? $parTtcsGst : 0.00;
            $tcs_sgst =  ($item['order_item_tcs_sgst'] > 0) ? $parTtcsGst : 0.00;
            $tcs_utgst = ($item['order_item_tcs_igst'] > 0) ? $parTtcsGst : 0.00;

            $itemcgst = ($item['order_item_cgst'] > 0) ? $partialtax : 0.00;
            $itemsgst = ($item['order_item_sgst'] > 0) ? $partialtax : 0.00;
            $itemutgst = ($item['order_item_ugst'] > 0) ? $partialtax : 0.00;
            $itemigst = ($item['order_item_igst'] > 0) ? $taxValue : 0.00;


            info('coupondatacoupondata');
            info(json_encode($coupondata));
            info('$itemSP');info($itemDiscountedSP);
            $amountAfterCoupon = $itemSPEt - $coupondata["item_discount"];
            $item_discount_total = $seller_discount + $coupondata["item_discount"];
            $total_discount += $item_discount_total;
            $total_seller_discount += $seller_discount;
            $total_after_coupon += $itemDiscountedSPEt;
            $total_gst += $new_price_tax;
            $total_kfc += $kfc_val;
            $totalCess += $productCess;
            $total_selling += $itemDiscountedSP;
            info('$total_selling');info($total_selling);
            $item->update([
                "item_type"                     => $coupondata["item_type"],
                "item_type_offer"               => $coupondata["item_type_offer"],
                "item_coupon_code"              => $coupondata["item_coupon_code"],
                "item_discount"                 => $coupondata["item_discount"],
                "bom_id"                        => $coupondata["bom_id"],
                'order_item_basket_price'       => $itemDiscountedSPEt,
                'order_item_basket_price_et'    => $itemDiscountedSPEt,
                'order_item_gst'                => ($itemcgst + $itemutgst + $itemsgst + $itemigst),
                'order_item_cgst'               => $itemcgst,
                'order_item_ugst'               => $itemutgst,
                'order_item_sgst'               => $itemsgst,
                'order_item_igst'               => $itemigst,
                'order_item_tcs_gst'            => ($tcs_igst + $tcs_cgst + $tcs_utgst + $tcs_sgst),
                'order_item_tcs_igst'           => $tcs_igst,
                'order_item_tcs_cgst'           => $tcs_cgst,
                'order_item_tcs_utgst'          => $tcs_utgst,
                'order_item_tcs_sgst'           => $tcs_sgst,
                'order_item_cess'               => $productCess,
                "orginal_sales_price"           => $orginal_sales_price,
                "order_item_seller_discount"    => $seller_discount,
                "order_item_total_mrp"          => $itemMrp,
                'item_price'                    => $itemDiscountedSP,
                'item_sales_price'              => $item_sales_price
            ]);
        }
        return compact("total_after_coupon", "total_gst", "total_kfc", "total_seller_discount", "totalCess","total_discount","total_selling");
    }

    public function updateOrderCoupon($orders, $updateItemOrders = array())
    {
        //$total = $total_discount + $orders->order_delivery_charge + $updateItemOrders["total_gst"] + $updateItemOrders["total_kfc"];
        $order_subtotal = $orders->order_mrp_et - $updateItemOrders["total_seller_discount"] + $orders->order_delivery_charge + $updateItemOrders["total_gst"] + $updateItemOrders["total_kfc"] + $updateItemOrders["totalCess"];
        $subtotal = $updateItemOrders["total_selling"];
        $isRoudoff = (env('ROUND_OFF', false) ? 'true' : 'false');
        $gross_total = $isRoudoff == 'true' ? round($order_subtotal, 0, PHP_ROUND_HALF_UP) ?? 0 : round($order_subtotal, 2);

        $total = $updateItemOrders["total_after_coupon"] + $orders->order_delivery_charge + $updateItemOrders["total_gst"] + $updateItemOrders["total_kfc"];

        $isRoudoff = (env('ROUND_OFF', false) ? 'true' : 'false');
        $order_roundoff = round($gross_total - $order_subtotal, 2) ?? 0;
        $order_nettotal = $total;

        $rounding_precision = $isRoudoff == 'true' ? 0 : 2;
        $round_total = round($total, $rounding_precision, PHP_ROUND_HALF_UP);

        $update = [
            //"order_discount_amount"     => round($coupon_amount, 2),
            //"order_discount_add_total"  => round($total_discount, 2),
            "total"                     => $round_total,
            "order_total_gst"           => $updateItemOrders["total_gst"],
            "order_kfc_amount"          => $updateItemOrders["total_kfc"],
            "order_total_amount"        => $updateItemOrders["total_after_coupon"],
            "subtotal"                  => $subtotal,
            "order_saved_amount"        => $updateItemOrders["total_seller_discount"],
            "order_seller_discount"     => -round($updateItemOrders["total_seller_discount"], 2),
            "order_subtotal"            => $order_subtotal,
            "order_nettotal"            => $order_nettotal,
            "order_grosstotal"          => $gross_total,
            "order_roundoff"            => $order_roundoff
        ];

        $orders->update($update);

        /* $defaultFinance = config('finance.default');
        $financeClass = config("finance.{$defaultFinance}");
        $financeObj = new $financeClass();

        $financeObj->financeAutopostings($orders, 'coupon'); */

        $postReq = new Request();
        $postReq->setMethod('POST');
        $postReq->request->add([
            'order_id' => $orders->order_id,
            'finascopEventRefId'     => '0780263b-38d7-11ee-9967-065723bafb24',
            'storegroup_id' => (@$orders->storegroup_id ? $orders->storegroup_id : 0)
        ]);

        (new PostingRepository)->finascopPosting($postReq);
        //(new PostingRepository)->finascopPosting($orders->order_id, '0780263b-38d7-11ee-9967-065723bafb24', (@$orders->storegroup_id ? $orders->storegroup_id : 0));

        return $round_total;
    }

    public function updateOrderItemsPacking($items, $discountAmount)
    {
        $itemsArray = json_decode($items, true);
        $total_gst = $total_kfc = $total_after_coupon = $totalCess = $totalMrpEt = $totalMrp = $totalsellingPrice = 0 ;
        $total_seller_discount = 0;
        $item_discount_total = 0;
        $sellingPrice = 0;
        $kfc = $this->getKfc();
        $totalItemMrps = array_sum(array_map(function ($itemOrder) {
            $qty = $itemOrder['item_order_qty_scanned'] > 0 ? $itemOrder['item_order_qty_scanned'] : $itemOrder['item_order_qty'];
            return $itemOrder['order_item_mrp'] * $qty;
        }, $itemsArray));
        foreach ($items as $item) {
            /*$itemmaster = Item::select("stit_ID", "taxValueId", "product_category", "stit_courierWt")->where("stit_ID", $item['item_product_id'])->with('productCategory')->first();
            $taxValues = DB::table("hsn_value")->where("id", $itemmaster->taxValueId)->first();
            $tax_percentage = @$taxValues->hsnGst ? $taxValues->hsnGst : 0;
            $itemcess = @$taxValues->hsnCess ? $taxValues->hsnCess : 0;*/
            $branchInventory = BranchInventory::select("id", "stit_id", "branch_id", "taxValue", "cessValue")->where([
                ["branch_id", $item['order_branch_id']],
                ["stit_id", $item['item_product_id']]
            ])->first();
            $itemmaster = $branchInventory->item;
            $tax_percentage = @$branchInventory->taxValue ?? 0;
            $itemcess = @$branchInventory->cessValue ?? 0;
            $tax_val = $tax_percentage + $kfc;

            $qty = $item['item_order_qty_scanned'] > 0 ? $item['item_order_qty_scanned'] : $item['item_order_qty'];
            $itemMrp = $item['order_item_mrp'] * $qty;
            $itemMrpEt = $itemMrp * (1 - (($itemcess + $tax_val) / (100 + ($tax_val + $itemcess))));
            $itemMrpEt = floor($itemMrpEt * 100) / 100;
            if ($itemMrpEt > 0) {
                $itemMrpEt = $this->nearestEvenDecimal($itemMrpEt);
            }
            

            $orginal_sales_price = $item['orginal_sales_price'];
            $item_sales_price = $item['item_sales_price'];
            $itemDiscountValue = 0;

            $itemDiscountedSP = $item['item_sales_price'] * $qty;
            if ($itemDiscountedSP > 0) {
                $sellingPrice = $this->nearestEvenDecimal($itemDiscountedSP);
            }
            $itemDiscountedSPEt = $itemDiscountedSP * (1 - (($itemcess + $tax_val) / (100 + ($tax_val + $itemcess))));
            $itemDiscountedSPEt = floor($itemDiscountedSPEt * 100) / 100;
            if ($itemDiscountedSPEt > 0) {
                $itemDiscountedSPEt = $this->nearestEvenDecimal($itemDiscountedSPEt);
            }
            $seller_discount = round($itemMrpEt - $itemDiscountedSPEt, 2);
            
            $productCess = $itemDiscountedSPEt * $itemcess / 100;
            $productCess = floor($productCess * 100) / 100;
            if ($productCess > 0) {
                $productCess = $this->nearestEvenDecimal($productCess);
            }
            
            $new_price_tax = $itemDiscountedSP - ($itemDiscountedSPEt + $productCess);
            if ($new_price_tax > 0) {
                $new_price_tax = $this->nearestEvenDecimal($new_price_tax);
            }
            
            $partialtax = $new_price_tax / 2;
            $kfc_val = ($kfc > 0) ? ($new_price_tax * $kfc) / $tax_val : 0;
            $taxValue = ($kfc > 0) ? ($new_price_tax - $kfc_val) : $new_price_tax;

            $tcsVal = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TCS' limit 1");
            $partTcsVal = round($tcsVal[0]->cfg_Value / 2, 2);
            $parTtcsGst =  round($itemDiscountedSPEt * $partTcsVal / 100, 2);
            $tcsGst = $parTtcsGst * 2;

            $tcs_igst = ($item['order_item_tcs_igst'] > 0) ? $tcsGst : 0.00;
            $tcs_cgst =  ($item['order_item_tcs_cgst'] > 0) ? $parTtcsGst : 0.00;
            $tcs_sgst =  ($item['order_item_tcs_sgst'] > 0) ? $parTtcsGst : 0.00;
            $tcs_utgst = ($item['order_item_tcs_igst'] > 0) ? $parTtcsGst : 0.00;

            $itemcgst = ($item['order_item_cgst'] > 0) ? $partialtax : 0.00;
            $itemsgst = ($item['order_item_sgst'] > 0) ? $partialtax : 0.00;
            $itemutgst = ($item['order_item_ugst'] > 0) ? $partialtax : 0.00;
            $itemigst = ($item['order_item_igst'] > 0) ? $taxValue : 0.00;

            $totalsellingPrice += $sellingPrice;
            $total_seller_discount += $seller_discount;
            $total_after_coupon += $itemDiscountedSPEt;
            $total_gst += $new_price_tax;
            $total_kfc += $kfc_val;
            $totalCess += $productCess;
            $totalMrpEt += $itemMrpEt;
            $totalMrp += $itemMrp;
            $item->update([         
                'order_item_mrp_et'             => $itemMrpEt,
                'order_item_basket_price'       => $itemDiscountedSPEt,
                'order_item_basket_price_et'    => $itemDiscountedSPEt,
                'order_item_seller_discount'    => $seller_discount,
                'order_item_gst'                => ($itemcgst + $itemutgst + $itemsgst + $itemigst),
                'order_item_cgst'               => $itemcgst,
                'order_item_ugst'               => $itemutgst,
                'order_item_sgst'               => $itemsgst,
                'order_item_igst'               => $itemigst,
                'order_item_tcs_gst'            => ($tcs_igst + $tcs_cgst + $tcs_utgst + $tcs_sgst),
                'order_item_tcs_igst'           => $tcs_igst,
                'order_item_tcs_cgst'           => $tcs_cgst,
                'order_item_tcs_utgst'          => $tcs_utgst,
                'order_item_tcs_sgst'           => $tcs_sgst,
                'order_item_cess'               => $productCess,
                "orginal_sales_price"           => $orginal_sales_price,
                "order_item_seller_discount"    => $seller_discount,
                "order_item_total_mrp"          => $itemMrp,
                'item_price'                    => $itemDiscountedSP,
                'item_sales_price'              => $item_sales_price
            ]);
        }
        return compact("total_after_coupon", "total_gst", "total_kfc", "total_seller_discount", "totalCess","totalMrpEt","totalMrp","totalsellingPrice");
    }

    public function updateOrderPacking($orders, $updateItemOrders = array())
    {
        //$total = $total_discount + $orders->order_delivery_charge + $updateItemOrders["total_gst"] + $updateItemOrders["total_kfc"];
        $order_subtotal = $updateItemOrders["totalMrpEt"] - $updateItemOrders["total_seller_discount"] + $orders->order_delivery_charge + $updateItemOrders["total_gst"] + $updateItemOrders["total_kfc"] + $updateItemOrders["totalCess"];

        $isRoudoff = (env('ROUND_OFF', false) ? 'true' : 'false');
        $gross_total = $isRoudoff == 'true' ? round($order_subtotal, 0, PHP_ROUND_HALF_UP) ?? 0 : round($order_subtotal, 2);

        $total = $updateItemOrders["total_after_coupon"] + $orders->order_delivery_charge + $updateItemOrders["total_gst"] + $updateItemOrders["total_kfc"];

        $isRoudoff = (env('ROUND_OFF', false) ? 'true' : 'false');
        $order_roundoff = round($gross_total - $order_subtotal, 2) ?? 0;
        $order_nettotal = $total;
        $rounding_precision = $isRoudoff == 'true' ? 0 : 2;
        $round_total = round($total, $rounding_precision, PHP_ROUND_HALF_UP);
        $update = [
            "order_mrp"                 => $updateItemOrders["totalMrp"],
            "order_mrp_et"              => $updateItemOrders["totalMrpEt"],
            "total"                     => $round_total,
            "order_total_gst"           => $updateItemOrders["total_gst"],
            "order_kfc_amount"          => $updateItemOrders["total_kfc"],
            "order_total_amount"        => $updateItemOrders["total_after_coupon"],
            "subtotal"                  => $updateItemOrders["totalsellingPrice"],
            "order_saved_amount"        => $updateItemOrders["total_seller_discount"],
            "order_seller_discount"     => -round($updateItemOrders["total_seller_discount"], 2),
            "order_subtotal"            => $order_subtotal,
            "order_nettotal"            => $order_nettotal,
            "order_grosstotal"          => $gross_total,
            "order_roundoff"            => $order_roundoff
        ];

        $orders->update($update);
        return $round_total;
    }
}
