<?php

namespace App\Http\Repositories\Checkout;

use stdClass;
use App\Models\Cart;
use App\Models\Order;
use App\Models\FinanceAutopostingValues;
use App\Models\AppConfig;
use Illuminate\Support\Arr;
use App\Events\OrderHistory;
use App\Models\BlockedItems;
use App\Models\CompanyBranch;
use App\Models\StockItemMaster;
use App\Modules\DetermineStates;
use App\Modules\PriceCalculation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Domains\Cart\GenerateOrderId;
use BackOffice\Models\BranchInventory;
use App\Http\Services\B2CToTransferOrder;
use BackOffice\Status\CustomerOrderStatus;
use App\Http\Repositories\Payment\PaymentRepository;
use Illuminate\Http\Request;
use App\Http\Repositories\PostingRepository;

class CheckoutOrder
{

    private $order;

    public function __construct()
    {
        $this->order = new Order;
    }
    public static function customerCheckout(array $cart)
    {
        return (new static)->customerOrder($cart);
    }

    private function customerOrder($cart)
    {
        $order = null;
        DB::transaction(function () use ($cart, &$order) {
            $order = $this->order->create(
                        $this->prepareData($cart)
                            );
            
            /* $autoPosting = FinanceAutopostingValues::create([
                'order_id'          => $order->order_id,
                'RetailSalePrice'   => $order->order_total_amount,
                // 'ODCTotal'          => $order->order_delivery_charge,
                // 'GSTonODCTotal'     => $order->order_delivery_charge_gst,
                'GSTonRSP_Final'    => $order->order_total_gst
            ]); */
            
            $postReq = new Request();
            $postReq->setMethod('POST');
            $postReq->request->add([
                'order_id'              => $order->order_id,
                'finascopEventRefId'    => config("event_master.checkout"),
                'storegroup_id'         => (@$order->storegroup_id ? $order->storegroup_id : 0)
            ]);
            
            (new PostingRepository)->finascopPosting($postReq);
            
            $this->saveOrderAddress($order);
            $order->productItem()->createMany(
            $this->prepareOrderedItems($cart)
                    );
            $order ? $this->blockedOrderedItems($order, $cart,$order['order_branch_id']) : false;
       });
       $order->total = $order ? $order->total = $order->order_delivery_charge +
                                $order->order_total_gst +
                                $order->order_total_amount : 0;
        event(new OrderHistory($order->order_id, CustomerOrderStatus::CHECKEDOUT));
        return $order ?? new \stdClass;
    }

