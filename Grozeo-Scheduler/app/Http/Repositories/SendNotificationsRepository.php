<?php
namespace App\Http\Repositories;

use App\Models\Order;
use App\Helpers\HttpCurlCalls;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\ActivityLogRepository;

class SendNotificationsRepository
{
    public function __construct()
    {
        $this->curlCall = new HttpCurlCalls();
        $this->activtyLog = new ActivityLogRepository();
    }

    public function sendNotifications($orderID, $eventID)
    {
        $eventMaster = DB::table('finance_event_master')->where('event_ref_id', $eventID)->first();
        $order = Order::where('order_id', $orderID)
        ->whereHas('branchDetails.settings', function ($q) use ($eventID)
        {
            $q->where('type', 6)
            ->where('tp_type', 'events')
            ->whereRaw("FIND_IN_SET(?, tp_value)", [$eventID]);
        })->first();
        if(!$order)
        {
            return ;
        }
        $branchSettings = $this->transformSettings($order->branchDetails->settings->toArray());
        // return $branchSettings;
        if(empty($branchSettings))
        {
            return ;
        }
        $this->notify($order, $branchSettings, $eventMaster);
        return true;
    }


    private function notify($order, $settings, $eventMaster)
    {
        foreach ($settings as $key => $value)
        {
            switch ($key)
            {
                case 'API':
                    $this->sendAPINotification($order, $value, $eventMaster);
                    break;
                case 'SMS':
                    $this->sendSMSNotification($order, $value);
                    break;
                case 'email':
                    $this->sendEmailNotification($order, $value);
                    break;
                
                default:
                    return ;
                    break;
            }
        }
    }

    private function sendAPINotification($order, $creds, $eventMaster)
    {
        $url = $creds["url"];
        $body = $this->getRequestBody($order, $eventMaster);
    	$curlHeaders = [];

	    $header = (@$creds["header"] != "") ? json_decode($creds["header"], true) : [];
    	$jsonBody = json_encode($body);
        foreach ($header as $key => $value) {
            $curlHeaders[] = $key . ": " . $value;
        }
        $curlHeaders[] = "Content-Length: " . strlen($jsonBody);

        $callAPI = $this->curlCall->curlCall($url, $jsonBody, "POST", $curlHeaders, "all");

        $description = ["request" => $body, "response" => $callAPI];

        $this->activtyLog->insertActivityLog([
            "source"        => "API Notification",
            "User"          => "{$order->branchDetails->br_Name} ({$order->order_branch_id})",
            "Description"   => json_encode($description),
        ]);
    }

