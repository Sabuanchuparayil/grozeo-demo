<?php

namespace App\Modules;

use App\Models\Order;
use App\Models\BlockedItems;
use App\Models\CompanyBranch;
use App\Models\StockItemMaster;
use App\Models\UploadPrescription;
use App\Models\FinanceAutopostingValues;
use App\Http\Repositories\PostingRepository;
use App\Events\OrderHistory;
use App\Modules\CreateOrderId;
use App\Exceptions\MsgException;
use App\Modules\PriceCalculation;
use Illuminate\Support\Facades\DB;
use BackOffice\Models\BranchInventory;
use BackOffice\Status\CustomerOrderStatus;
use Illuminate\Support\Facades\Log;
use App\Http\Repositories\Item\Price;
use BackOffice\Http\Repositories\ReduceStock;
use App\Models\Branch;
use Illuminate\Http\Request;
use App\CourierPartners\CourierPartnerSection;
use App\Http\Repositories\CheckDriverRepository;

class OrderCollect
{
    private $order;

    private $inventory;

    private $item_master;

    private $blocked;

    private $company;

    private $courierPartner;

    const ONLINE = 2;

    public function __construct()
    {
        $this->order = new Order;
        $this->inventory = new BranchInventory;
        $this->item_master = new StockItemMaster;
        $this->blocked = new BlockedItems;
        $this->company = new CompanyBranch;
        $this->courierPartner = new CourierPartnerSection;
    }

    public static function createOrder(array $cart, array $request, $hasRestService = 0)
    {
        return (new static)->create($cart, $request, $hasRestService);
    }

    private function create($cart, $request, $hasRestService)
    {
        $order = 0;
        DB::transaction(function () use ($cart, $request, &$order, $hasRestService) {
            
            $branch_id = $request['cart_branch_id'] ? $request['cart_branch_id'] : $request['branch_id'];
            $branch_type_id= $request['branch_type_id'] ? $request['branch_type_id'] : 1;
            if($hasRestService > 0)
            {
                $prices = PriceCalculation::calculate($cart, $branch_id, $request, $branch_type_id);
                $checkAvailableDrivers = (new CheckDriverRepository)->checkIfDriverAvailable($branch_id, @$prices['delivered_by'], @$prices["grand_total"]);
                if($checkAvailableDrivers == 0)
                {
                    $prices['delivery_status'] = 0;
                }
            }
            else
            {
                $prices = PriceCalculation::calculate($cart, $branch_id, $request, $branch_type_id);
            }
            $order = $this->order->create(
                $this->prepareData($cart, $request, $branch_id, $branch_type_id, $prices)
            );
            /* $procedOrder = $this->createOrderByProcedure($this->prepareData($cart, $request));
            if($procedOrder)
            {
                $orderID = $procedOrder[0]->order_id;
                $order = $this->order->find($orderID, $this->orderSelectors()); */
                if($order)
                {
                    /* if($order->order_method == 3)
                    {
                        $this->courierPartner->updateCourierSelection(['order_id' => $order->order_order_id], $prices['delivery_selection']);
                    } */


                    // $order = $this->convertOrderNumbers($order);
                    
                    $this->saveOrderAddress($order);
                    $order->productItem()->createMany(
                        $this->prepareOrderedItems($cart, $order, $request, $prices)
                                );
                    $this->blockedOrderedItems($order, $cart);
                    
                    if (!(array_key_exists('splitorder', $request) && $request['splitorder'] == 1)) {
                        $this->updateOrderDetails($order->order_id);
                    }

                    if(isset($request['prescription_id']) && !empty($request['prescription_id'])){
                        $this->updatePrescriptions($request['prescription_id'],$order->order_id);
                    }
                    
                    $this->saveFinanceAutoPostingValues($order);
                    event(new OrderHistory($order->order_id, CustomerOrderStatus::CHECKEDOUT));

                    $order->delivery_status = @$prices['delivery_status'];
                }
            // }
        });
        return $order;
    }

