<?php

namespace App\Modules;

use BackOffice\Models\Item;
use App\Models\Order;
use App\Models\StockItemMaster;
use App\Modules\DetermineStates;
use App\Http\Repositories\Item\Stock;
use BackOffice\Models\BranchInventory;
use Illuminate\Support\Facades\Log;
use App\Http\Repositories\Item\Price;
use Illuminate\Support\Facades\DB;
use App\Models\CPOItems;
use BackOffice\Models\Branch;
use App\Models\DeliveryRules;
use App\Models\DeliveryInfo;
use App\Models\Vendor;
use BackOffice\Actions\Inventory\QugeoProcessor;

use App\CourierPartners\CourierPartnerSection;

class PriceCalculation
{

    private $inventory;
    private $item_master;
    private $stock;
    private $cpoItems;
    private $partner = '';

    public function __construct()
    {
        $this->inventory = new BranchInventory;
        $this->item_master = new StockItemMaster;
        $this->stock = new Stock;
        $this->cpoItems = new CPOItems;

        $this->courierPartner = new CourierPartnerSection;
    }

    public static function calculate(array $cart, int $branch_id, array $request, $branchtypeid = 1, $isCart = 0)
    {
        return (new static)->get($cart, $branch_id, $request, $branchtypeid, $isCart);
    }