     /**
     * Prepare data for creating order.
     *
     * @param
     * @return array
     */
    private function prepareData($cart)
    {
        $price = $this->priceCalculation($cart);
      return [
            'order_order_id' => GenerateOrderId::generate(),
            'order_customer_id' => auth()->user()->cust_id,
            'order_total_amount' => $price['basket_price'],
            'order_delivery_charge' => $this->getDeliveryCharge(),
            'order_delivery_charge_gst' => $price['order_delivery_charge_gst'],
            'order_total_gst' => $price['total_gst'],
            'order_branch_id' => $branch_id = $cart[0]['cart_branch_id'],
            'order_company_id' => $this->getCompany($branch_id),
            'entry_RefId'   => (DB::select('SELECT UUID() as uuid')[0]->uuid)
        ];
    }
    public function order_success($order_id){
        $order_customer_cancel_till = PaymentRepository::getAfterBookingDelayTime(time(),1);
        $order_delivery_start_at = PaymentRepository::getAfterBookingDelayTime(time(),0);
        $order_status=CustomerOrderStatus::SUCCESS;
        event(new OrderHistory($order_id, $order_status));
        Order::where('order_id', $order_id)
                    ->update([
                        'status_id' => $order_status,
                        'order_customer_cancel_till' =>  $order_customer_cancel_till,
                        'order_delivery_start_at' => $order_delivery_start_at
                    ]);
         B2CToTransferOrder::transferOrders($order_id);
                    
    }
    public function priceCalculation($cart, $selection = 0, $all = 0,$shipping_mehtod=2, $isCart=0)
    {
       $cartItems = array_map(function ($item) {
                        return [
                            'cart_customer_id' => $item['cart_customer_id'],
                            'cart_group_id' => $item['cart_group_id'],
                            'cart_product_id' => $item['cart_product_id'],
                            'cart_branch_id' => $item['cart_branch_id'],
                            'cart_order_qty' => $item['cart_order_qty'],
                            'order_method' => $item['order_method'],
                            'branch_type_id' => $item['branch_type_id'],
                            'storegroup_id' => $item['storegroup_id'],
                            ];
                    }, $cart);
        $branch = $cartItems[0]['cart_branch_id'];
        $branchtypeid=$cartItems[0]['branch_type_id'];
        $data['order_method'] = $cartItems[0]['order_method'];
        $data['selection'] = $selection;
        $data['all'] = $all;
        $data['shipping_method'] = $shipping_mehtod;
        $price = app(PriceCalculation::class)->calculate($cart, $branch, $data, $branchtypeid, $isCart);
       $response= [
            "cart_total" => $price['cart_total'] ?? 0,
			"subtotal" => $price['total_selling'] ?? 0,
            "order_total_amount" => $price['basket_price'] ?? 0,
            "order_total_gst" => $price['order_total_gst'] ?? 0,
           "total_tax" => $price['total_tax'] ?? 0,
            "order_total_cgst" => $price['order_total_cgst'] ?? 0,
            "order_total_sgst" => $price['order_total_sgst'] ?? 0,
            "order_kfc_amount" => $price['total_kfc'] ?? 0,
           "order_delivery_charge_gst" => $price['order_delivery_charge_gst'] ?? 0,
           // "total" => $round_total = round($price['total'], 0, PHP_ROUND_HALF_UP) ?? 0,
            "total"=>$price["grand_total"],
     //       "order_roundoff" => round($round_total - $price['total'], 2) ?? 0,
            "order_roundoff" => $price['order_roundoff'],
            "order_discount" => $price['discount_amount'] ?? 0,
			 "total_mrpet" => $price['total_mrpet'] ?? 0,
        "seller_discount" => $price['seller_discount'] ?? 0,
		"delivery_charge_et" => $price['delivery_charge_et'] ?? 0,
         //   "order_delivery_charge" => $price['delivery_charge'] ?? 0,
          //  "order_courier_charge" => $price['courier_charge'] ?? 0,
        ];
        
        if($price['delivery_charge']){
            $response["order_delivery_charge"]= $price['delivery_charge'];
        }
        if($price['courier_charge']){
            $response["order_courier_charge"]= $price['courier_charge'];
        }
        return $response;
    }

    public function getPriceValues(array $cart, $branch_id, int $selection, $child = 0,$shipping_method=2)
    {
        $styles = [];
        if(count($cart) > 0)
        {
          $state = DetermineStates::find($branch_id);
          
          $price = $this->priceCalculation($cart, $selection, $child,$shipping_method);
          $styles = config('style.checkout');
/*
            if($state)
            {
                $styles = Arr::except($styles, ["order_total_gst"]);
             //   $gst = $price['order_total_gst'] / 2;
                $price['order_total_sgst'] = $price['order_total_sgst'] ;
                $price['order_total_cgst'] = $price['order_total_cgst'];
            }
            else {
*/
                $styles = Arr::except($styles, ["order_total_sgst", "order_total_cgst", "order_kfc_amount"]);
//            }
            $i = 1;
            foreach($styles as $key => $style)
            {
                if(array_key_exists($key, $price))
                {
                    $styles[$key]['order'] = $i;
                    $styles[$key]['value'] = config('app.def_currency_symbol')." ".(string) $price[$key];
                    $i++;
                }else if($key=="order_delivery_charge" || $key=="order_courier_charge") {
                    unset($styles[$key]);
                }
            }
        }
        return array_values($styles);
    }

    public function priceCalculation1($cart, $branch_id)
    {
        $cart_items = $cart['all_available_product_quality'];
        $notavailable = $cart['not_available_product_quality_in_48_hours'];
        $cart_all=$cart['all_product_in_48_hours'];
        return [
            'all_available_product_price' => $this->getPriceValues($cart_items, $branch_id, 1),
            'not_available_product_price_48_hours' => $this->getPriceValues($notavailable, $branch_id, 2),
            'all_product_price_48_hours' => $this->getPriceValues($cart_all, $branch_id, 3),
        ];
    }