    private function prepareData($cart, $request, $branch_id, $branch_type_id, $prices)
    {
        $storegroupid = getHeaderStoreGroup();
        // order_method options: 1 - home delivery, 3 - courier delivery, 2 - customer pickup.
        $ordermethod = ($request["order_method"] != 2 ? ($branch_type_id == 3 ? 1 : 3) : 2);

        return [
            'order_order_id'                        => CreateOrderId::generate(),
            'order_group_id'                        => $request['order_group_id'],
            'order_branch_type_id'                  => $branch_type_id,
            'order_customer_id'                     => auth()->user()->cust_id,
            'order_total_amount'                    => (@$prices['basket_price'] ? $prices['basket_price'] : 0),
            'order_delivery_charge'                 => (@$prices['delivery_charge'] ? $prices['delivery_charge'] : 0),
            'order_courier_charge'                  => (@$prices['courier_charge'] ? $prices['courier_charge'] : 0),
            'order_total_gst'                       => (@$prices['order_total_gst'] ? $prices['order_total_gst'] : 0),
            //'total_tax' => (@$prices['total_tax'] ? $prices['total_tax'] : 0),
            'order_branch_id'                       => $branch_id,
            'order_company_id'                      => $this->getCompany($branch_id),
            'subtotal'                              => $selling = (@$prices['total_selling'] ? $prices['total_selling'] : 0),
            'order_mrp'                             => $mrp = (@$prices['total_mrp'] ? $prices['total_mrp'] : 0),
            'order_saved_amount'                    => round($mrp - $selling, 2),
            'order_kfc_amount'                      => (@$prices['total_kfc'] ? $prices['total_kfc'] : 0),
            'order_subtotal'                        => (@$prices["order_subtotal"] ? $prices["order_subtotal"] : 0),
            'order_nettotal'                        => (@$prices["order_nettotal"] ? $prices["order_nettotal"] : 0),
            'order_grosstotal'                      => (@$prices["order_grosstotal"] ? $prices["order_grosstotal"] : 0),
            'total'                                 => (@$prices["grand_total"] ? $prices["grand_total"] : 0),
            'order_roundoff'                        =>  (@$prices['order_roundoff'] ? $prices['order_roundoff'] : 0),
            'order_method'                          => $ordermethod, //(isset($request["shipping_method"]) && $request["shipping_method"]==2  && $request["order_method"]==1)? 3: $request['order_method'],
            'order_type'                            => isset($request['selection'])?$request['selection']:0,
            'order_discount_amount'                 => (@$prices['discount_amount'] ? $prices['discount_amount'] : 0),
            'order_discount_add_total'              => (@$prices['discount_basket_price'] ? $prices['discount_basket_price'] : 0),
            'order_total_cgst'                      => (@$prices['order_total_cgst'] ? $prices['order_total_cgst'] : 0),
            'order_total_sgst'                      => (@$prices['order_total_sgst'] ? $prices['order_total_sgst'] : 0),
            'order_total_utgst'                     => (@$prices['order_total_utgst'] ? $prices['order_total_utgst'] : 0),
            'order_total_igst'                      => (@$prices['order_total_igst'] ? $prices['order_total_igst'] : 0),
            'order_delivery_charge_gst'             => (@$prices['order_delivery_charge_gst'] ? $prices['order_delivery_charge_gst'] : 0),
            'order_delivery_charge_cgst'            => (@$prices['order_delivery_charge_cgst'] ? $prices['order_delivery_charge_cgst'] : 0),
            'order_delivery_charge_sgst'            => (@$prices['order_delivery_charge_sgst'] ? $prices['order_delivery_charge_sgst'] : 0),
            'order_delivery_charge_utgst'           => (@$prices['order_delivery_charge_utgst'] ? $prices['order_delivery_charge_utgst'] : 0),
            'order_delivery_charge_igst'            => (@$prices['order_delivery_charge_igst'] ? $prices['order_delivery_charge_igst'] : 0),
            'order_cess'                            => (@$prices['order_cess'] ? $prices['order_cess'] : 0),
            'order_tds'                             => (@$prices['order_tds'] ? $prices['order_tds'] : 0),
            'order_tcs'                             => (@$prices['order_tcs'] ? $prices['order_tcs'] : 0),
            'order_tcs_cgst'                        => (@$prices['order_tcs_cgst'] ? $prices['order_tcs_cgst'] : 0),
            'order_tcs_sgst'                        => (@$prices['order_tcs_sgst'] ? $prices['order_tcs_sgst'] : 0),
            'order_tcs_utgst'                       => (@$prices['order_tcs_utgst'] ? $prices['order_tcs_utgst'] : 0),
            'order_tcs_igst'                        => (@$prices['order_tcs_igst'] ? $prices['order_tcs_igst'] : 0),
            'order_portal_afterpayment_redirecturl' => (isset($request['portal_redirecturl'])?base64_decode($request['portal_redirecturl']):""),
            'storegroup_id'                         => $storegroupid,
            'order_mrp_et'                          => (@$prices['total_mrpet'] ? $prices['total_mrpet'] : 0),
            'order_seller_discount'                 => (@$prices['seller_discount'] ? $prices['seller_discount'] : 0),
            'order_delivery_charge_et'              => (@$prices['delivery_charge_et'] ? $prices['delivery_charge_et'] : 0),
            'order_sales_margin'                    => (@$prices['order_sales_margin'] ? $prices['order_sales_margin'] : 0),
            'order_landing_cost'                    => (@$prices['order_landing_cost'] ? $prices['order_landing_cost'] : 0),
            'entry_RefId'                           => (DB::select('SELECT UUID() as uuid')[0]->uuid),
            'status_id'                             => (@$prices['delivery_status'] == 0) ? 54 : 0,
            'delivery_rule_id'                      => @$prices['delivery_rule_id'],
            'delivery_rule_type'                    => @$prices['delivery_type'],
        ];
    }