    private function get($cart, $branch_id, $request, $branchtypeid = 1, $isCart)
    {
        $storegroupid = getHeaderStoreGroup();
        $basket_price = $total_tax = $total_seller_discount = $cart_total = 0;
        $total_selling = $total_kfc = $total_mrpet = 0;
        $total_mrp = $total_cgst = $total_sgst = $total_igst = $total_utgst = $totalSalesMargin = $totalLandingCost = $totalCess = 0;
        $product_qty = array_column($cart, 'cart_order_qty', 'cart_product_id');
        $product_id = array_column($cart, 'cart_product_id');
        $weightSum = 0;

        $productStoreGroup = array_column($cart, 'storegroup_id');

        $stock = $this->stock->getStock($product_id, $branch_id, $branchtypeid);
        $priceInventory = Price::findPriceFromCart($cart); //findPrice($product_id, $branch_id, $branchtypeid);
        //$price = $this->getPrice($product_id, $branch_id, $branchtypeid)->toArray();
        $tax = $this->getTax($product_id)->toArray();
        $taxes = array_column($tax, 'stit_GST', 'stit_ID');
        $hsnIds = array_column($tax, 'stit_hsnId', 'stit_ID');

        $cosnos = $this->getcosnos($product_id)->toArray();
        $cosnoses = array_column($cosnos, 'cos_nos', 'stit_ID');

        $prices = $priceInventory['selling_price']; //array_column($price, 'selling_price', 'stit_id');
        $mrp = $priceInventory['mrp']; //array_column($price, 'mrp', 'stit_id');

        if ($request['order_method'] == 2) {
            $stocks = array_filter($stock);
            $prices = array_intersect_key($prices, $stocks);
        }
        //  shipping_method
        // shipping_mehtod
        $order_method = $request['order_method'];
        $kfc = $this->getKfc($branch_id);

        $cart_customer_id = array_column($cart, 'cart_customer_id');
        $intraState = (@$cart_customer_id[0]) ? ($this->checkIntraState($cart_customer_id[0], $branch_id)) : 0;
        $isUTGT = $this->isUTGSTApplicable($branch_id);



        $itemDetails = [];
        $x = 0;
        $hasRestService = 0;
        foreach ($prices as $key => $rs) {

            // $branchInventory = DB::select("SELECT selling_price,issponsered,fpod_poLandingCostleastSKU,discount_selling_price FROM finascop_stock_branch_inventory bi inner join finascop_branch b on (b.br_ID=bi.branch_id OR (b.br_type = 1 AND b.br_typeParent = bi.branch_id)) WHERE b.br_ID = {$branch_id} AND stit_id = {$product_id[$x]} limit 1");

            $branchInventory = BranchInventory::from("finascop_stock_branch_inventory as fsi")
            ->select("selling_price", "issponsered", "fpod_poLandingCostleastSKU", "discount_selling_price", "branch_id", "stit_id", "hsnCode", "taxValue", "cessValue")
            ->join("finascop_branch as fb", function ($join) {
                $join->on("fsi.branch_id", "fb.br_ID")
                ->orOn(function ($q) {
                    $q->on('fsi.branch_id', 'fb.br_typeParent')->where('fb.br_type', 1);
                });
            })->with("item")
            ->where([
                ["branch_id", $branch_id],
                ["stit_id", $product_id[$x]]
            ])->first();

            $itemmaster = $branchInventory->item;//Item::select("stit_ID", "taxValueId", "product_category", "stit_courierWt")->where("stit_ID", $product_id[$x])->with('productCategory')->first();
            // $taxValues = DB::table("hsn_value")->where("id", $itemmaster->taxValueId)->first();
            $currentQty = (@$product_qty[$product_id[$x]]) ?? 1;
            $weightSum += (@$itemmaster->stit_courierWt) ? @$itemmaster->stit_courierWt * $currentQty : 1;
            $hasRestService = @$itemmaster->productCategory->hasRestaurantService ?? 0;

            // $tax_percentage = array_key_exists($key, $taxes) ? $taxes[$key] : 0;
            // $tax_percentage = @$taxValues->hsnGst ? $taxValues->hsnGst : 0;
            $tax_percentage = @$branchInventory->taxValue;
            $hsnId = array_key_exists($key, $hsnIds) ? $hsnIds[$key] : 0;
            /* $cessVal = DB::select("SELECT cess FROM finascop_hsn WHERE hsn_id = {$hsnId}");
            $itemcess = $cessVal ? $cessVal[0]->cess : 0; */
            // $itemcess = @$taxValues->hsnCess ? $taxValues->hsnCess : 0;
            $itemcess = @$branchInventory->cessValue;
            $tax_val = $tax_percentage + $kfc;
            $count = array_key_exists($key, $product_qty) ? $product_qty[$key] : 1;
            $cosnoses[$key] = ($cosnoses[$key] > 0) ? $cosnoses[$key] : 1;
            $mrpes = array_key_exists($key, $priceInventory['fpod_leastSKUmrp']) ? $priceInventory['fpod_leastSKUmrp'][$key] * $cosnoses[$key] : 0;
            //  $mrpes=round($mrpes,2);
            //  $order_method= \Session::get('order_method');
            //   $order_method= 1;
            /*
              if($order_method==1){
              $shipping_mehtod= ($branchtypeid == 3 ? 1 : 2);//isset($request['shipping_method'])?$request['shipping_method']:2;

              if($shipping_mehtod==1){
              $selling_price = array_key_exists($key, $priceInventory['fpod_customerRateHmDel']) ? $priceInventory['fpod_customerRateHmDel'][$key] *$cosnoses[$key]: 0;
              }else{
              $selling_price = array_key_exists($key, $priceInventory['fpod_customerRateCouDel']) ? $priceInventory['fpod_customerRateCouDel'][$key] *$cosnoses[$key]: 0;

              }
              }else{
             */
            $branch_type_id = array_key_exists($key, $priceInventory['branch_type_id']) ? $priceInventory['branch_type_id'][$key] : 1;
            //$itemstoregroup_id = array_key_exists($key, $priceInventory['storegroup_id']) ? $priceInventory['storegroup_id'][$key] : -1;
            $branch = Branch::where('br_ID', $priceInventory['cart_branch_id'])->first();
            $itemstoregroup_id = $branch->br_storeGroup;

            //$sellingpriceField = ($storegroupid > 0 && $itemstoregroup_id == $storegroupid ? 'selling_price' : 'CASE retaline_cart.branch_type_id WHEN 2 THEN cpo.fcpod_customerRateCouDel WHEN 1 THEN br.fpod_customerRateCouDel ELSE IFNULL( br.fpod_customerRateHmDel, 0 ) END');
            $sellingpriceField = ($storegroupid > 0 && $itemstoregroup_id == $storegroupid ? 'selling_price' : (@$branchInventory->issponsered != 1 ? 'selling_price' : ($branch_type_id == 3 ? 'fpod_customerRateHmDel' : 'fpod_customerRateCouDel')));

            $selling_price = array_key_exists($key, $priceInventory[$sellingpriceField]) ? $priceInventory[$sellingpriceField][$key] * $cosnoses[$key] : 0;
            /*
              }
             */
            // $selling_price=round($selling_price,2);

            $rs = $selling_price;

            $itemSalesMargin = $itemSalesMarginet = 0;
            $landingCost =  0;
            // calculate item sales margin if its sponsered sales
            if ($branchInventory && ($storegroupid > 0 && $itemstoregroup_id != $storegroupid)) {
                $itemSalesMargin = $selling_price - $branchInventory[0]->discount_selling_price;
                $itemSalesMarginet = ($itemSalesMargin * 100) / (100 + $tax_val);
                $landingCost =  $branchInventory[0]->fpod_poLandingCostleastSKU;
            }
            // If it is grozeo then apply the grozeo margin, expected from finance team to provde the margine distribution
            // It is on hold for now.


            //  $mrpes = array_key_exists($key, $mrp) ? $mrp[$key] : 0;
            $price_tax = ($tax_val * $rs * $count) / 100;
            //$mrpeset = round(($mrpes * $count * 100) / (100 + $tax_val),2);
            $mrpeset = $mrpes * $count * (1 - (($itemcess + $tax_val) / (100 + ($tax_val + $itemcess))));
            $mrpeset = floor($mrpeset * 100) / 100;
            if ($mrpeset > 0 && $tax_val > 0) {
                $mrpeset = $this->nearestEvenDecimal($mrpeset);
            }
            $basket_priceet = $rs * $count * (1 - (($itemcess + $tax_val) / (100 + ($tax_val + $itemcess))));
            $basket_priceet = floor($basket_priceet * 100) / 100;
            if ($basket_priceet > 0 && $tax_val > 0) {
                $basket_priceet = $this->nearestEvenDecimal($basket_priceet);
            }
            //$basket_price += ($rs * $count * 100) / (100 + $tax_val);
            $basket_price += $basket_priceet;
            $seller_discount = round($mrpeset - $basket_priceet, 2);
            //$basket_price += ($rs * $count) - $price_tax;
            $sellingPrice = $rs * $count;
			if ($sellingPrice > 0) {
                $sellingPrice = $this->nearestEvenDecimal($sellingPrice);
            }
            $total_selling += $sellingPrice;
            $total_mrp += $mrpes * $count;
            $total_mrpet += $mrpeset;
            $total_seller_discount += $seller_discount;
            $tax_val_half = round($tax_val / 2, 2);
            //$partialtax = round((($mrpeset * $tax_val_half) / 100),2);
            //$partialtax = round((($basket_priceet * $tax_val_half) / 100),2);
            //$new_price_tax = $partialtax*2;
            //rounding tax to even decimals
            $productCess = $basket_priceet * $itemcess / 100;
            $productCess = floor($productCess * 100) / 100;
            if ($productCess > 0) {
                $productCess = $this->nearestEvenDecimal($productCess);
            }
            $new_price_tax = ($rs * $count) - ($basket_priceet + $productCess);
            // $new_price_tax = round((($basket_priceet * $tax_val) / 100),2);
            //$new_price_tax = floor($new_price_tax * pow(10, 2)) / pow(10, 2);
            if ($new_price_tax > 0) {
                $new_price_tax = $this->nearestEvenDecimal($new_price_tax);
            }
            $partialtax = $new_price_tax / 2;
            //$new_price_tax = ($basket_priceet * $tax_val) / 100;
            $kfc_val = ($kfc > 0) ? ($new_price_tax * $kfc) / $tax_val : 0;
            $taxValue = ($kfc > 0) ? ($new_price_tax - $kfc_val) : $new_price_tax;

            $tcsVal = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TCS' limit 1");
            $partTcsVal = round($tcsVal[0]->cfg_Value / 2, 2);
            $parTtcsGst =  round($basket_priceet * $partTcsVal / 100, 2);
            $tcsGst = $parTtcsGst * 2;

            //$totalSalesMargin += $itemSalesMargin;
            $totalSalesMargin += $itemSalesMarginet;
            $totalLandingCost += $landingCost;
            $totalCess += $productCess;

            $tcs_igst = 0.00;
            $tcs_cgst =  0.00;
            $tcs_sgst =  0.00;
            $tcs_utgst = 0.00;
            $itemcgst = 0;
            $itemsgst = 0;
            $itemutgst = 0;
            $itemigst = 0.00;
            if ($tax_val > 0) {
                if ($intraState) {
                    $tcs_igst = 0.00;
                    $tcs_cgst = $parTtcsGst;
                    $itemcgst = $partialtax;
                    if ($isUTGT == true) {
                        $tcs_utgst = $parTtcsGst;
                        $tcs_sgst = 0;
                        $itemutgst = $partialtax;
                        $itemsgst = 0;
                    } else {
                        $tcs_sgst = $parTtcsGst;
                        $tcs_utgst = 0;
                        $itemsgst = $partialtax;
                        $itemutgst = 0;
                    }
                    $itemigst = 0;
                } else {
                    $tcs_igst = $tcsGst;
                    $tcs_cgst =  0.00;
                    $tcs_sgst =  0.00;
                    $tcs_utgst = 0.00;

                    $itemcgst = 0;
                    $itemsgst = 0;
                    $itemutgst = 0;
                    $itemigst = $taxValue;
                }
            }
            $itemDetails[$product_id[$x]] = [
                'orginal_sales_price'           => $selling_price,
                'order_product_id'              => $product_id[$x],
                'order_item_mrp'                => $mrpes,
                'order_item_mrp_et'             => $mrpeset,
                'order_item_basket_price'       => $basket_priceet,
                'order_item_basket_price_et'    => $basket_priceet,
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
                'item_sales_margin'             => $itemSalesMarginet,
                'order_item_cess'               => $productCess,
                'is_restaurant'                 => @$itemmaster->productCategory->hasRestaurantService,
                'itemHsncode'                   => @$branchInventory->hsnCode,
                'itemGst'                       => @$branchInventory->taxValue,
                'itemCess'                      => @$branchInventory->cessValue
            ];
            $x++;

            $total_cgst += $itemcgst;
            $total_sgst += $itemsgst;
            $total_igst += $itemigst;
            //$total_tax += $taxValue;
            $total_kfc += $kfc_val;
            $total_utgst += $itemutgst;
        }
        $order_total_cgst = $total_cgst;
        $order_total_sgst = $total_sgst;
        $order_total_utgst = $total_utgst;
        $order_total_igst = $total_igst;
        $total_tax = $order_total_cgst + $order_total_sgst + $order_total_igst + $order_total_utgst;

        $cart_total = round($total_mrpet - $total_seller_discount + $total_tax + $total_kfc + $totalCess, 2);

        $total_kfc = round($total_kfc, 2);
        $basket_price = round($basket_price, 2);
        //$totalBeforeDeliveryChrg = $order_total_cgst + $order_total_sgst + $order_total_igst +$order_total_utgst + $basket_price + $total_kfc;
        $totalBeforeDeliveryChrg = $total_mrpet - $total_seller_discount + $total_tax + $total_kfc + $totalCess;

        // $latlang = DB::select('SELECT deli_latitude, deli_longitude FROM retaline_customer_delivery_info WHERE deli_is_primary=1 AND deli_customer_id=' . $cart_customer_id[0] . ' limit 1');
        $deliData = DeliveryInfo::where([
            ['deli_is_primary', 1],
            ['deli_customer_id', @$cart_customer_id[0]]
        ])->first();
        //$latlang = DB::select('SELECT order_latitude as deli_latitude, order_longitude as deli_longitude FROM retaline_customer_order_delivery_address '
        //  . 'WHERE customer_order_id = '.$request['order_id'].' AND deli_customer_id='. $cart_customer_id[0] . ' limit 1');
        if ($order_method == 1) {
            //$shipping_mehtod = ($branchtypeid == 3 ? 1 : 2); //isset($request['shipping_method'])?$request['shipping_method']:2;
            if ($isCart == 0) {
                $isScheduled = 0;
                $delicharge = $this->calculateDeliveryChargeNew($branch_id, $branchtypeid, $weightSum, $hasRestService, @$deliData->deli_latitude, @$deliData->deli_longitude, $totalBeforeDeliveryChrg, $isScheduled, @$deliData->state->cnt_ID, @$deliData->state->st_ID, @$deliData->deli_district);

                $charge['delivery_charge'] = @$delicharge['ratforDistance'];
                $charge['delivery_status'] = @$delicharge['delivery_status'];
                $charge['delivery_rule_id'] = @$delicharge['delivery_rule_id'];
                $charge['delivery_type'] = @$delicharge['delivery_type'];
                $charge['delivered_by'] = @$delicharge['delivered_by'];
            }
        }
        //$charge = $this->getCharges($request);
        $delivery_chargeet = $charge['delivery_charge'] ?? 0;
        $courier_charge = $charge['courier_charge'] ?? 0;
        // $delivery_selection = $charge['delivery_selection'] ?? 0;
        $delivery_status = @$charge['delivery_status'] ?? 0;
        $delivery_rule_id = @$charge['delivery_rule_id'] ?? 0;
        $delivery_type = @$charge['delivery_type'] ?? 0;
        $delivered_by = @$charge['delivered_by'] ?? NULL;
        if ($basket_price <= 0) {
            $delivery_chargeet = $courier_charge = 0;
        }


        //$delivery_chargeet = round($delivery_chargeet, 2);
        $delivery_chargeet = round($delivery_chargeet) ?? 0; //delivery round off
        $courier_charge = round($courier_charge, 2);
        $deliveryTax = $this->calculateDeliveryTax($branch_id, $storegroupid, $delivery_type, $delivery_chargeet, $basket_price, $priceInventory, $prices, $product_id, $product_qty, $cosnoses);

        $delChrgGst = $deliveryTax['Gst'];
        $delChrgGstVal = $deliveryTax['GstVal'];
        //$delChrgGst = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'HANDLING_CHRG_GST' limit 1");
        //$delChrgGstVal = $delivery_chargeet * $delChrgGst[0]->cfg_Value / 100;
        $roundOffPrecision = (config('charges.delivery_charge_roundoff') == 0) ? 0 : 2;
        $delChrgGstVal = round($delChrgGstVal, $roundOffPrecision, PHP_ROUND_HALF_UP); //delivery gst round off
        $delChrgPartGstVal = $delChrgGstVal / 2;
        //$delChrgPartGstVal = round($delChrgPartGstVal) ?? 0;//delivery gst round off
        if ($intraState) {
            $delchrgcgst = $delChrgPartGstVal;
            if ($isUTGT == true) {
                $delchrgutgst = $delChrgPartGstVal;
                $delchrgsgst = 0;
            } else {
                $delchrgsgst = $delChrgPartGstVal;
                $delchrgutgst = 0;
            }

            $delchrgigst = 0;
        } else {
            $delchrgcgst = 0;
            $delchrgsgst = 0;
            $delchrgutgst = 0;
            $delchrgigst = $delChrgPartGstVal * 2;
        }
        $delChrgGstVal = round($delchrgcgst + $delchrgsgst + $delchrgigst + $delchrgutgst, 2);

        $delivery_charge = $delivery_chargeet + $delChrgGstVal;

        $tcsVal = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TCS' limit 1");
        $tdsVal = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TDS' limit 1");
        $partTcsVal = round($tcsVal[0]->cfg_Value / 2, 2);
        $partTdsVal = round($tdsVal[0]->cfg_Value / 2, 2);

        $parTtcsGst =  round($basket_price * $partTcsVal / 100, 2);
        $tcsGst = $parTtcsGst * 2;

        $tdsGst = ($basket_price + $delivery_chargeet) * $tdsVal[0]->cfg_Value / 100;
        $order_tcs = 0.00;
        $tcs_igst =  0.00;
        $tcs_cgst =  0.00;
        $tcs_sgst =  0.00;
        $tcs_utgst = 0.00;
        if ($total_tax > 0) {
            if ($intraState) {
                $tcs_igst = 0.00;
                $tcs_cgst = $parTtcsGst;
                if ($isUTGT == true) {
                    $tcs_utgst = $parTtcsGst;
                    $tcs_sgst = 0;
                } else {
                    $tcs_sgst = $parTtcsGst;
                    $tcs_utgst = 0;
                }
            } else {
                $tcs_igst = $tcsGst;
                $tcs_cgst =  0.00;
                $tcs_sgst =  0.00;
                $tcs_utgst = 0.00;
            }
            $order_tcs = $tcsGst;
        }
        $order_tds = round($tdsGst, 2);

        $order_subtotal = $total_mrpet - $total_seller_discount + $total_tax + $total_kfc + $delivery_charge + $courier_charge + $totalCess;

        $isRoudoff = (env('ROUND_OFF', false) ? 'true' : 'false');
        if ($isRoudoff == 'true') {
            $gross_total = round($order_subtotal, 0, PHP_ROUND_HALF_UP) ?? 0;
        } else {
            $gross_total = round($order_subtotal, 2);
        }
        //$gross_total = round($total, 0, PHP_ROUND_HALF_UP) ?? 0;
        $order_roundoff = round($gross_total - $order_subtotal, 2) ?? 0;
        // Discount calcuation is not enable as of now due to customer requirement.
        $discount = $this->findDiscount($request, $basket_price, $gross_total);
        /*$discount = [
            "total" => $total,
            "discount_amount" => 0,
            "discount_basket_price" => 0,
        ];*/
        $order_nettotal = $total = $discount['total'];

        return [
            'order_tds' => $order_tds,
            'order_tcs' => $order_tcs,
            'order_tcs_cgst' => $tcs_cgst,
            'order_tcs_sgst' => $tcs_sgst,
            'order_tcs_utgst' => $tcs_utgst,
            'order_tcs_igst' => $tcs_igst,
            'order_total_cgst' => $order_total_cgst,
            'order_total_sgst' => $order_total_sgst,
            'order_total_utgst' => $order_total_utgst,
            'order_total_igst' => $order_total_igst,
            "order_total_gst" => $order_total_cgst + $order_total_sgst + $order_total_igst + $order_total_utgst + $totalCess,
            "total_tax" => $total_tax + $delChrgGstVal,
            "total_kfc" => $total_kfc,
            "order_cess" => $totalCess,
            "basket_price" => $basket_price,
            "delivery_charge" => $delivery_charge,
            "delivery_charge_et" => $delivery_chargeet,
            "courier_charge" => $courier_charge,
            "order_subtotal" => $order_subtotal,
            "order_nettotal" => $order_nettotal,
            "order_grosstotal" => $gross_total,
            "total" => $total,
            "order_delivery_charge_gst" => $delChrgGstVal,
            "order_delivery_charge_cgst" => $delchrgcgst,
            "order_delivery_charge_sgst" => $delchrgsgst,
            "order_delivery_charge_utgst" => $delchrgutgst,
            "order_delivery_charge_igst" => $delchrgigst,
            "total_selling" => round($total_selling, 2),
            "order_sales_margin" => (($totalSalesMargin < 0) ? 0 : round($totalSalesMargin, 2)),
            "order_landing_cost" => (($totalLandingCost < 0) ? 0 : round($totalLandingCost, 2)),
            "total_mrp" => round($total_mrp, 2),
            "total_mrpet" => round($total_mrpet, 2),
            "seller_discount" => -round($total_seller_discount, 2),
            "discount_amount" => round($discount['discount_amount'], 2),
            "discount_basket_price" => round($discount['discount_basket_price'], 2),
            'order_roundoff' => $order_roundoff,
            'grand_total' => $total,
            "cart_total" => $cart_total,
            'item_details'  => $itemDetails,
            // 'delivery_selection' => $delivery_selection,
            'delivery_status'   => $delivery_status,
            'delivery_rule_id'   => $delivery_rule_id,
            'delivery_type'   => $delivery_type,
            'delivered_by'   => $delivered_by,
        ];
    }

