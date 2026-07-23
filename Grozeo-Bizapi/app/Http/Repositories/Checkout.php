<?php

namespace App\Http\Repositories;

use App\Models\Order;
use App\Models\BlockedItems;
use App\Models\FinanceAutopostingValues;
use Illuminate\Http\Request;
use App\Http\Repositories\PostingRepository;
use Illuminate\Support\Facades\DB;
use App\Domains\Paytm\PaytmPayment;
use App\Domains\Cart\GenerateOrderId;
use Illuminate\Support\Facades\Log;

class Checkout
{
    private $order;

    private $blockedItems;

    private $payment;

    public function __construct()
    {
        $this->order = new Order;

        $this->payment = new PaytmPayment;

        $this->blockedItems = new BlockedItems();
    }

    public static function customerCheckout($cart)
    {


        return (new static)->customerOrder($cart);
    }

    private function customerOrder($cart)
    {
        $order = null;
        $paytm = null;

        DB::transaction(function () use($cart, &$order, &$paytm) {

        $order = $this->order->create(
            $this->prepareData($cart)
            );
        

        /* $autoPosting = FinanceAutopostingValues::create([
            'order_id'          => $order->order_id,
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

        $order->status()->create([
                'stat_order_id'=>"",
                'stat_order_status' => "PROCESSING",
                'stat_order_description' => 'Awaiting Payment',
                'stat_date' => now(),
            ]);

        $this->saveOrderAddress($order);

        $orderItems = $order->productItem()->createMany(
            $this->prepareOrderedItems($cart)
        );

    $order ? $this->blockedOrderedItems($order['order_order_id'], $cart,$order['order_branch_id']) : false;
    $paytm = $this->createChecksum($order['order_order_id'], $order->total ?? $order->order_total_amount ?? 0);

     });
        return $paytm;
    }

     /**
     * Prepare data for creating order.
     *
     * @param
     * @return array
     */
    private function prepareData($cart)
    {
        $orderTotal = 0;
        foreach ($cart as $item) {
            $orderTotal += ($item['cart_sales_price'] ?? 0) * ($item['cart_order_qty'] ?? 1);
        }

        return [
            'order_order_id' => GenerateOrderId::generate(),
            'order_customer_id' => auth()->user()->cust_id,
            'order_total_amount' => $orderTotal,
            'order_delivery_charge' => 0.0,
            'order_total_gst' => 0.0,
            'order_branch_id' => null,
            'order_company_id' => null,
            'entry_RefId'   => (DB::select('SELECT UUID() as uuid')[0]->uuid)
        ];
    }

    /**
     * Save the delivery address for the order.
     *
     * @param \App\Models\Order $order
     * @return void
     */
    private function saveOrderAddress(&$order)
    {

        $primaryAddress = auth()->user()->primaryAddress;

        $order->deliveryAddress()->create([
            'order_id'          =>$order['order_order_id'],
            'order_customer_id' => auth()->user()->cust_id,
            'order_contact_no'  => $primaryAddress->deli_contact_no,
            'order_house_no'    => $primaryAddress->deli_house_no,
            'order_house_name'  => $primaryAddress->deli_house_name,
            'order_city'        => $primaryAddress->deli_city,
            'order_post'        => $primaryAddress->deli_post,
            'order_state'       => $primaryAddress->deli_state,
            'order_pin'         => $primaryAddress->deli_delivery_pin,
            'order_address'     => $primaryAddress->deli_address,
            'order_address2'    => $primaryAddress->deli_address2,
            'order_country'     => config('app.operatingcountry'),
            'order_land_mark'   =>''
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
                'item_order_qty' => $item['cart_order_qty'],
                'item_price' => $item['cart_price'],
                'item_retail_price' => $item['cart_retail_price'],
                'item_sales_price' => $item['cart_sales_price'],
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
        $this->blockedItems->create([
                "customer_id"=> auth()->user()->cust_id,
                "item_id" => $cartItem['cart_product_id'],
                "branch_id"  => $order_branch_id ,
                "count" => $cartItem['cart_order_qty'],
                "order_id" => $order,
                "expiry" => now()->addMinutes(15),
        ]);
       }

    }

    private function createChecksum($orderId, $txnAmount = 0)
    {
        $paytmParams = [];
        $paytmParams["MID"] = config('paytm.merchant_mid');
        $paytmParams["ORDER_ID"] = $orderId;
        $paytmParams["CUST_ID"] = auth()->user()->cust_customer_id;
        $paytmParams["MOBILE_NO"] = auth()->user()->cust_mobile;
        $paytmParams["EMAIL"] = auth()->user()->cust_email;
        $paytmParams["CHANNEL_ID"] = "WAP";
        $paytmParams["TXN_AMOUNT"] = $txnAmount;
        $paytmParams["WEBSITE"] = "WEBSTAGING";
        $paytmParams["INDUSTRY_TYPE_ID"] = "Retail";
        $paytmParams["CALLBACK_URL"] = config('paytm.callback_url') . "?ORDER_ID={$orderId}";
        $paytmChecksum = $this->payment->getChecksumFromArray($paytmParams, config('paytm.merchant_key'));

        return [
            'checksum' => $paytmChecksum,
            'order_id' => $orderId,
            'cust_id'  => $paytmParams["CUST_ID"],
            'mobile_no' => $paytmParams["MOBILE_NO"],
            'email' => $paytmParams["EMAIL"],
            'txn_amount' => $paytmParams["TXN_AMOUNT"],
            'callback_url' => $paytmParams["CALLBACK_URL"],
        ];
    }



}