    public function saveOrderAddress(&$order)
    {
        $primaryAddress = auth()->user()->primaryAddress;
        if(empty($primaryAddress))
        {
            throw new MsgException('Primary address is not found..!');
        }
        $order->deliveryAddress()->create([
            'order_customer_id' => auth()->user()->cust_id,
            'order_id'              => $order->order_order_id,
            'deli_id'               => $primaryAddress->deli_id,
            'order_contact_no'      => $primaryAddress->deli_contact_no,
            'order_house_no'        => $primaryAddress->deli_house_no,
            'order_house_name'      => $primaryAddress->deli_house_name,
            'order_city'            => (isset($primaryAddress->deli_city) ? $primaryAddress->deli_city : (isset($primaryAddress->deli_land_mark) ? $primaryAddress->deli_land_mark : '')),
            'order_post'            => $primaryAddress->deli_post,
            'order_state'           => $primaryAddress->deli_state,
            'order_pin'             => $primaryAddress->deli_delivery_pin,
            'order_latitude'        => $primaryAddress->deli_latitude,
            'order_longitude'       => $primaryAddress->deli_longitude,
            'order_country'         => config('app.operatingcountry'),
            'order_customer_name'   => ($primaryAddress->deli_name != "") ? $primaryAddress->deli_name : auth()->user()->cust_customer_name,
            'order_land_mark'       => (isset($primaryAddress->deli_land_mark) ? $primaryAddress->deli_land_mark : ''),
            'order_address'         => $primaryAddress->deli_address,
            'order_address2'        => $primaryAddress->deli_address2,
            'order_customer_email'  => ($primaryAddress->deli_email != "") ? $primaryAddress->deli_email : auth()->user()->cust_email,
        ]);
    }

