<?php

namespace App\Http\Repositories\Item;

use App\Models\Branch;
use BackOffice\Models\BranchInventory;
use Illuminate\Support\Facades\DB;

class Price {

    public static function findPrice(array $stit_id, int $branch_id, $branchtypeid = 1) {
        return (new static)->getPrice($stit_id, $branch_id, $branchtypeid);
    }

    private function getPrice($stit_id, $branch_id, $branchtypeid = 1) {
        
        if ($branchtypeid == 2) {
            $cpitem_prices = DB::select('SELECT fcpod_itemid AS stit_id, fcpod_leastSKUmrp AS mrp, fcpod_customerRateCouDel AS selling_price, fcpod_leastSKUmrp AS fpod_leastSKUmrp, fcpod_customerRateHmDel AS fpod_customerRateHmDel, -1 AS fpod_customerRatePikup, fcpod_customerRateCouDel AS fpod_customerRateCouDel FROM finascop_contractpo_products WHERE fcpod_vendorid=' . $branch_id); 
            //$cpitem_prices =  $cpitem_prices->toArray();
            return [
                "mrp" => array_column($cpitem_prices, "mrp", "stit_id"),
                "selling_price" => array_column($cpitem_prices, "selling_price", "stit_id"),
                "fpod_leastSKUmrp" => array_column($cpitem_prices, "fpod_leastSKUmrp", "stit_id"),
                "fpod_customerRateHmDel" => array_column($cpitem_prices, "fpod_customerRateHmDel", "stit_id"),
                "fpod_customerRatePikup" => array_column($cpitem_prices, "fpod_customerRatePikup", "stit_id"),
                "fpod_customerRateCouDel" => array_column($cpitem_prices, "fpod_customerRateCouDel", "stit_id"),
                "issponsered" => 0,
                "branch_storegroup" => 0,
            ];
        } else {
            // Join finascop_branch for enable satellite branch pricing
            $item_prices = BranchInventory::join('finascop_branch as b', function($join) { 
	            $join->on('b.br_ID', '=', DB::raw("finascop_stock_branch_inventory.branch_id or (b.br_type = 1 AND b.br_typeParent = finascop_stock_branch_inventory.branch_id)"));
            })->whereIn('stit_id', $stit_id)
                    ->where('b.br_ID', $branch_id)
                    ->select('stit_id', 'mrp', 'issponsered', 'selling_price', 'fpod_leastSKUmrp', 'fpod_customerRateHmDel', 'fpod_customerRatePikup', 'fpod_customerRateCouDel', 'taxValue');
            $item_prices = $item_prices->get()->toArray();
            $checkBranchStoregroup = Branch::where('br_ID', $branch_id)->first();
            return [
                "mrp" => array_column($item_prices, "mrp", "stit_id"),
                "selling_price" => array_column($item_prices, "selling_price", "stit_id"),
                "fpod_leastSKUmrp" => array_column($item_prices, "fpod_leastSKUmrp", "stit_id"),
                "fpod_customerRateHmDel" => array_column($item_prices, "fpod_customerRateHmDel", "stit_id"),
                "fpod_customerRatePikup" => array_column($item_prices, "fpod_customerRatePikup", "stit_id"),
                "fpod_customerRateCouDel" => array_column($item_prices, "fpod_customerRateCouDel", "stit_id"),
                "branch_type_id" => array_column($item_prices, "branch_type_id", "stit_id"),
                "storegroup_id" => array_column($item_prices, "storegroup_id", "stit_id"),
                "issponsered" => array_column($item_prices, "issponsered", "stit_id"),
                "branch_storegroup" => @$checkBranchStoregroup->br_storeGroup,
                "taxValue"  => array_column($item_prices, "taxValue", "stit_id")
            ];
        }
    }

    public static function findPriceFromCart($cart) {
        return (new static)->getPriceFromCart($cart);
    }

    private function getPriceFromCart($cart) {
            return [
                "mrp" => array_column($cart, "cart_price", "cart_product_id"),
                "selling_price" => array_column($cart, "cart_sales_price", "cart_product_id"),
                "fpod_leastSKUmrp" => array_column($cart, "fpod_leastSKUmrp", "cart_product_id"),
                "fpod_customerRateHmDel" => array_column($cart, "fpod_customerRateHmDel", "cart_product_id"),
                "fpod_customerRatePikup" => array_column($cart, "fpod_customerRatePikup", "cart_product_id"),
                "fpod_customerRateCouDel" => array_column($cart, "fpod_customerRateCouDel", "cart_product_id"),
                "branch_type_id" => array_column($cart, "branch_type_id", "cart_product_id"),
                "storegroup_id" => array_column($cart, "storegroup_id", "cart_product_id"),
                "cart_branch_id" => array_column($cart, "cart_branch_id", "cart_product_id"),
            ];
    }


}