    private function getPrice(array $product_id, $branch_id, $branchtypeid = 1)
    {
        if ($branchtypeid == 2) {
            //return DB::select('SELECT fcpod_itemid AS stit_id, fcpod_itemoffrrateet AS selling_price, fcpod_itemmrp AS mrp FROM finascop_contractpo_products WHERE branch_id=' . $branch_id);
            return $this->cpoItems->whereIn('fcpod_itemid', $product_id)->where('fcpod_vendorid', $branch_id)
                ->select('fcpod_itemid AS stit_id', 'fcpod_customerRateCouDel AS selling_price', 'fcpod_leastSKUmrp AS mrp')
                ->get();
        }

        return $this->inventory->whereIn('stit_id', $product_id)
            ->where('branch_id', $branch_id)
            ->select('stit_id', 'selling_price', 'mrp')
            ->get();
    }

    private function getTax(array $product_id)
    {
        return $this->item_master->whereIn('stit_ID', $product_id)
            ->select('stit_ID', 'stit_GST', 'stit_HSNCode', 'stit_hsnId')
            ->get();
    }

    private function getcosnos(array $product_id)
    {
        return $this->item_master->whereIn('stit_ID', $product_id)
            ->select('stit_ID', 'cos_nos')
            ->get();
    }

    public function getKfc($branch_id)
    {
        $state = DetermineStates::find($branch_id);
        if ($state) {
            return (int) config('kfc.kfc_percentage') ?? 0;
        }
        return 0;
    }