    private function prepareOrderedItems($cart, $order, $request, $prices)
    {
        $branch_id= $request["branch_id"];
        $getOrderItemsPrices= $this-> getOrderItemsPrices( $cart,$order, $request);
        
        $product_id = array_column($cart, 'cart_product_id');
        // $prices = $this->getPrice($product_id, $branch_id)->toArray();
        // $selling = array_column($prices, 'selling_price', 'stit_id');
        // $mrp = array_column($prices, 'mrp', 'stit_id');
        $medicine = $this->getItems($product_id)->toArray();
        $med = array_column($medicine, 'isMedicine', 'stit_ID');
        $itemDetails = @$prices['item_details'];
        return array_map(function ($item) use ($order, $med,$getOrderItemsPrices, $itemDetails) {
            $count = $item['cart_order_qty'] ?? 1;
            $product_id = $item['cart_product_id'];
            // TODO: Order line items use cart prices while the order header uses recalculated prices; reconcile for tax/margin/refund consistency.
            return [
                'item_product_id'               => $product_id,
                'item_group_id'                 => $item['cart_group_id'],
                'item_order_qty'                => $count,
                'item_order_id'                 => $order->order_order_id,
                'item_price'                    => round($item['cart_sales_price'] * $count, 2),//round($getOrderItemsPrices[$product_id]["selling_price"] * $count, 2),
                'item_retail_price'             => $item['cart_retail_price'],//$getOrderItemsPrices[$product_id]["mrp"] ?? 0,
                'item_sales_price'              => $item['cart_sales_price'],//$getOrderItemsPrices[$product_id]["selling_price"],
                'item_amount'                   => 0,
                'item_isMedicine'               => $med[$product_id] ?? 0,
                'item_status'                   => 0,
                'item_cgst'                     => round( $item['cart_cgst'],2), //round( $getOrderItemsPrices[$product_id]["gst"]/2,2),
                'item_sgst'                     => round( $item['cart_sgst'],2), //round( $getOrderItemsPrices[$product_id]["gst"]/2,2),
                'item_igst'                     => round( $item['cart_igst'],2), //$getOrderItemsPrices[$product_id]["gst"],
                'branch_type_id'                => $item['branch_type_id'],//$order->order_branch_type_id,
                'order_branch_id'               => $item['cart_branch_id'],//$order->order_branch_id,
                'orginal_sales_price'           => @$itemDetails[$product_id]['orginal_sales_price'],
                'order_item_mrp'                => @$itemDetails[$product_id]['order_item_mrp'],
                'order_item_mrp_et'             => @$itemDetails[$product_id]['order_item_mrp_et'],
                'order_item_basket_price'       => @$itemDetails[$product_id]['order_item_basket_price'],
                'order_item_basket_price_et'    => @$itemDetails[$product_id]['order_item_basket_price_et'],
                'order_item_seller_discount'    => @$itemDetails[$product_id]['order_item_seller_discount'],
                'order_item_gst'                => @$itemDetails[$product_id]['order_item_gst'],
                'order_item_cgst'               => @$itemDetails[$product_id]['order_item_cgst'],
                'order_item_ugst'               => @$itemDetails[$product_id]['order_item_ugst'],
                'order_item_sgst'               => @$itemDetails[$product_id]['order_item_sgst'],
                'order_item_igst'               => @$itemDetails[$product_id]['order_item_igst'],
                'order_item_tcs_gst'            => @$itemDetails[$product_id]['order_item_tcs_gst'],
                'order_item_tcs_igst'           => @$itemDetails[$product_id]['order_item_tcs_igst'],
                'order_item_tcs_cgst'           => @$itemDetails[$product_id]['order_item_tcs_cgst'],
                'order_item_tcs_utgst'          => @$itemDetails[$product_id]['order_item_tcs_utgst'],
                'order_item_tcs_sgst'           => @$itemDetails[$product_id]['order_item_tcs_sgst'],
                'item_sales_margin'             => @$itemDetails[$product_id]['item_sales_margin'],
                'order_item_cess'               => @$itemDetails[$product_id]['order_item_cess'],
                'is_restaurant'                 => @$itemDetails[$product_id]['is_restaurant'],
                'itemHsncode'                   => @$itemDetails[$product_id]['itemHsncode'],
                'itemGst'                       => @$itemDetails[$product_id]['itemGst'],
                'itemCess'                      => @$itemDetails[$product_id]['itemCess']
            ];
        }, $cart);
    }
    private function getOrderItemsPrices( $cart,$order, $request)
    {
        $product_id = array_column($cart, 'cart_product_id');
        $branch_type_id= $request['branch_type_id'] ? $request['branch_type_id'] : 1;
        $price = Price::findPrice($product_id, $request['branch_id'],$branch_type_id);
        $cos_noss = StockItemMaster::whereIn('stit_ID',$product_id)->pluck('cos_nos','stit_ID','stit_GST')->toArray();
        
        $result=[];
        foreach($product_id as $pId){
            $cos_noss[$pId] = ($cos_noss[$pId] > 0)?$cos_noss[$pId]:1;
            $result[$pId]["mrp"] = array_key_exists($pId, $price['fpod_leastSKUmrp']) ? $price['fpod_leastSKUmrp'][$pId] *$cos_noss[$pId] : 0;
            //$order_method= 1;
              /*  if($order_method==1){
                     $selling_price = array_key_exists($pId, $price['fpod_customerRateCouDel']) ? $price['fpod_customerRateCouDel'][$pId] *$cos_noss[$pId]: 0;
                }elseif($order_method==2){
                   $selling_price = array_key_exists($pId, $price['fpod_customerRatePikup']) ? $price['fpod_customerRatePikup'][$pId] *$cos_noss[$pId] : 0;  
                }else{
                    $selling_price = array_key_exists($pId, $price['fpod_customerRatePikup']) ? $price['fpod_customerRatePikup'][$pId] *$cos_noss[$pId] : 0;  
                }*/
            /*
                $order_method=$request['order_method'];
                if($order_method==1){
                    $shipping_mehtod= ($branch_type_id == 3 ? 1 : 2); //isset($request['shipping_method'])?$request['shipping_method']:2;
        
                    if($shipping_mehtod==1){ 
                        $selling_price = array_key_exists($pId, $price['fpod_customerRateHmDel']) ? $price['fpod_customerRateHmDel'][$pId] *$cos_noss[$pId]: 0;
                    }else{
                        $selling_price = array_key_exists($pId, $price['fpod_customerRateCouDel']) ? $price['fpod_customerRateCouDel'][$pId] *$cos_noss[$pId]: 0;

                    }
                }else{
            */
                   $selling_price = array_key_exists($pId, $price['fpod_customerRatePikup']) ? $price['fpod_customerRatePikup'][$pId] *$cos_noss[$pId] : 0;
            /*
                }
            */
                $result[$pId]["selling_price"] = round($selling_price,2);
                $result[$pId]["mrp"]=round( $result[$pId]["mrp"],2);
                $gstval = StockItemMaster::where('stit_ID',$pId)->pluck('stit_GST')->toArray();
                $result[$pId]["gst"]=  $gstval[0];
        }
       return $result;
    }