    public function priceCalculation1_old($cart)
    {

        $all_product=array();
        $notavailable=array();
        $all_48=array();
        $basket_price = $total_gst = 0;
        $cart_all=$cart['all_available_product_quality'];
        $gst=0;
        $basket_price=0;
        $total_gst=0;
        $styles = config('style.checkout');
        foreach ($cart_all as $key => $itm) {


            $product = $itm['cart_product_id'];
            $branch = $itm['cart_branch_id'];
            $no = $itm['cart_order_qty'];
            $price = $this->getPrice($product, $branch);
            $gst = $this->getGst($product);
            $basket_price += $price['selling_price'] * $no;
            $total_gst += (($price['selling_price'] * $no) * $gst) / 100;
        }
       $delivery = 0;
       // $delivery = $this->getDeliveryCharge() ?? 0;
       if(count($cart_all))
       {

        foreach($styles as $key => $style)
        {
            $selling = round($basket_price, 2) ?? 0;
                ($key === "total")? $styles[$key]['value'] = "₹ ".(string)round($gst + $selling + $delivery, 2) ?? 0 : "";
                ($key === "subtotal")? $styles[$key]['value'] = "₹ ".(string)$selling = round($basket_price, 2) ?? 0 : "";
                ($key === "order_delivery_charge")? $styles[$key]['value'] = "₹ ".(string)$delivery = $selling ? $delivery : 0 : "";
                 ($key === "order_total_gst")? $styles[$key]['value'] = "₹ ".(string)$gst = round($total_gst, 2) ?? 0 : "";
                // ($key === "total") ? $styles[$key]['value'] = "₹ ".(string) $customer[$key] : "";

        }


       $all_product= array_values($styles);
    }



        $cart_all=$cart['not_available_product_quality_in_48_hours'];
        $gst=0;
        $basket_price=0;
        $total_gst=0;
        foreach ($cart_all as $key => $itm) {


            $product = $itm['cart_product_id'];
            $branch = $itm['cart_branch_id'];
            $no = $itm['cart_order_qty'];
            $price = $this->getPrice($product, $branch);
            $gst = $this->getGst($product);
            $basket_price += $price['selling_price'] * $no*0.4;
            $total_gst += (($price['selling_price'] * $no) * $gst) / 100;
        }
        $delivery = 0;
       // $delivery = $this->getDeliveryCharge() ?? 0;
       if(count($cart_all))
       {


        foreach($styles as $key => $style)
        {
            $selling = round($basket_price, 2) ?? 0;
                ($key === "total")? $styles[$key]['value'] = "₹ ".(string)round($gst + $selling + $delivery, 2) ?? 0 : "";
                ($key === "subtotal")? $styles[$key]['value'] = "₹ ".(string)$selling = round($basket_price, 2) ?? 0 : "";
                ($key === "order_delivery_charge")? $styles[$key]['value'] = "₹ ".(string)$delivery = $selling ? $delivery : 0 : "";
                 ($key === "order_total_gst")? $styles[$key]['value'] = "₹ ".(string)$gst = round($total_gst, 2) ?? 0 : "";
                // ($key === "total") ? $styles[$key]['value'] = "₹ ".(string) $customer[$key] : "";

        }
       $notavailable= array_values($styles);
    }



        $cart_all=$cart['all_product_in_48_hours'];
        $gst=0;
        $basket_price=0;
        $total_gst=0;
        foreach ($cart_all as $key => $itm) {


            $product = $itm['cart_product_id'];
            $branch = $itm['cart_branch_id'];
            $no = $itm['cart_order_qty'];
            $price = $this->getPrice($product,$branch);

            $gst = $this->getGst($product);

            $basket_price += $price['selling_price'] * $no*0.4;
            $total_gst += (($price['selling_price'] * $no) * $gst) / 100;
        }
        $delivery = 0;
       // $delivery = $this->getDeliveryCharge() ?? 0;
       if(count($cart_all))
       {

        foreach($styles as $key => $style)
        {
            $selling = round($basket_price, 2) ?? 0;
                ($key === "total")? $styles[$key]['value'] = "₹ ".(string)round($gst + $selling + $delivery, 2) ?? 0 : "";
                ($key === "subtotal")? $styles[$key]['value'] = "₹ ".(string)$selling = round($basket_price, 2) ?? 0 : "";
                ($key === "order_delivery_charge")? $styles[$key]['value'] = "₹ ".(string)$delivery = $selling ? $delivery : 0 : "";
                 ($key === "order_total_gst")? $styles[$key]['value'] = "₹ ".(string)$gst = round($total_gst, 2) ?? 0 : "";
                // ($key === "total") ? $styles[$key]['value'] = "₹ ".(string) $customer[$key] : "";

        }
        $all_48=array_values($styles);
       }




        return array('all_available_product_price'=>$all_product,'not_available_product_price_48_hours'=>$notavailable,'all_product_price_48_hours'=>$all_48);



    }