    private function getCharges(array $request)
    {
        if ($request['order_method'] == 1) {
            $selection = $request['selection'] ?? 0;
            switch ($selection) {
                case 1:
                    return $this->findChargeCaseOne();
                    break;
                case 2:
                    return $this->findChargeCaseTwo();
                    break;
                case 3:
                    return $this->findChargeCaseThree();
                    break;
                case 4:
                    return $this->findChargeCaseFour($request);
                    break;
                default:
                    return 0;
            }
        }
        return 0;
    }

    private function findChargeCaseOne()
    {
        return [
            "delivery_charge" => (int) config('charges.delivery_charge') ?? 0,
            "courier_charge" => 0,
        ];
    }

    private function findChargeCaseTwo()
    {
        return [
            "delivery_charge" => 0,
            "courier_charge" => (int) config('charges.courier_charge') ?? 0,
        ];
    }

    private function findChargeCaseThree()
    {
        return [
            "delivery_charge" => 0,
            "courier_charge" => (int) config('charges.courier_charge') ?? 0,
        ];
    }

    private function findChargeCaseFour(array $request)
    {
        $child = array_key_exists('child', $request) ? $request['child'] : 0;
        $all_charges = array_key_exists('all', $request) ? $request['all'] : 0;
        $delivery_charge = (int) config('charges.delivery_charge') ?? 0;
        $courier_charge = (int) config('charges.courier_charge') ?? 0;
        if ($child) {
            return [
                "delivery_charge" => 0,
                "courier_charge" => $courier_charge,
            ];
        } else if ($all_charges) {
            return [
                "delivery_charge" => $delivery_charge,
                "courier_charge" => $courier_charge,
            ];
        }
        return [
            "delivery_charge" => $delivery_charge,
            "courier_charge" => 0,
        ];
    }

    private function findDiscount(array $request, $basket_price, $total)
    {
        $discount_amt = 0;
        $discount_basket_price = 0;
        $selection = $request['selection'] ?? 0;
        if (in_array($selection, [2, 3])) {
            $discount = (int) config('charges.discount2') ?? 0;
            if ($request['selection'] === 2) {
                $discount = (int) config('charges.discount1') ?? 0;
            }
            $discount_amt = ($discount * $basket_price) / 100;
            $discount_amt = round($discount_amt, 0, PHP_ROUND_HALF_DOWN);
            $discount_basket_price = $basket_price - $discount_amt;
            $total = $total - $discount_amt;
        }
        return [
            "total" => $total,
            "discount_amount" => $discount_amt,
            "discount_basket_price" => $discount_basket_price,
        ];
    }
    private function courierDeliveryCharges($weightSum, $branch_id, $totalAmount)
    {
        $deliveryCharge = [
            'amount'    => 0,
            'selection' => 0
        ];
        $branch = Branch::where('br_ID', $branch_id)->first();
        $reqData = [
            'from_details'  => [
                'pincode'   => $branch->br_pincode,
                'city'      => $branch->br_City
            ],
            'to_details'    => [
                'pincode'   => auth()->user()->primaryAddress->deli_post,
                'city'      => auth()->user()->primaryAddress->deli_city
            ],
            'item_number'   => '12345',
            'total'         => $totalAmount,
            'length'        => 10,
            'width'         => 10,
            'height'        => 10,
            'weight'        => (@$weightSum > 0) ? $weightSum : 1
        ];
        $response = $this->courierPartner->getPartnersList($reqData);
        if ($response['status'] == 'success') {
            $deliveryCharge['amount'] = $response['amount'];
            $deliveryCharge['selection'] = $response['selection'];
        }
        return $deliveryCharge;
    }

