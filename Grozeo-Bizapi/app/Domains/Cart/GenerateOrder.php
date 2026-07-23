<?php

namespace App\Domains\Cart;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\FinanceAutopostingValues;
use Illuminate\Http\Request;
use App\Http\Repositories\PostingRepository;
use Illuminate\Support\Facades\DB;
use App\BackOffice\BO_Order;
use App\Domains\BackOffice\BackOfficeOrderEntry;


class GenerateOrder
{
    protected $order;

    protected $boOrder;

    public function __construct()
    {
        $this->order = new Order;
        $this->boOrder = new BO_Order;
    }

    /**
     * Create the order
     *
     * @param \Illuminate\Database\Eloquent\Collection $data
     * @param integer $amount
     * @return \App\Models\Order
     */
    public static function create($data, $amount)
    {
        return (new static)->createOrder($data, $amount);
    }

    /**
     * Create the order
     *
     * @param \Illuminate\Database\Eloquent\Collection $data
     * @param integer $amount
     * @return \App\Models\Order
     */
    public function createOrder($data, $amount)
    {
        $order = null;
        DB::transaction(function () use (&$order, $data, $amount) {
            $order = $this->order->create(
                $this->prepareData($amount)
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
                'stat_order_status' => 'PROCESSING',
                'stat_order_description' => 'Awaiting Payment',
                'stat_date' => now(),
            ]);

            $this->saveOrderAddress($order);

            $order->orderItems()->saveMany(
                $this->prepareOrderedItems($data)
            );

            BackOfficeOrderEntry::create($order, $data);
        });

        return $order;
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
            'order_customer_id' => auth_user()->cust_id,
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
        ]);
    }

    /**
     * Prepare data for creating order.
     *
     * @param integer $amount
     * @return array
     */
    public function prepareData($amount)
    {
        return [
            'order_order_id' => GenerateOrderId::generate(),
            'order_customer_id' => auth_user()->cust_id,
            'order_total_amount' => $amount,
            'order_delivery_charge' => 0.0,
            'order_status' => 'PROCESSING',
            'order_total_gst' => 0.0,
            'order_branch_id' => null,
            'order_company_id' => null,
            'entry_RefId'   => (DB::select('SELECT UUID() as uuid')[0]->uuid)
        ];
    }

    /**
     * Preapare the item data for an order.
     *
     * @param \Illuminate\Database\Eloquent\Collection $data
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function prepareOrderedItems($data)
    {
        return $data->map(function($item){
            return new OrderItem([
                'item_product_id' => $item->cart_product_id,
                'item_order_qty' => $item->cart_order_qty,
                'item_price' => $item->cart_price,
                'item_retail_price' => $item->cart_retail_price,
                'item_sales_price' => $item->cart_sales_price,
                'item_subcategory_id' => $item->cart_subcategory_id,
                'item_package_type_id' => $item->cart_package_type_id,
                'item_is_taxable' => ($item->cart_is_taxable)? $item->cart_is_taxable:0,
                'item_cgst' => $item->cart_cgst,
                'item_sgst' => $item->cart_sgst,
                'item_igst' => $item->cart_igst,
                'item_discount' => $item->cart_discount,
                'item_sku_id' => $item->cart_sku_id,
                'item_status' => $item->cart_status,
            ]);
        });
    }
}