    private function getPrice(array $product_id, $branch_id)
    {
        return  $this->inventory->whereIn('stit_id',$product_id)
                                ->where('branch_id',$branch_id)
                                ->select('stit_id','selling_price','mrp')
                                ->get();
    }

    private function getItems(array $product_id)
    {
        return $this->item_master->whereIn('stit_ID', $product_id)
                                ->select('stit_ID', 'isMedicine')
                                ->get();
    }

    private function blockedOrderedItems($order, $cart)
    {
       DB::transaction(function () use ($order, $cart) {
       foreach($cart as $cartItem)
       {
            $itemid=$cartItem['cart_product_id'];
            $item = $this->item_master->where('stit_ID', $itemid)->first();
            $orderQty = $cartItem['cart_order_qty'];

            $br_schedulePackiing = Branch::where('br_ID', $order->order_branch_id)->first();
            if ($br_schedulePackiing->br_type == 1) {
                $order_branch_id = $br_schedulePackiing->br_typeParent;
            }else{
                $order_branch_id = $order->order_branch_id;
            }

            $inventory = BranchInventory::where('stit_id', $itemid)
                ->where('branch_id', $order_branch_id)
                ->lockForUpdate()
                ->first();
            if (!$inventory || $inventory->item_count < $orderQty) {
                throw new MsgException('Insufficient stock for item ' . $itemid);
            }

            $this->blocked->create([
                    "item_id" => $itemid, //$cartItem['cart_product_id'],
                    "branch_id"  => $order_branch_id,
                    "count" => $orderQty, //$cartItem['cart_order_qty'],
                    "customer_id" => $order['order_customer_id'],
                    "expiry" => now()->addMinutes(25),
                    "markedfordelivery" => 0,
                    "order_id" => $order->order_id
            ]);
            if(isset($item) && $item->stit_ParentItemId > 0){
                ReduceStock::ResetChildItemsStock($item->stit_ParentItemId, $order_branch_id);
            }
       }
       });
    }