    public function calculateDeliveryChargeNew($branch_id, $branchtypeid, $weightSum, $hasRestService, $toLat, $toLng, $totalAmount, $isScheduled = 0, $country, $state, $district = 0)
    {
        $response = [
            "freeDeliveryAmt"   => 0,
            "ratforDistance"    => 0,
            "delivery_status"   => 0,
            'delivery_rule_id'  => 0,
            'delivery_type'     => 0,
            'delivered_by'      => NULL
        ];
        $branchData = Branch::where('br_ID', $branch_id)->first();
        $strgrpID = @$branchData->br_storeGroup ? $branchData->br_storeGroup : 0;
        $deliveryData = [];
        $country = (@$country > 0) ? $country : 0;
        $state = (@$state > 0) ? $state : 0;
        $district = (@$district > 0) ? $district : 0;
        $query = "CALL DeliveryChargeCalculation({$strgrpID}, 3, {$branch_id}, 0, {$branchtypeid}, {$isScheduled}, {$weightSum}, {$hasRestService}, {$country}, {$state}, {$district});";
        $deliveryData = DB::select($query);
        if (count((array)$deliveryData) > 0) {
            $response['delivered_by'] = 0;
            $response['delivery_type'] = 3;
            $deliveryData = reset($deliveryData);
            $response['delivery_rule_id'] = @$deliveryData->rdr_id;
            if ($branchtypeid == 3) {
                $response['delivery_status'] = 1;
                $qugeoProcessor = new QugeoProcessor;
                $distanceinKm = $qugeoProcessor->getDistancebyLek($branchData->br_Lat, $branchData->br_Lng, $toLat, $toLng, 'K');
                $response['ratforDistance'] = $this->distanceRateCalculation($deliveryData, $distanceinKm,$totalAmount);
            }
            if ($branchtypeid == 1) {
                $response['delivery_status'] = 1;
                $response['ratforDistance'] = $this->deliveryRateCalcMode($branchData, $deliveryData, $toLat, $toLng, $weightSum,$totalAmount);
            }
        } else {
            $response['delivered_by'] = 1;
            // let grozeo manage
            $areaID = $branchData->areaId;
            if ($areaID > 0) {
                $response['delivery_type'] = 2;
                $grozeoQuery = "CALL DeliveryChargeCalculation(0, 4, {$areaID}, 0, {$branchtypeid}, {$isScheduled}, {$weightSum}, {$hasRestService}, {$country}, {$state}, {$district});";
                $grozeoDeliveryData = DB::select($grozeoQuery);
            }
            if (($areaID == 0) || (count((array)$grozeoDeliveryData) == 0)) {
                $response['delivery_type'] = 1;
                $grozeoQuery = "CALL DeliveryChargeCalculation(0, 1, 0, 1, {$branchtypeid}, {$isScheduled}, {$weightSum}, {$hasRestService}, {$country}, {$state}, {$district});";
                $grozeoDeliveryData = DB::select($grozeoQuery);
            }
            if (count((array)$grozeoDeliveryData) > 0) {
                $response['delivery_status'] = 1;
                $grozeoDeliveryData = reset($grozeoDeliveryData);
                $response['delivery_rule_id'] = @$grozeoDeliveryData->rdr_id;
                $response['ratforDistance'] = $this->deliveryRateCalcMode($branchData, $grozeoDeliveryData, $toLat, $toLng, $weightSum,$totalAmount);
            }
        }
        $storeShare = DB::table('delivery_cost_share')->where([
            ['storeGroupId', '=', $strgrpID],
            ['branchId', '=', $branch_id]
        ])->first();
        if ($storeShare) {
            $response["ratforDistance"] = $response["ratforDistance"] - $storeShare->shareValue;
            $response['ratforDistance'] = ($response['ratforDistance'] < 0) ? 0 : $response['ratforDistance'];
        }
        return $response;
    }
    private function deliveryRateCalcMode($branchData, $deliveryData, $toLat, $toLng, $weightSum,$totalAmount)
    {
        $ratforDistance = 0;
        $qugeoProcessor = new QugeoProcessor;
        $distanceinKm = $qugeoProcessor->getDistancebyLek($branchData->br_Lat, $branchData->br_Lng, $toLat, $toLng, 'K');
        if ($deliveryData->rdr_calculationMode == 1) {
            //Dynamic Distance rate
            $ratforDistance = $this->slabRateCalculation($deliveryData, $distanceinKm);
        }
        if (in_array($deliveryData->rdr_calculationMode, [2, 3])) {
            //Distance rate
            $ratforDistance = $this->distanceRateCalculation($deliveryData, $distanceinKm,$totalAmount);
        }
        if (in_array($deliveryData->rdr_calculationMode, [4, 5])) {
            $ratforDistance = $this->slabRateCalculation($deliveryData, $weightSum);
        }
        return $ratforDistance;
    }
    private function distanceRateCalculation($deliveryData, $distanceinKm,$totalAmount)
    {
        $ratforDistance = 0;
        $dynamicRate = $distanceinKm * $deliveryData->rdr_fixedRateperkm;
        $ratforDistance = $dynamicRate;
        if ($dynamicRate < $deliveryData->rdr_fixedRateMin) {
            $ratforDistance = $deliveryData->rdr_fixedRateMin;
        }
        if ($dynamicRate > $deliveryData->rdr_fixedRateMax) {
            $ratforDistance = $deliveryData->rdr_fixedRateMax;
        }
        if (($deliveryData->rdr_isfreeDeliveryAmt > 0) && ($totalAmount > $deliveryData->rdr_isfreeDeliveryAmt)) {
            $ratforDistance = 0;
        }
        return $ratforDistance;
    }
    private function slabRateCalculation($deliveryData, $distWeight)
    {
        $ratforDistance = 0;
        $calculationMode = $deliveryData->rdr_calculationMode;
        try {
            $slabData = @$deliveryData->slab;
            if ($slabData) {
                $slabArr = explode("|", $slabData);
                foreach ($slabArr as $s) {
                    //if (!is_null($s) && is_countable($s) && count($s) == 3) {
                    if (!is_null($s)) {
                        $parts = explode(",", $s);
                        if (count($parts) == 3) {
                            list($slabId, $slabDistWeight, $slabRate) = explode(",", $s);

                            // Convert slab distance and rate to integers
                            $slabDistWeight = (int)$slabDistWeight;
                            $slabRate = (int)$slabRate;

                            if ($distWeight > 0) {
                                // Call the function to calculate charge for the current slab
                                if($calculationMode == 1)
                                list($charge, $distWeight) = $this->calculateDistanceCharge($distWeight, $slabDistWeight, $slabRate);
                                else
                                list($charge, $distWeight) = $this->calculateWeightCharge($distWeight, $slabDistWeight, $slabRate);
                                // Add the charge for this slab to the total
                                $ratforDistance += $charge;
                            } else {
                                break; // No distance left to calculate
                            }                            
                            /*$distWeight -= $slabDistWeight;
                            if ($distWeight >= 0) {
                                $ratforDistance += $slabRate * $slabDistWeight;
                            } else {
                                $ratforDistance += $slabRate * ($slabDistWeight + $distWeight);
                            }*/
                        }                        
                    }
                }
                if ($deliveryData->rdr_fixedRateMin > 0 && $deliveryData->rdr_fixedRateMax > 0 && $ratforDistance > 0) {
                    $ratforDistance = max($deliveryData->rdr_fixedRateMin, min($ratforDistance, $deliveryData->rdr_fixedRateMax));
                }
                
            }
        } catch (\Exception $e) {
            // info("PriceCalculation slabRateCalculation Exception");
            // info($e);
            $ratforDistance = 0;
        }
        return $ratforDistance;
    }
    // Function to calculate distance-based charge for a specific slab
    private function calculateDistanceCharge($remainingDistance, $distanceinKm, $slabRate) {
        // Calculate applicable distance/weight for the slab
        $applicableDistance = min($remainingDistance, $distanceinKm);
        // Calculate charge for the applicable distance/weight
        $charge = $applicableDistance * $slabRate;
        return [$charge, $remainingDistance - $applicableDistance];
    }
    function calculateWeightCharge($remainingWeight, $slabWeight, $slabRate) {
        // Check if the weight is within the current slab
        if ($remainingWeight > 0) {
            return [$slabRate, $remainingWeight - $slabWeight];
        }
        return [0, $remainingWeight];
    }
    private function calculateDeliveryChargeNew_OLD_25_07_2024($branch_id, $branchtypeid, $weightSum, $hasRestService, $toLat, $toLng, $totalAmount)
    {
        $response = [
            "freeDeliveryAmt"   => 0,
            "ratforDistance"    => 0,
            "delivery_status"   => 0
        ];
        $branchData = Branch::where('br_ID', $branch_id)->first();
        $strgrpID = @$branchData->br_storeGroup ? $branchData->br_storeGroup : 0;
        $isScheduled = @$branchData->br_schedulePackiing ? $branchData->br_schedulePackiing : 0;
        $deliveryData = [];
        // grozeo can deliver => branch rule selected
        $query = "CALL DeliveryChargeCalculation({$strgrpID}, 3, {$branch_id}, 0, {$branchtypeid}, {$isScheduled}, {$weightSum}, {$hasRestService});";
        $deliveryData = DB::select($query);
        if (count((array)$deliveryData) > 0) {
            $response['delivery_status'] = 1;
            $deliveryData = reset($deliveryData);
            $qugeoProcessor = new QugeoProcessor;
            $distanceinKm = $qugeoProcessor->getDistancebyLek($branchData->br_Lat, $branchData->br_Lng, $toLat, $toLng, 'K');

            if ($deliveryData->rdr_calculationMode == 1) {
                //Distance/Dynamic rate
                $response['ratforDistance'] = $this->dynamicRateCalculation($deliveryData, $distanceinKm);
            }
            if ($deliveryData->rdr_calculationMode == 2) {
                //Flat/Fixed rate
                $response["ratforDistance"] = $deliveryData->rdr_fixedRateperkm;
            }
            if ($deliveryData->rdr_calculationMode == 3) {
                // grozeo can deliver => area rule selected
                $areaID = $branchData->areaId;
                $query = "CALL DeliveryChargeCalculation(0, 4, {$areaID}, 1, {$branchtypeid}, {$isScheduled}, {$weightSum}, {$hasRestService});";
                $areaData = DB::select($query);
                if (count((array)$areaData) > 0) {
                    $areaData = reset($areaData);
                    if ($areaData->rdr_calculationMode == 1) {
                        //Distance/Dynamic rate
                        $response['ratforDistance'] = $this->dynamicRateCalculation($areaData, $distanceinKm);
                    }
                    if ($areaData->rdr_calculationMode == 2) {
                        //Flat/Fixed rate
                        $response["ratforDistance"] = $areaData->rdr_fixedRateperkm;
                    }
                } else {
                    // grozeo can deliver => common rule selected
                    $query = "CALL DeliveryChargeCalculation(0, 1, 0, 1, {$branchtypeid}, {$isScheduled}, {$weightSum}, {$hasRestService});";
                    $commonData = DB::select($query);
                    if (count((array)$commonData) > 0) {
                        $commonData = reset($commonData);
                        if ($commonData->rdr_calculationMode == 1) {
                            //Distance/Dynamic rate
                            $response['ratforDistance'] = $this->dynamicRateCalculation($commonData, $distanceinKm);
                        }
                        if ($commonData->rdr_calculationMode == 2) {
                            //Flat/Fixed rate
                            $response["ratforDistance"] = $commonData->rdr_fixedRateperkm;
                        }
                    }
                }
            }
            $storeShare = DB::table('delivery_cost_share')->where([
                ['storeGroupId', '=', $strgrpID],
                ['branchId', '=', $branch_id]
            ])->first();
            if ($storeShare) {
                $response["ratforDistance"] = $response["ratforDistance"] - $storeShare->shareValue;
                $response['ratforDistance'] = ($response['ratforDistance'] < 0) ? 0 : $response['ratforDistance'];
            }
        }

        return $response;
    }
    private function dynamicRateCalculation($deliveryData, $distanceinKm)
    {
        $ratforDistance = 0;
        $checkDistanceSlab = DB::table('delivery_rule_slab')->where([
            ['drId', '=', $deliveryData->rdr_id],
            ['slabKm', '<=', $distanceinKm]
        ])->orderBy('slabKm', 'ASC')->first();
        if ($checkDistanceSlab) {
            // If rule has a price slab
            $ratforDistance = $distanceinKm * $checkDistanceSlab->slabAmount;
        } else {
            // If rule does not have a price slab
            $dynamicRate = $distanceinKm * $deliveryData->rdr_amt1;
            if ($dynamicRate < $deliveryData->rdr_fixedRateMin) {
                $dynamicRate = $deliveryData->rdr_fixedRateMin;
            }
            if ($dynamicRate > $deliveryData->rdr_fixedRateMax) {
                $dynamicRate = $deliveryData->rdr_fixedRateMax;
            }
            $ratforDistance = $dynamicRate;
        }
        return $ratforDistance;
    }

