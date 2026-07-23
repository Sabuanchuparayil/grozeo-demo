<?php

namespace App\Http\Repositories\Coupon;

use BackOffice\Models\Item;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\FinanceAutopostingValues;
use Illuminate\Http\Request;
use App\Http\Repositories\PostingRepository;
use App\Exceptions\OfferException;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\Item\Stock;
use Illuminate\Support\Facades\Log;
use App\Models\StockItemMaster;
use App\Http\Repositories\Checkout\CheckoutOrder;
use Illuminate\Support\Arr;
use App\Http\Repositories\Order\OrderRepositoryInterface;
use App\Http\Repositories\Order\OrderRepository;
use App\Http\Responses\ErrorResponse;
use App\Http\Repositories\PaymentGatewayCredentials;

class Coupon
{

    const FLATOFFER = 1;
    const INVOICETARGETOFFER = 2;

    protected $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository = null)
    {
        $this->orderRepository = $orderRepository;
    }

    protected function getOrderRepo()
    {
        if (!$this->orderRepository) {
            $this->orderRepository = app(OrderRepositoryInterface::class);
        }

        return $this->orderRepository;
    }

    public static function coupon($request)
    {
        $res = (new static)->getDiscound($request);
        return $res;
    }

    public function getWalletOnlyDetails($request)
    {
        $orders = $this->getOrders($request['order_id']);
        $orders->order_total_amount = 0;
        $orders->order_delivery_charge = 0;
        $orders->order_total_gst = 0;
        $orders->subtotal = 0;
        $orders->order_saved_amount = 0;
        $orders->order_kfc_amount = 0;
        $orders->total = 0;
        $orders->order_roundoff = 0;
        return $this->updateStyle($request, $orders);
    }

    public function getDiscound($request)
    {
        $order = $this->getOrders($request['order_id']);
        $coupon = $this->checkCoupon($request['coupon_code']);
        if(!$coupon){
            return new ErrorResponse("Invalid Coupon.");
        }
        $discountLog = $this->checkCouponLog($coupon, $order);
        if (@$discountLog->id > 0) {
            throw new OfferException('Offer Already Used.');
        }
        if ($coupon) { //checkExpire
            if ($isValid = $this->checkCouponValidity($coupon, $order)) {
                return $this->applyCoupon($request, $coupon, $isValid);
            }
        } else {
            throw new OfferException('Invalid coupon code');
        }
    }

    private function checkCoupon($coupon)
    {
        $query = Offer::query();
        $qry =  $query->where('bom_offerCode', $coupon)
            // ->where('bom_type', 1)
            ->where('bom_status', 1);
        $offer = $qry->first();
        $use = $offer ? $offer->bom_use : 0;
        if (@$offer->maxDiscountValue > 0) {
            if ($use === 2) {
                return $offer;
            } elseif ($use === 1) {
                return $qry->where('bom_locked', 0)->first();
            }
        } else {
            throw new OfferException('Invalid Coupon Code.');
        }
        return false;
    }

    private function checkCouponLog($coupon, $order)
    {
        $trancactionLog = DB::table('discount_onOrder')
            ->select('id')
            ->where('discountId', $coupon->bom_id)
            ->where('customerId', $order->order_customer_id)
            ->where('status', 1);
        if ($coupon->customerRedemtion == 2) {
            $trancactionLog->where('orderId', $order->order_id);
        }
         return $trancactionLog->first();
    }


    private function checkCouponValidity($coupon, $order)
    {
        $type = $coupon->bom_offfrvalidtype;
        $date_to = new \DateTime($coupon->bom_enddate);
        $now = new \DateTime("now");
        if ($now > $date_to) {
            throw new OfferException('Offer Expired.');
        }
        $orderList = $this->getGroupOrders($order->order_group_id)->toArray();
        $orderStGroups = array_unique(array_column($orderList, 'storegroup_id'));
        
        if (!in_array($coupon->storeGroupId, $orderStGroups)) {
            throw new OfferException('Invalid Merchant Coupon.');
        }
        $orderBranches = array_unique(array_column($orderList, 'order_branch_id'));
        
        $availablebranches = array_filter(
            explode(',', $coupon->branch),
            function ($value) {
                return trim($value) !== ''; // Exclude empty strings and whitespace
            }
        );
        
        if (count($availablebranches) > 0) {
            $similarBranch = array_values(array_intersect($orderBranches, $availablebranches));
            if (empty($similarBranch)) {
                throw new OfferException('Invalid Store Coupon.');
            }
            $similarBranch = reset($similarBranch);
            $getOrderID = current(
                array_filter($orderList, function ($item) use ($similarBranch) {
                    return $item['order_branch_id'] === $similarBranch;
                })
            );
            return @$getOrderID['order_id'];
        }

        return true;
    }
    private function checkExpire($coupon, $order_id)
    {
        $type = $coupon->bom_offfrvalidtype;
        if ($type === 'Particular Date') {
            $date_from = new \DateTime($coupon->bom_startdate);
            $date_to = new \DateTime($coupon->bom_enddate);
            $now = new \DateTime("now");
            if ($date_from > $now || $now > $date_to) {
                throw new OfferException('Offer Expired.');
            }
        } elseif ($type === 'Till stock lasts') {
            $order = Order::where('order_id', $order_id)
                ->first(['order_branch_id']);
            $branch_id = $order->order_branch_id ?? 0;
            $stock = Stock::getStock([$coupon->stiid_itemmasterid], $branch_id);
            $stk = array_values($stock);
            if (array_key_exists(0, $stk) && $stk[0] == 0) {
                throw new OfferException('Item Out of Stock');
            }
        }

        return true;
    }

    private function applyCoupon(array $request, $coupon, $isValid = NULL)
    {

        //$offer_type = $coupon->bom_offerType ?? '';
        $offer_type = $coupon->discountType ?? '';
        switch ($offer_type) {
            case static::FLATOFFER:
            case static::INVOICETARGETOFFER:
                return $this->calculateOfferValue($request, $coupon, $isValid);
                break;
            default:
                throw new OfferException('Invalid Offer type.');
        }
    }

    private function calculateOfferValue(array $request, $coupon, $isValid = NULL)
    {
        $orders = $this->getOrders($isValid);
        if (is_bool($isValid)) {
            $orders = $this->getOrders($request['order_id']);
        }
        if ($orders) {
            $total = $orders->order_total_amount;
            if ($total >= $coupon->bom_offrDiffer) {
                $coupon_percentage = $coupon->bom_offrPlacement;
                
                $items = $orders->productItem()->get();
                $coupon_amount = ($coupon->bom_type == 0) ? ($coupon_percentage * $total) / 100 : $coupon_percentage;
                
                $discountAmount = (round($coupon_amount, 2) > $coupon->maxDiscountValue) ? $coupon->maxDiscountValue : round($coupon_amount, 2);
                
                $total_discount = $total - $discountAmount;

                $total = DB::transaction(function () use ($items, $orders, $discountAmount, $total_discount, $request, $coupon) {
                    $this->discountTransaction($orders, $coupon, $discountAmount);
                    $orderRepo = $this->getOrderRepo();
                    $updateItemOrders = $orderRepo->updateOrderItemsCoupon($items, $discountAmount, $coupon, true);
                    $total = $orderRepo->updateOrderCoupon($orders, $updateItemOrders);
                    return $total;
                });
                return $this->updateStyle($request, $orders);
            } else {
                throw new OfferException('Invalid Coupon Code');
            }
        }
    }   

    private function getOrders($order_id)
    {
        return Order::where('order_id', $order_id)
            ->where('order_customer_id', auth_user()->cust_id)
            ->select($this->orderFields())
            ->first();
    }
    private function getGroupOrders($group_order_id)
    {
        return Order::where('order_group_id', $group_order_id)
            ->selectRaw('order_id, order_total_amount, (CASE WHEN order_branch_type_id = 1 and order_courier_charge > 0 THEN order_courier_charge ELSE order_delivery_charge END) AS order_delivery_charge, order_total_gst, subtotal, order_saved_amount, order_kfc_amount, total, order_roundoff, order_mrp_et, order_seller_discount, order_delivery_charge_et, order_subtotal, order_nettotal, order_grosstotal, order_discount_amount, order_seller_discount, order_total_gst, order_delivery_charge_gst, order_cess, status_id, order_branch_id, storegroup_id')
            ->get();
    }

    private function update($orders, $updateItemOrders = array())
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

    private function orderFields()
    {
        return [
            "order_order_id",
            "order_group_id",
            "order_customer_id",
            "order_total_amount",
            "order_delivery_charge",
            "order_total_gst",
            "order_branch_id",
            "order_company_id",
            "subtotal",
            "order_saved_amount",
            "order_kfc_amount",
            "total",
            "order_roundoff",
            "storegroup_id",
            "order_id",
            "order_mrp_et",
            "order_seller_discount",
            "order_delivery_charge_et",
            "order_subtotal",
            "order_nettotal",
            "order_grosstotal",
            "order_cess"
        ];
    }

    private function updateOrderItems($items, $discountAmount, $coupon, $process = true)
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
            // Fetch item details
            $itemmaster = Item::select("stit_ID", "taxValueId", "product_category", "stit_courierWt")
                ->where("stit_ID", $itemOrder['item_product_id'])
                ->with('productCategory')
                ->first();
        
            // Fetch tax values
            $taxValues = DB::table("hsn_value")->where("id", $itemmaster->taxValueId)->first();
            $tax_percentage = @$taxValues->hsnGst ?: 0;
            $itemcess = @$taxValues->hsnCess ?: 0;
        
            // Calculate tax value
            $tax_val = $tax_percentage + $kfc;
        
            // Calculate item sales price excluding taxes (SPEt)
            $itemSPEt = $itemOrder['item_sales_price'] * (1 - (($itemcess + $tax_val) / (100 + $tax_val + $itemcess)));
            $itemSPEt = floor($itemSPEt * 100) / 100;
        
            // Return the calculated value multiplied by quantity
            return $itemSPEt * $itemOrder['item_order_qty'];
        }, $itemsArray));
        
        foreach ($items as $item) {
            $itemmaster = Item::select("stit_ID", "taxValueId", "product_category", "stit_courierWt")->where("stit_ID", $item['item_product_id'])->with('productCategory')->first();
            $taxValues = DB::table("hsn_value")->where("id", $itemmaster->taxValueId)->first();
            $tax_percentage = @$taxValues->hsnGst ? $taxValues->hsnGst : 0;
            $itemcess = @$taxValues->hsnCess ? $taxValues->hsnCess : 0;
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
            $amountAfterCoupon = $itemSPEt - $coupondata["item_discount"];
            $item_discount_total = $seller_discount + $coupondata["item_discount"];
            $total_discount += $item_discount_total;
            $total_seller_discount += $seller_discount;
            $total_after_coupon += $itemDiscountedSPEt;
            $total_gst += $new_price_tax;
            $total_kfc += $kfc_val;
            $totalCess += $productCess;
            $total_selling += $itemSP;
            $item->update([
                "item_type"                     => $coupondata["item_type"],
                "item_type_offer"               => $coupondata["item_type_offer"],
                "item_coupon_code"              => $coupondata["item_coupon_code"],
                "item_discount"                 => $coupondata["item_discount"],
                "bom_id"                        => $coupondata["bom_id"],
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
        return compact("total_after_coupon", "total_gst", "total_kfc", "total_seller_discount", "totalCess","total_discount","total_selling");
    }    

    public static function lockOffer($order_id)
    {
        $itemOrders = OrderItem::where('customer_order_id', $order_id)
            ->select('bom_id')
            ->get()
            ->pluck('bom_id')
            ->unique()
            ->toArray();
        return Offer::whereIn('bom_id', $itemOrders)
            ->update([
                'bom_locked' => 1,
            ]);
    }

    private function getStyle(array $order)
    {
        $styles = config('style.coupon');
        //$styles = Arr::except($styles, ['subtotal','order_total_amount','order_discount_amount','order_total_gst','order_delivery_charge','total']);
        $styles = Arr::except($styles, ["order_total_sgst", "order_total_cgst", "order_kfc_amount", "subtotal", "order_total_amount", 'order_total_gst', 'order_delivery_charge', 'total']);
        foreach ($styles as $key => $style) {
            if (array_key_exists($key, $order)) {
                $styles[$key]['value'] = $val = (string) round($order[$key], 2, PHP_ROUND_HALF_UP); //'.', '');
                //($key === "total") ? $styles[$key]['value'] = config('app.def_currency_symbol')." ".$val : "";
                $styles[$key]['value'] = config('app.def_currency_symbol') . " " . $val;
            }
        }

        if (!array_key_exists('wallet_amount_used', $order)) {
            unset($styles['wallet_amount_used']);
        }
        return array_values($styles);
    }

    private function orderStyle($order_id, $style)
    {
        return Order::where('order_id', $order_id)
            ->update([
                "order_amounts" => json_encode($style),
            ]);
    }
    public static function getKfc()
    {
        return (int) config('kfc.kfc_percentage') ?? 0;
    }
    public function getGst($product_id)
    {
        return StockItemMaster::where('stit_ID', $product_id)
            ->select('stit_ID', 'stit_GST')
            ->get();
    }

    private function discountTransaction($orders, $coupon, $coupon_amount)
    {
        DB::table('discount_onOrder')->insert([
            'orderId' => $orders->order_id,
            'discountId'  => $coupon->bom_id,
            'customerId' => $orders->order_customer_id,
            'created_at' => now(),
            'discountAmount' => $coupon_amount,
            'status' => 1
        ]);
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

    public function removeAppliedCoupon($request)
    {
        $orders = $this->getOrders($request['order_id']);
        $coupon = $this->getCoupon($request['coupon_code']);
        if(!$coupon){
            return new ErrorResponse("Invalid Coupon.");
        }
        $discountLog = $this->checkCouponLog($coupon, $orders);
        $items = $orders->productItem()->get();
        if (@$discountLog->id > 0) {
            $total = DB::transaction(function () use ($orders, $coupon, $discountLog, $items) {
                DB::table('discount_onOrder')->where('id', $discountLog->id)->delete();
                //DB::statement("DELETE FROM discount_onOrder WHERE id = {$discountLog->id}");
                //DB::table('discount_onOrder')->where('id', $discountLog->id)->update(array('status' => 0, 'updated_at' => now()));
                $orderRepo = $this->getOrderRepo();
                $updateItemOrders = $orderRepo->updateOrderItemsCoupon($items, 0, $coupon, false);
                $total = $orderRepo->updateOrderCoupon($orders, $updateItemOrders);
                return $total;
            });

            return $this->updateStyle($request, $orders);
        } else {
            throw new OfferException('Invalid coupon code');
        }
    }

    private function getCoupon($coupon)
    {
        $query = Offer::query();
        $qry =  $query->where('bom_offerCode', $coupon);
        $offer = $qry->first();
        return $offer;
    }

    private function updateStyle($request, $orders)
    {
        $orders->total_mrpet = 0;
        $orders->delivery_charge_et = 0;
        $orders->order_discount = 0;
        $orders->order_subtotal = 0;
        $orders->order_nettotal = 0;
        $orders->order_grosstotal = 0;
        $orders->total_tax = 0;
        $orders->order_total_amount = 0;
        $orders->order_total_gst = 0;
        $orders->order_delivery_charge = 0;
        $orders->order_cess = 0;
        $orders->order_roundoff = 0;
        $orders->total = 0;
        $orders->subtotal = 0;

        $orders->order_saved_amount = 0;
        $orders->order_kfc_amount = 0;
        $orders->order_seller_discount = 0;

        $groupOrders = $this->getGroupOrders($orders->order_group_id);
        foreach ($groupOrders as $ordr) {
            if ($ordr->status_id != 54) {
                $orders->order_total_amount += $ordr->order_total_amount;
                $orders->order_delivery_charge += $ordr->order_delivery_charge;
                $orders->order_total_gst += $ordr->order_total_gst;
                $orders->subtotal += $ordr->subtotal;
                $orders->order_saved_amount += $ordr->order_saved_amount;
                $orders->order_kfc_amount += $ordr->order_kfc_amount;
                $orders->total += $ordr->total;
                $orders->order_roundoff += $ordr->order_roundoff;
                $orders->total_mrpet += $ordr->order_mrp_et;
                $orders->order_seller_discount += $ordr->order_seller_discount;
                $orders->delivery_charge_et += $ordr->order_delivery_charge_et;
                $orders->order_subtotal += $ordr->order_subtotal;
                $orders->order_nettotal += $ordr->order_nettotal;
                $orders->order_grosstotal += $ordr->order_grosstotal;
                $orders->order_discount += $ordr->order_seller_discount;
                $orders->total_tax += $ordr->order_total_gst + $ordr->order_delivery_charge_gst;
                $orders->order_cess += $ordr->order_cess;
            }
        }
        $payCredentials = app(PaymentGatewayCredentials::class)->getCredentials();
        $orders->payment_gateway = (@$payCredentials['provider'] != "") ? $payCredentials['provider'] : config('paymentgateway.default');

        $isRoudoff = (env('ROUND_OFF', false) ? 'true' : 'false');
        if ($isRoudoff == 'true') {
            $orders->total_before_discount = round($orders->subtotal + $orders->order_delivery_charge, 0, PHP_ROUND_HALF_UP);
        } else {
            $orders->total_before_discount = round($orders->subtotal + $orders->order_delivery_charge, 2);
        }
        $orders->order_delivery_charge = (float) $orders->order_delivery_charge ?? 0;
        $orders->net_amount_payable = $orders->total;

        $walletAmount = auth_user()->cust_walletbalance ?? 0;

        if (isset($request['use_wallet']) && $request['use_wallet'] == 1) {

            if ($walletAmount > 0) {
                $orders->wallet_amount_used = $walletAmount > $orders->total ? -$orders->total : -$walletAmount;

                $orders->net_amount_payable = $orders->total + $orders->wallet_amount_used;
            }
        }
        $style = $this->getStyle($orders->toArray());
        $this->orderStyle($orders->order_id, $style);

        return [
            'style' => $orders ? $style : [],
            'net_amount_payable' => $orders->net_amount_payable,
        ];
    }
}