    private function getCompany($branch_id)
    {
        $company_id = $this->company->where('br_Id', $branch_id)->first();
        return $company_id->comp_id ?? 0;
    }

    private function updatePrescriptions($prescription_id,$order_id)
    {
        //echo "<pre>";print_r($prescription_id);exit;
        foreach ($prescription_id as $id){
            UploadPrescription::where('id', $id["id"])
            ->update(['order_id' => $order_id]);
        }
        return true;
    }
    private function updateOrderDetails($order_id)
    {
        return $this->order->where('order_id', $order_id)
            ->update([
                'payment_mode' => static::ONLINE,
                'status_id' => CustomerOrderStatus::PAYMENT_INITIATED,
                'order_confirm_date' => now()->format('Y-m-d'),
                'order_confirmed_on' =>  now()->format('Y-m-d H:i:s'),
                'order_customer_cancel_till' => $this->getAfterBookingDelayTime(time(),1),
                'order_delivery_start_at' => $this->getAfterBookingDelayTime(time(),2)
            ]);
    }
    private function getAfterBookingDelayTime($date,$type)
    {
        if($type==1){
            $addseconds  =  config('b2cbooking.customer_cancel_till_seconds') ?? 120;
        }else{
            $addseconds  =  config('b2cbooking.delivery_process_start_at_seconds') ?? 240;
        }
        return date('Y-m-d H:i:s', $date + $addseconds);
    }
    private function createOrderByProcedure($orderDetails)
    {
        $query = 'CALL createNewOrderProcedure("'.$orderDetails['order_group_id'].'", '.$orderDetails['order_branch_type_id'].', '.$orderDetails['order_customer_id'].', '.$orderDetails['order_total_amount'].', '.$orderDetails['order_delivery_charge'].', '.$orderDetails['order_courier_charge'].', '.$orderDetails['order_total_gst'].', '.$orderDetails['order_branch_id'].', '.$orderDetails['order_company_id'].', '.$orderDetails['subtotal'].', '.$orderDetails['order_mrp'].', '.$orderDetails['order_saved_amount'].', '.$orderDetails['order_kfc_amount'].', '.$orderDetails['order_subtotal'].', '.$orderDetails['order_nettotal'].', '.$orderDetails['order_grosstotal'].', '.$orderDetails['total'].', '.$orderDetails['order_roundoff'].', '.$orderDetails['order_method'].', '.$orderDetails['order_type'].', '.$orderDetails['order_discount_amount'].', '.$orderDetails['order_discount_add_total'].', '.$orderDetails['order_total_cgst'].', '.$orderDetails['order_total_sgst'].', '.$orderDetails['order_total_utgst'].', '.$orderDetails['order_total_igst'].', '.$orderDetails['order_delivery_charge_gst'].', '.$orderDetails['order_delivery_charge_cgst'].', '.$orderDetails['order_delivery_charge_sgst'].', '.$orderDetails['order_delivery_charge_utgst'].', '.$orderDetails['order_delivery_charge_igst'].', '.$orderDetails['order_tds'].', '.$orderDetails['order_tcs'].', '.$orderDetails['order_tcs_cgst'].', '.$orderDetails['order_tcs_sgst'].', '.$orderDetails['order_tcs_utgst'].', '.$orderDetails['order_tcs_igst'].', "'.$orderDetails['order_portal_afterpayment_redirecturl'].'", '.$orderDetails['storegroup_id'].', '.$orderDetails['order_mrp_et'].', '.$orderDetails['order_seller_discount'].', '.$orderDetails['order_delivery_charge_et'].', "'.now().'")';

        return DB::select($query);
    }
    private function orderSelectors()
    {
        return [
            'order_id',
            'order_order_id',
            'order_group_id',
            'order_branch_type_id',
            'order_customer_id',
            'order_total_amount',
            'order_delivery_charge',
            'order_courier_charge',
            'order_total_gst',
            'order_branch_id',
            'order_company_id',
            'subtotal',
            'order_mrp',
            'order_saved_amount',
            'order_kfc_amount',
            'order_subtotal',
            'order_nettotal',
            'order_grosstotal',
            'total',
            'order_roundoff',
            'order_method',
            'order_type',
            'order_discount_amount',
            'order_discount_add_total',
            'order_total_cgst',
            'order_total_sgst',
            'order_total_utgst',
            'order_total_igst',
            'order_delivery_charge_gst',
            'order_delivery_charge_cgst',
            'order_delivery_charge_sgst',
            'order_delivery_charge_utgst',
            'order_delivery_charge_igst',
            'order_tds',
            'order_tcs',
            'order_tcs_cgst',
            'order_tcs_sgst',
            'order_tcs_utgst',
            'order_tcs_igst',
            'order_portal_afterpayment_redirecturl',
            'storegroup_id',
            'order_mrp_et',
            'order_seller_discount',
            'order_delivery_charge_et'
        ];
    }
    private function convertOrderNumbers($order)
    {
        $order->order_total_amount = floatval($order->order_total_amount);
        $order->order_total_gst = floatval($order->order_total_gst);
        $order->order_roundoff = floatval($order->order_roundoff);
        $order->total = floatval($order->total);
        $order->order_delivery_charge = floatval($order->order_delivery_charge);
        $order->order_courier_charge = floatval($order->order_courier_charge);
        $order->subtotal = floatval($order->subtotal);
        $order->order_mrp = floatval($order->order_mrp);
        $order->order_saved_amount = floatval($order->order_saved_amount);
        $order->order_kfc_amount = floatval($order->order_kfc_amount);
        $order->order_discount_amount = floatval($order->order_discount_amount);
        $order->order_discount_add_total = floatval($order->order_discount_add_total);
        $order->order_total_cgst = floatval($order->order_total_cgst);
        $order->order_total_sgst = floatval($order->order_total_sgst);
        $order->order_total_utgst = floatval($order->order_total_utgst);
        $order->order_total_igst = floatval($order->order_total_igst);
        $order->order_delivery_charge_gst = floatval($order->order_delivery_charge_gst);
        $order->order_delivery_charge_cgst = floatval($order->order_delivery_charge_cgst);
        $order->order_delivery_charge_sgst = floatval($order->order_delivery_charge_sgst);
        $order->order_delivery_charge_utgst = floatval($order->order_delivery_charge_utgst);
        $order->order_delivery_charge_igst = floatval($order->order_delivery_charge_igst);

        return $order;
    }

    
    private function saveFinanceAutoPostingValues($order)
    {
        /* $defaultFinance = config('finance.default');
        $financeClass = config("finance.{$defaultFinance}");
        $financeObj = new $financeClass();

        $financeObj->financeAutopostings($order, 'collectorder'); */

        $postReq = new Request();
        $postReq->setMethod('POST');
        $postReq->request->add([
            'order_id'              => $order->order_id,
            'finascopEventRefId'    => config("event_master.checkout"),
            'storegroup_id'         => ($order->storegroup_id ? $order->storegroup_id : 0)
        ]);

        (new PostingRepository)->finascopPosting($postReq); 
    }
}