    private function calculateDeliveryChargeNew_OLD_24_06_2024($branch_id, $branchtypeid, $weightSum, $hasRestService, $toLat, $toLng, $totalAmount)
    {
        $response = [
            "freeDeliveryAmt"   => 0,
            "ratforDistance"    => 0,
            "delivery_status"   => 0
        ];
        $branchData = Branch::where('br_ID', $branch_id)->first();
        $strgrpID = @$branchData->br_storeGroup ? $branchData->br_storeGroup : 0;
        $isScheduled = @$branchData->br_schedulePackiing ? $branchData->br_schedulePackiing : 0;

        $query = "CALL DeliveryChargeCalculation({$strgrpID}, {$branch_id}, {$branchtypeid}, {$isScheduled}, {$weightSum}, {$hasRestService});";
        $deliveryData = DB::select($query);

        if (count((array)$deliveryData) > 0) {
            $response['delivery_status'] = 1;
            $deliveryData = reset($deliveryData);
            $qugeoProcessor = new QugeoProcessor;
            $distanceinKm = $qugeoProcessor->getDistancebyLek($branchData->br_Lat, $branchData->br_Lng, $toLat, $toLng, 'K');

            if ($deliveryData->rdr_calculationMode == 1) {
                //Distance/Dynamic rate
                $checkDistanceSlab = DB::table('delivery_rule_slab')->where([
                    ['drId', '=', $deliveryData->rdr_id],
                    ['slabKm', '<=', $distanceinKm]
                ])->orderBy('slabKm', 'ASC')->first();
                if ($checkDistanceSlab) {
                    // If rule has a price slab
                    $response["ratforDistance"] = $distanceinKm * $checkDistanceSlab->slabAmount;
                } else {
                    // If rule does not have a price slab
                    $dynamicRate = $distanceinKm * $deliveryData->rdr_amt1;
                    if ($dynamicRate < $deliveryData->rdr_fixedRateMin) {
                        $dynamicRate = $deliveryData->rdr_fixedRateMin;
                    }
                    if ($dynamicRate > $deliveryData->rdr_fixedRateMax) {
                        $dynamicRate = $deliveryData->rdr_fixedRateMax;
                    }
                    $response["ratforDistance"] = $dynamicRate;
                }
            }
            if ($deliveryData->rdr_calculationMode == 2) {
                //Flat/Fixed rate
                $response["ratforDistance"] = $distanceinKm * $deliveryData->rdr_fixedRateperkm;
            }
            $storeShare = DB::table('delivery_cost_share')->where([
                ['storeGroupId', '=', $strgrpID],
                ['branchId', '=', $branch_id]
            ])->first();
            if ($storeShare) {
                $response["ratforDistance"] = $response["ratforDistance"] - $storeShare->shareValue;
                $response['ratforDistance'] = ($response['ratforDistance'] < 0) ? 0 : $response['ratforDistance'];
            }
        }
        return $response;
    }

