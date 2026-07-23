<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use BackOffice\Models\EmailsmsQueue;

class EmailTemplateController extends Controller
{
  
    public function __invoke($customer_id, $order_id)
    {
        $this->makeTemplate($customer_id, $order_id);
    }

    private function makeTemplate($customer_id, $order_id)
    {
        $order_details = Order::where('order_id', $order_id)
            ->with(['productItem' => function ($query) {
                $query->select($this->getOrderItems())
                    ->with(['image' => function ($q) {
                        $q->where('image_type', 1)
                            ->select('product_id', 'image_url', 'image_thumb_url');
                    }])
                    ->with(['products' => function ($query) {
                        $query->select('stit_ID', 'stit_SKU', 'stit_GST');
                    }]);
            }])
            ->select($this->getOrderFields())
            ->first();
        if ($order_details) {
            $order_details->order_count = OrderItem::where('customer_order_id', $order_id)->count();
            $order_details->customer_name = auth_user()->cust_customer_name;
            $order_details->customer_email = auth_user()->cust_email;
            $order_details->customer_mobile = auth_user()->cust_mobile;
            $order_details->total = $order_details->total;
           
            //$gst = round($order_details->order_total_gst / 2, 2);
            $order_details->cgst = $order_details->order_total_cgst;
            $order_details->sgst = $order_details->order_total_sgst;
            $order_details->saved_amount = $order_details->order_saved_amount;
            $order_details->order_mrp = empty($order_details->order_mrp) ? 1 : $order_details->order_mrp;
            $save_percentage = ($order_details->order_saved_amount / $order_details->order_mrp ?? 1) * 100;
            $order_details->saved_percentage = round($save_percentage, 2);
            $order_details->food_cess = $order_details->order_kfc_amount;
            $order_details->order_delivery_charge = $order_details->order_delivery_charge;
            $order_details->order_discount_amount = $order_details->order_discount_amount;
            $order_details->order_roundoff = $order_details->order_roundoff;
            $order_details->order_wallet_amount = $order_details->order_wallet_amount;
        }
        $json = json_encode($order_details);
        $view_data = view('order.email', ['data' => $json])->render();
        $insert = EmailsmsQueue::create([
            'is_sent' => 0,
            'receiver_id' => auth_user()->cust_email,
            'type' => 1,
            'is_sms' => 0,
            'extra_info' => "Order conformation",
            'text_message' => $view_data,
            'sender_id' => config('emailschedule.order_email'),
            'sender_name' => config('emailschedule.order_sender'),
        ]);
        return $insert;
    }

    private function getOrderFields()
    {
        return [
            'order_id',
            'order_order_id',
            'order_total_amount',
            'order_delivery_charge',
            'order_total_gst',
            'order_total_sgst',
            'order_total_cgst',
            'total',
            'order_saved_amount',
            'order_mrp',
            'order_kfc_amount',
            'order_discount_amount',
            'order_roundoff',
            'order_wallet_amount'
        ];
    }

    private function getOrderItems()
    {
        return [
            'item_id',
            'item_product_id',
            'customer_order_id',
            'item_order_qty',
            'item_price as items_total',
            'item_retail_price as mrp',
            'item_sales_price as selling',
        ];
    }

}