    private function sendSMSNotification($order, $creds)
    {
        // TODO: Implement SMS notification dispatch for merchant event notifications.
    }
    private function sendEmailNotification($order, $creds)
    {
        // TODO: Implement email notification dispatch for merchant event notifications.
    }
    private function transformSettings($settings)
    {
        $outs = [];
        $settings = array_values(
            array_filter($settings, function($item) {
                return ($item['type'] == 6);
            })
        );
        foreach ($settings as $set)
        {
            if($set["is_default"] == 0)
            {
                continue;
            }
            $name = $set['tp_name'];
            $tpType = $set['tp_type'];
            $tpValue = $set['tp_value'];

            $outs[$name][$tpType] = $tpValue;
        }
        return $outs;
    }
    private function getRequestBody($order, $eventMaster)
    {
        $response = [
            "order_id"          => $order->order_id,
            "order_number"      => $order->order_order_id,
            "order_date"        => $order->order_confirmed_on,
            "event_id"          => $eventMaster->id,
            "order_mrp"         => $order->order_mrp,
            "subtotal"          => $order->subtotal,
            "total"             => $order->total,
            "status"            => [
                "id"        => $order->status_id,
                "value"     => $order->orderStatus->customer_description
            ],
            "total_payable"     => (double)$order->order_amount_payable,
            "delivered_date"    => $order->order_delivered_date,
            "payment"           => [
                "mode"      => $this->getPaymentMode($order->payment_mode),
                "gateway"   => $order->order_payment_gateway
            ],
            "tracking"          => [
                "id"        => $order->order_trackID,
                "url"       => $order->order_trackURL
            ],
            "total_tax"     => $order->order_total_gst,
            "tax"               => $this->taxFields($order),
            "delivery_charge"   => [
                "amount"    => $order->order_delivery_charge,
                "cgst"      => $order->order_delivery_charge_cgst,
                "sgst"      => $order->order_delivery_charge_sgst,
                "igst"      => $order->order_delivery_charge_utgst,
                "utgst"     => $order->order_delivery_charge_igst
            ],
            "items"             => $this->getOrderItems($order),
            "customer"          => $this->customerDetails($order),
            "storeDetails"      => $this->storeDetails($order),
            "order_invoiceamt"  => $order->order_invoiceamt,
            "order_invoiceno"   => $order->order_invoiceno,
            "order_invoicedate" => $order->order_invoicedate
        ];
        return $response;
    }
    private function getPaymentMode($mode)
    {
        $paymentMode = "";
        switch ($mode)
        {
            case 1:
                $paymentMode = "Pay on Delivery (POD)";
                break;
            case 2:
                $paymentMode = "Online";
                break;
            case 3:
                $paymentMode = "Wallet Payment";
                break;
            case 4:
                $paymentMode = "Pay on Delivery with Wallet";
                break;
            case 5:
                $paymentMode = "Online with Wallet";
                break;
            case 6:
                $paymentMode = "Online on Delivery";
                break;
            case 7:
                $paymentMode = "Cash on delivery";
                break;
            
            default:
                $paymentMode = "";
                break;
        }
        return $paymentMode;
    }
    private function taxFields($order)
    {
        return [
            "tcs" => [
                "amount"    => $order->order_tcs,
                "cgst"      => $order->order_tcs_cgst,
                "sgst"      => $order->order_tcs_sgst,
                "igst"      => $order->order_tcs_igst,
                "utgst"     => $order->order_tcs_utgst
            ],
            "tdr" => [
                "amount"    => $order->order_tdr,
                "cgst"      => $order->order_tdr_cgst,
                "sgst"      => $order->order_tdr_sgst,
                "igst"      => $order->order_tdr_igst,
                "utgst"     => $order->order_tdr_utgst
            ],
            "tds" => $order->order_tds
        ];
    }
    private function getOrderItems($order)
    {
        $outs = [];
        foreach ($order->orderItems as $item)
        {
            $details = [
                "id"                    => $item->item->stit_ID,
                "sku"                   => $item->item->stit_SKU,
                "quantity"              => $item->item_order_qty,
                "packed_quantity"       => $item->item_order_qty_scanned,
                "status"                => $item->order_item_status,
                "original_sales_price"  => $item->orginal_sales_price,
                "coupon"                => NULL,
                "price"                 => [
                    "mrp"                   => $item->item_retail_price,
                    "selling_price"         => $item->item_sales_price,
                    "total"                 => $item->item_price,
                    "seller_discount"       => $item->order_item_seller_discount,
                    "tax"                   => [
                        "gst"       => $item->order_item_gst,
                        "cgst"      => $item->order_item_cgst,
                        "sgst"      => $item->order_item_sgst,
                        "igst"      => $item->order_item_igst,
                        "utgst"     => $item->order_item_ugst
                    ],
                ],
            ];
            if($item->item_coupon_code)
            {
                $details["coupon"] = [
                    "code"      => $item->item_coupon_code,
                    "discount"  => $item->item_discount
                ];
            }
            $outs[] = $details;
        }
        return $outs;
    }
    private function customerDetails($order)
    {
        $customer = $order->customer;
        $address = $order->deliveryAddress;
        return [
            "id"        => $customer->cust_id,
            "name"      => $customer->cust_customer_name,
            "phone"     => $customer->cust_mobile,
            "email"     => $customer->cust_email,
            "address"   => [
                "name"          => $address->order_customer_name,
                "phone"         => $address->order_contact_no,
                "email"         => $address->order_customer_email,
                "house_no"      => $address->order_house_no,
                "house_name"    => $address->order_house_name,
                "address1"      => $address->order_address,
                "address2"      => $address->order_address2,
                "landmark"      => $address->order_land_mark,
                "city"          => $address->order_city,
                "state"         => $address->order_state,
                "pincode"       => $address->order_post
            ]
        ];
    }
    private function storeDetails($order)
    {
        $branch = $order->branchDetails;
        return [
            "id"        => $branch->br_ID,
            "name"      => $branch->br_Name,
            "phone"     => $branch->br_Phone,
            "email"     => $branch->br_Email,
            "address"   => [
                "address"   => $branch->br_Address,
                "address2"  => $branch->br_Address2,
                "address3"  => $branch->br_Address3,
                "city"      => $branch->br_City,
                "district"  => $branch->district->dst_Name,
                "state"     => $branch->state->st_name,
                "pincode"   => $branch->br_pincode,
            ]
        ];
    }
}