    function calculateDeliveryCharges($fromBranchId, $toBranchIdLat, $toBranchIdLong, $deliveryMode, $branchtypeid, $totalBeforeDeliveryChrg)
    {
        //deliveryMode` ---->  1:Coureir,2:Express,3:Slotted'
        //$toBranchIdDetails = Branch::where('br_ID', $toBranchId)->first();
        $data['freeDeliveryAmt'] = 0;
        if ($branchtypeid == 2) {
            $vendorDetails = Vendor::where('stpa_id', $fromBranchId)->first();
            if ($vendorDetails->asctedbrach_cpr > 0) {
                $fromBranchIdDetails = Branch::where('br_ID', $vendorDetails->asctedbrach_cpr)->first();

                $fromBranchIdDetailsbr_Lat = $fromBranchIdDetails->br_Lat;
                $fromBranchIdDetailsbr_Lng = $fromBranchIdDetails->br_Lng;
                $fromBranchIdDetailsbr_rdrIdCourier = $fromBranchIdDetails->br_rdrIdCourier;
                $fromBranchIdDetailsbr_rdrIdExpress = $fromBranchIdDetails->br_rdrIdExpress;
                $fromBranchIdDetailsbr_rdrIdSlotted = $fromBranchIdDetails->br_rdrIdSlotted;
            } else {
                $fromBranchIdDetailsbr_Lat = $vendorDetails->stpa_latitude;
                $fromBranchIdDetailsbr_Lng = $vendorDetails->stpa_longitude;
                $fromBranchIdDetailsbr_rdrIdCourier = $vendorDetails->deliveryRule_courier;
                $fromBranchIdDetailsbr_rdrIdExpress = $vendorDetails->deliveryRule_express;
                $fromBranchIdDetailsbr_rdrIdSlotted = $vendorDetails->deliveryRule_slotted;
            }
        } else {
            $fromBranchIdDetails = Branch::where('br_ID', $fromBranchId)->first();

            if (!isset($fromBranchIdDetails))
                return null;

            $fromBranchIdDetailsbr_Lat = $fromBranchIdDetails->br_Lat;
            $fromBranchIdDetailsbr_Lng = $fromBranchIdDetails->br_Lng;
            $fromBranchIdDetailsbr_rdrIdCourier = $fromBranchIdDetails->br_rdrIdCourier;
            $fromBranchIdDetailsbr_rdrIdExpress = $fromBranchIdDetails->br_rdrIdExpress;
            $fromBranchIdDetailsbr_rdrIdSlotted = $fromBranchIdDetails->br_rdrIdSlotted;
        }

        $qugeoProcessor = new QugeoProcessor;
        //$distanceinKm = $qugeoProcessor->getDistance($fromBranchIdDetails->br_Lat, $fromBranchIdDetails->br_Lng, $toBranchIdLat, $toBranchIdLong);
        $distanceinKm = $qugeoProcessor->getDistancebyLek($fromBranchIdDetailsbr_Lat, $fromBranchIdDetailsbr_Lng, $toBranchIdLat, $toBranchIdLong, 'K');
        //Store rule
        switch ($deliveryMode) {
            case 1:
                $rdr_id = $fromBranchIdDetailsbr_rdrIdCourier;
                break;
            case 2:
                $rdr_id = $fromBranchIdDetailsbr_rdrIdExpress;
                break;
            case 3:
                $rdr_id = $fromBranchIdDetailsbr_rdrIdSlotted;
                break;
        }

        if ($rdr_id > 0) {
            $storeDeliveryRules = DeliveryRules::where('rdr_id', $rdr_id)->get();
        } else {
            $storeDeliveryRules = DeliveryRules::where('rdr_ruleFor', 1)->where('is_default', 1)->where('rdr_deliveryMode', $deliveryMode)->get();
        }

        $data['ratforDistance'] = 0;
        if (count($storeDeliveryRules) > 0) {

            foreach ($storeDeliveryRules as $storeDeliveryRule) {
                $data['rdr_ruleFor'] = $storeDeliveryRule->rdr_ruleFor; //1:commonrule,2:storegrouprule,3:storerule
                $data['rdr_id'] = $storeDeliveryRule->rdr_id;
                $data['rdr_calculationMode'] = $storeDeliveryRule->rdr_calculationMode;
                if ($storeDeliveryRule->rdr_isfreeDelivery == 1) {
                    $data['freeDeliveryAmt'] = $storeDeliveryRule->rdr_isfreeDeliveryAmt;
                }
                if (($data['freeDeliveryAmt'] > 0) && ($totalBeforeDeliveryChrg >= $data['freeDeliveryAmt'])) {
                    $data['ratforDistance'] = 0;
                } else {
                    if ($storeDeliveryRule->rdr_calculationMode == 2) { //calculation mode is flat
                        $data['rateperKm'] = $storeDeliveryRule->rdr_fixedRateperkm;
                        $data['minRate'] = $storeDeliveryRule->rdr_fixedRateMin;
                        $data['maxRate'] = $storeDeliveryRule->rdr_fixedRateMax;
                        $delCharge = $distanceinKm * $data['rateperKm'];
                        if ($delCharge > $data['minRate'] && $delCharge < $data['maxRate']) {
                            $data['ratforDistance'] = $delCharge;
                        } else {
                            if ($delCharge < $data['minRate'])
                                $data['ratforDistance'] = $data['minRate'];
                            if ($delCharge > $data['maxRate'])
                                $data['ratforDistance'] = $data['maxRate'];
                        }
                    } else {
                        if (($distanceinKm >= $storeDeliveryRule->rdr_fromkm1) && ($distanceinKm <= $storeDeliveryRule->rdr_tokm1)) {
                            $data['ratforDistance'] = $storeDeliveryRule->rdr_amt1;
                        } else {
                            if ($storeDeliveryRule->rdr_amt2 > 0) {
                                if (($distanceinKm >= $storeDeliveryRule->rdr_fromkm2) && ($distanceinKm <= $storeDeliveryRule->rdr_tokm2)) {
                                    $data['ratforDistance'] = $storeDeliveryRule->rdr_amt2;
                                } else {
                                    if ($storeDeliveryRule->rdr_amt3 > 0) {
                                        if (($distanceinKm >= $storeDeliveryRule->rdr_fromkm3) && ($distanceinKm <= $storeDeliveryRule->rdr_tokm3)) {
                                            $data['ratforDistance'] = $storeDeliveryRule->rdr_amt3;
                                        } else {
                                            $data['ratforDistance'] = $storeDeliveryRule->rdr_amt3;
                                        }
                                    } else {
                                        $data['ratforDistance'] = $storeDeliveryRule->rdr_amt2;
                                    }
                                }
                            } else {
                                $data['ratforDistance'] = $storeDeliveryRule->rdr_amt1;
                            }
                        }

                        $data['ratforDistance'] = $data['ratforDistance'];
                    }
                    $data['ratforDistance'] = round($data['ratforDistance'], 2);
                }
            }
        }
        return $data;
    }

    public function checkIntraState($cart_customer_id = 0, $branchId)
    {
        $cusState = DB::select("SELECT deli_state FROM retaline_customer_delivery_info WHERE deli_is_primary=1 AND deli_customer_id= {$cart_customer_id} limit 1");
        $branchState =  DB::select("SELECT st_name FROM finascop_branch INNER JOIN finascop_state ON br_State = st_ID WHERE br_Id = {$branchId}");

        $intraState = false;
        if (@$cusState[0]->deli_state && @$branchState[0]->st_name) {
            $intraState = (strcasecmp($cusState[0]->deli_state, $branchState[0]->st_name) == 0) ? true : false;
        }
        return $intraState;
    }

    public function isUTGSTApplicable($branchId)
    {
        $branchGST =  DB::select("SELECT br_GST FROM finascop_branch WHERE br_Id = {$branchId}");
        $utCodes = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'UTS' limit 1");
        $utCodesValue = $utCodes[0]->cfg_Value;
        $utCodesArray = explode(',', $utCodesValue);
        $branchStateCode = substr($branchGST[0]->br_GST, 0, 2);

        if (in_array($branchStateCode, $utCodesArray)) {
            return true;
        } else {
            return false;
        }
    }