    private function getDeliveryCharge()
    {
        $delivaryCharge = AppConfig::where('brac_id', 1)
                            ->select('brac_delivery_charge')
                            ->first();
        return  $delivaryCharge['brac_delivery_charge'] ?? 0;
    }

    private function getPrice($product_id = '', $branch_id = '')
    {
        $price = BranchInventory::where('stit_id',$product_id)
                                ->where('branch_id',$branch_id)
                                ->select('mrp','selling_price')
                                ->first();
        return [
            "mrp" => $price['mrp'] ?? 0,
            "selling_price" => $price['selling_price'] ?? 0,
        ];
    }

    private function getGst($product_id = '')
    {
        $gst = StockItemMaster::where('stit_ID', $product_id)
                                ->select('stit_GST')
                                ->first();
        return $gst['stit_GST'] ?? 0;
    }

     /**
     * Save the delivery address for the order.
     *
     * @param \App\Models\Order $order
     * @return void
     */
    private function saveOrderAddress(&$order)
    {
        $primaryAddress = auth_user()->primaryAddress;

        $order->deliveryAddress()->create([
            'order_customer_id' => auth()->user()->cust_id,
            'order_contact_no'  => $primaryAddress->deli_contact_no,
            'order_house_no'    =>isset($primaryAddress->deli_house_no)?$primaryAddress->deli_house_no:' ' ,
            'order_house_name'  => $primaryAddress->deli_house_name,
            'order_city'        => $primaryAddress->deli_city,
            'order_post'        => $primaryAddress->deli_post,
            'order_state'       => $primaryAddress->deli_state,
            'order_pin'         => $primaryAddress->deli_delivery_pin,
            'order_latitude'    => $primaryAddress->deli_latitude,
            'order_longitude'   => $primaryAddress->deli_longitude,
            'order_address'    => $primaryAddress->deli_address,
            'order_address2'    => $primaryAddress->deli_address2,
            'order_country'     => config('app.operatingcountry'),
        ]);
    }

     /**
     * Preapare the item data for an order.
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function prepareOrderedItems($data)
    {
        return array_map(function ($item) {
            return [
                'item_product_id' => $item['cart_product_id'],
                'item_group_id' => $item['cart_group_id'],
                'item_order_qty' => $item['cart_order_qty'],
                'item_price' => ($item['cart_order_qty'] * ($selling = $this->getPrice($item['cart_product_id'], $item['cart_branch_id'])['selling_price'])),
                'item_retail_price' => $this->getPrice($item['cart_product_id'], $item['cart_branch_id'])['mrp'],
                'item_sales_price' => $selling,
                'item_subcategory_id' => $item['cart_subcategory_id'],
                'item_package_type_id' => $item['cart_package_type_id'],
                'item_is_taxable' => ($item['cart_is_taxable'])?$item['cart_is_taxable']:0,
                'item_cgst' => $item['cart_cgst'],
                'item_sgst' => $item['cart_sgst'],
                'item_igst' => $item['cart_igst'],
                'item_discount' => $item['cart_discount'],
                'item_sku_id' => $item['cart_sku_id'],
                'item_status' => $item['cart_status'],
            ];
        }, $data);
    }

    private function blockedOrderedItems($order, $cart,$order_branch_id)
    {
       foreach($cart as $cartItem)
       {
        BlockedItems::create([
                "item_id" => $cartItem['cart_product_id'],
                "branch_id"  => $order_branch_id,
                "count" => $cartItem['cart_order_qty'],
                "customer_id" => $order['order_customer_id'],
                "expiry" => now()->addMinutes(15),
        ]);
       }

    }

    private function getCompany($branch_id)
    {
        $company_id = CompanyBranch::where('br_Id', $branch_id)->first();
        return $company_id->comp_id ?? 0;
    }

    
}