    public function getDeliveryChargesFields($customer_id, $orderId, $deliveryChargeNew, $request)
    {

        $orderDetails = Order::where('order_id', $orderId)->first();
        $branch_id = $orderDetails->order_branch_id;
        $intraState = $this->checkIntraState($customer_id, $branch_id);
        $isUTGT = $this->isUTGSTApplicable($branch_id);
        $delivery_chargeet = round($deliveryChargeNew, 2);

        $total_mrpet = $orderDetails->order_mrp_et;
        $total_seller_discount = $orderDetails->order_seller_discount;
        $total_tax = $orderDetails->order_total_gst;
        $total_kfc = $orderDetails->order_kfc_amount;
        $courier_charge = $orderDetails->order_courier_charge;

        $delChrgGst = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'HANDLING_CHRG_GST' limit 1");
        $delChrgGstVal = $delivery_chargeet * $delChrgGst[0]->cfg_Value / 100;
        $delChrgPartGstVal = $delChrgGstVal / 2;
        if ($intraState) {
            $delchrgcgst = round($delChrgPartGstVal, 2);
            if ($isUTGT == true) {
                $delchrgutgst = round($delChrgPartGstVal, 2);
                $delchrgsgst = 0;
            } else {
                $delchrgsgst = round($delChrgPartGstVal, 2);
                $delchrgutgst = 0;
            }

            $delchrgigst = 0;
        } else {
            $delchrgcgst = 0;
            $delchrgsgst = 0;
            $delchrgutgst = 0;
            $delchrgigst = round($delChrgPartGstVal * 2, 2);
        }
        $delChrgGstVal = round($delchrgcgst + $delchrgsgst + $delchrgigst + $delchrgutgst, 2);
        $delivery_charge = $delivery_chargeet + $delChrgGstVal;

        $tcsVal = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TCS' limit 1");
        $tdsVal = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TDS' limit 1");
        $basket_price = $orderDetails->order_total_amount;

        $tcsGst = $basket_price * $tcsVal[0]->cfg_Value / 100;
        $parTtcsGst = $tcsGst / 2;
        $tdsGst = ($basket_price + $delivery_chargeet) * $tdsVal[0]->cfg_Value / 100;
        if ($intraState) {
            $tcs_igst = 0.00;
            $tcs_cgst = round($parTtcsGst, 2);
            if ($isUTGT == true) {
                $tcs_utgst = round($parTtcsGst, 2);
                $tcs_sgst = 0;
            } else {
                $tcs_sgst = round($parTtcsGst, 2);
                $tcs_utgst = 0;
            }
        } else {
            $tcs_igst = round($parTtcsGst * 2, 2);
            $tcs_cgst =  0.00;
            $tcs_sgst =  0.00;
            $tcs_utgst = 0.00;
        }
        $order_tcs = round($parTtcsGst * 2, 2);
        $order_tds = round($tdsGst, 2);

        $order_subtotal = $total_mrpet + $total_seller_discount + $total_tax + $total_kfc + $delivery_charge + $courier_charge;
        $order_subtotal = round($order_subtotal, 2);

        $isRoudoff = (env('ROUND_OFF', false) ? 'true' : 'false');
        if ($isRoudoff == 'true') {
            $gross_total = round($order_subtotal, 0, PHP_ROUND_HALF_UP) ?? 0;
        } else {
            $gross_total = round($order_subtotal, 2);
        }
        $order_roundoff = round($gross_total - $order_subtotal, 2) ?? 0;

        $discount = $this->findDiscount($request, $basket_price, $gross_total);

        $order_nettotal = $total = $discount['total'];

        $toUpdateFields['order_delivery_charge'] = $delivery_charge;
        $toUpdateFields['order_delivery_charge_et'] = $delivery_chargeet;

        $toUpdateFields['order_delivery_charge_gst'] = $delChrgGstVal;
        $toUpdateFields['order_delivery_charge_cgst'] = $delchrgcgst;
        $toUpdateFields['order_delivery_charge_sgst'] = $delchrgsgst;
        $toUpdateFields['order_delivery_charge_utgst'] = $delchrgutgst;
        $toUpdateFields['order_delivery_charge_igst'] = $delchrgigst;

        $toUpdateFields['order_tds'] = $order_tds;
        $toUpdateFields['order_tcs'] = $order_tcs;
        $toUpdateFields['order_tcs_cgst'] = $tcs_cgst;
        $toUpdateFields['order_tcs_sgst'] = $tcs_sgst;
        $toUpdateFields['order_tcs_utgst'] = $tcs_utgst;
        $toUpdateFields['order_tcs_igst'] = $tcs_igst;

        $toUpdateFields['order_subtotal'] = $order_subtotal;
        $toUpdateFields['order_nettotal'] = $order_nettotal;
        $toUpdateFields['order_grosstotal'] = $gross_total;
        $toUpdateFields['total'] = $total;
        $toUpdateFields['order_roundoff'] = $order_roundoff;

        return $toUpdateFields;
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

    public function calculateDeliveryTax($branch_id, $storegroupid, $delivery_type, $delivery_chargeet, $totalRate, $priceInventory, $prices, $product_id, $product_qty, $cosnoses)
    {

        if ($delivery_type == 3) {
            switch (config('deliverytax.default')) {
                case 'IN':
                    $itemtaxes = array();
                    $x = 0;
                    foreach ($prices as $key => $rs) {
                        // $itemmaster = Item::select("stit_ID", "taxValueId", "product_category")->where("stit_ID", $product_id[$x])->with('productCategory')->first();
                        // $taxValues = DB::table("hsn_value")->where("id", $itemmaster->taxValueId)->first();
                        $branchInventory = BranchInventory::select("id", "stit_id", "branch_id", "taxValue")->where([
                            ["branch_id", $branch_id],
                            ["stit_id", $product_id[$x]]
                        ])->first();
                        $tax_percentage = @$branchInventory->taxValue ?? 0;//@$taxValues->hsnGst ? $taxValues->hsnGst : 0;
                        array_push($itemtaxes, $tax_percentage);
                        $x++;
                    }
                    $gstValue = max($itemtaxes);
                    return $this->fixedDeliveryTaxCalculation($delivery_chargeet, $gstValue);
                    break;
                default:
                    return $this->variableDeliveryTaxCalculation($branch_id, $storegroupid, $delivery_chargeet, $totalRate, $priceInventory, $prices, $product_id, $product_qty, $cosnoses);
                    break;
            }
        } else {
            $delChrgGst = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'HANDLING_CHRG_GST' limit 1");
            $gstValue = $delChrgGst[0]->cfg_Value;
            return $this->fixedDeliveryTaxCalculation($delivery_chargeet, $gstValue);
        }
    }
    public function fixedDeliveryTaxCalculation($delivery_chargeet, $gstValue)
    {
        return [
            'Gst' => $gstValue,
            'GstVal' => $delivery_chargeet * $gstValue / 100
        ];
    }
    public function variableDeliveryTaxCalculation($branch_id, $storegroupid,$delivery_chargeet, $totalRate, $priceInventory, $prices, $product_id, $product_qty, $cosnoses)
    {
        $totalAppoortionedPercent = 0;
        $totalAppoortionedValue = 0;
        $x = 0;
        foreach ($prices as $key => $rs) {
            // $branchInventory = DB::select("SELECT selling_price,issponsered,fpod_poLandingCostleastSKU,discount_selling_price FROM finascop_stock_branch_inventory bi inner join finascop_branch b on (b.br_ID=bi.branch_id OR (b.br_type = 1 AND b.br_typeParent = bi.branch_id)) WHERE b.br_ID = {$branch_id} AND stit_id = {$product_id[$x]} limit 1");

            $branchInventory = BranchInventory::from("finascop_stock_branch_inventory as fsi")
            ->select("selling_price", "issponsered", "fpod_poLandingCostleastSKU", "discount_selling_price", "branch_id", "stit_id", "taxValue", "cessValue")
            ->join("finascop_branch as fb", function ($join) {
                $join->on("fsi.branch_id", "fb.br_ID")
                ->orOn(function ($q) {
                    $q->on('fsi.branch_id', 'fb.br_typeParent')->where('fb.br_type', 1);
                });
            })->with("item")
            ->where([
                ["branch_id", $branch_id],
                ["stit_id", $product_id[$x]]
            ])->first();

            $itemmaster = $branchInventory->item;//Item::select("stit_ID", "taxValueId", "product_category")->where("stit_ID", $product_id[$x])->with('productCategory')->first();
            //$taxValues = DB::table("hsn_value")->where("id", $itemmaster->taxValueId)->first();
            $tax_percentage = @$branchInventory->taxValue ?? 0;//@$taxValues->hsnGst ? $taxValues->hsnGst : 0;
            $count = array_key_exists($key, $product_qty) ? $product_qty[$key] : 1;
            $branch_type_id = array_key_exists($key, $priceInventory['branch_type_id']) ? $priceInventory['branch_type_id'][$key] : 1;

            $branch = Branch::where('br_ID', $priceInventory['cart_branch_id'])->first();
            $itemstoregroup_id = $branch->br_storeGroup;

            $sellingpriceField = ($storegroupid > 0 && $itemstoregroup_id == $storegroupid ? 'selling_price' : (@$branchInventory->issponsered != 1 ? 'selling_price' : ($branch_type_id == 3 ? 'fpod_customerRateHmDel' : 'fpod_customerRateCouDel')));

            $selling_price = array_key_exists($key, $priceInventory[$sellingpriceField]) ? $priceInventory[$sellingpriceField][$key] * $cosnoses[$key] : 0;
            $rs = $selling_price;

            $basket_priceet = $rs * $count * (1 - ($tax_percentage / (100 + $tax_percentage)));
            $basket_priceet = floor($basket_priceet * 100) / 100;
            if ($basket_priceet > 0 && $tax_percentage > 0) {
                $basket_priceet = $this->nearestEvenDecimal($basket_priceet);
            }
            $appoortionedPercent = ($basket_priceet / $totalRate) * 100;
            $appoortionedPercent = floor($appoortionedPercent * 100) / 100;
            if ($appoortionedPercent > 0) {
                $appoortionedPercent = $this->nearestEvenDecimal($appoortionedPercent);
            }
            $appoortionedValue = $delivery_chargeet * ($appoortionedPercent / 100) * ($tax_percentage / 100);
            $appoortionedValue = round($appoortionedValue,2);
            $totalAppoortionedValue += $appoortionedValue;
            $totalAppoortionedPercent += $appoortionedPercent;
            $x++;
        }
        $delChrg['Gst'] = $totalAppoortionedPercent;
        $delChrg['GstVal'] = $totalAppoortionedValue;
		
        return $delChrg;
    }
}
