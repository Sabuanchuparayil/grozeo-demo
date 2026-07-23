<?php
namespace App\PackingPartners\Petpooja;

use Illuminate\Support\Facades\DB;
use App\Models\{
    Order,
    Branch
};

class PetpoojaRequest
{
    public function __construct() {}

    public function createPackingRequest($orderID)
    {
        $outs = [
            'status'    => 'error',
            'data'      => [],
            'message'   => 'Order not found'
        ];
        $data = [];
        $orderData = Order::where('order_id', $orderID)->first();
        $outs['message'] = 'Branch not available.';
        if(@$orderData->order_branch_id > 0)
        {
            $branchData = Branch::where('br_ID', $orderData->order_branch_id)->first();
            $settings = $branchData->settings->toArray();
            $data['appKey'] = "";
            $data['secretKey'] = "";
            $data['accessToken'] = "";
            $data['orderType'] = "";
            $data['restPerpTime'] = "";
            $data['groPrepTime'] = "";
            $data['restID'] = "";
            if($settings)
            {
                $appKeyData = array_values(array_filter($settings, function($item) {
                    return ($item['tp_name'] == "petpooja" && $item['tp_type'] == "appKey" && $item['is_default'] == 1);
                }));
                $data['appKey'] = @$appKeyData[0]['tp_value'];
                $secretKeyData = array_values(array_filter($settings, function($item) {
                    return ($item['tp_name'] == "petpooja" && $item['tp_type'] == "secretKey" && $item['is_default'] == 1);
                }));
                $data['secretKey'] = @$secretKeyData[0]['tp_value'];
                $accessTokenData = array_values(array_filter($settings, function($item) {
                    return ($item['tp_name'] == "petpooja" && $item['tp_type'] == "accessToken" && $item['is_default'] == 1);
                }));
                $data['accessToken'] = @$accessTokenData[0]['tp_value'];
                $orderTypeData = array_values(array_filter($settings, function($item) {
                    return ($item['tp_name'] == "petpooja" && $item['tp_type'] == "orderType" && $item['is_default'] == 1);
                }));
                $data['orderType'] = @$orderTypeData[0]['tp_value'];
                $restPerpTimeData = array_values(array_filter($settings, function($item) {
                    return ($item['tp_name'] == "petpooja" && $item['tp_type'] == "restPerpTime" && $item['is_default'] == 1);
                }));
                $data['restPerpTime'] = @$restPerpTimeData[0]['tp_value'];
                $groPrepTimeData = array_values(array_filter($settings, function($item) {
                    return ($item['tp_name'] == "petpooja" && $item['tp_type'] == "groceryPrepTime" && $item['is_default'] == 1);
                }));
                $data['groPrepTime'] = @$groPrepTimeData[0]['tp_value'];
                $restIDData = array_values(array_filter($settings, function($item) {
                    return ($item['tp_name'] == "petpooja" && $item['tp_type'] == "storeID" && $item['is_default'] == 1);
                }));
                $data['restID'] = @$restIDData[0]['tp_value'];
            }
            $store_addr = [@$branchData->br_Address, @$branchData->br_City, @$branchData->district->dst_Name, @$branchData->state->st_name, @$branchData->br_pincode];

            $cust_addr = [@$orderData->deliveryAddress->order_customer_name, @$orderData->deliveryAddress->order_house_no, @$orderData->deliveryAddress->order_house_name, @$orderData->deliveryAddress->order_address, @$orderData->deliveryAddress->order_land_mark, @$orderData->deliveryAddress->order_city, @$orderData->deliveryAddress->order_state, @$orderData->deliveryAddress->order_pin];

            $data['order_id'] = @$orderData->order_order_id;
            $data['from_details'] = [
                'name'      => $branchData->br_Name,
                'address'   => implode(', ', array_filter($store_addr)),
                'city'      => $branchData->br_City,
                'pincode'   => $branchData->br_pincode,
                'phone'     => $branchData->br_Phone,
                'email'     => $branchData->br_Email,
                'latitude'  => $branchData->br_Lat,
                'longitude' => $branchData->br_Lng
            ];
            $data['to_details'] = [
                'name'      => @$orderData->deliveryAddress->order_customer_name,
                'email'     => @$orderData->deliveryAddress->order_customer_email,
                'address'   => implode(', ', array_filter($cust_addr)),
                'phone'     => @$orderData->deliveryAddress->order_contact_no,
                'city'      => @$orderData->deliveryAddress->order_city,
                'pincode'   => @$orderData->deliveryAddress->order_pin,
                'latitude'  => @$orderData->deliveryAddress->order_latitude,
                'longitude' => @$orderData->deliveryAddress->order_longitude
            ];
            $pickup = date("Y-m-d H:i:s", strtotime('+30 minutes'));
            $data['package_details'] = [
                'products'      => [],
                'pickup'        => $pickup
            ];
            $data['delivery_charge'] = @$orderData->order_delivery_charge_et;
            $data['delivery_charge_tax'] = @$orderData->order_delivery_charge_gst;
            $data['notes'] = @$orderData->order_notes;
            $data['discount_amount'] = @$orderData->order_discount_amount;
            $data['discount_add_total'] = @$orderData->order_discount_add_total;
            $data['total'] = @$orderData->total;
            $data['tax'] = @$orderData->order_total_gst;

            foreach ($orderData->orderItems as $oitem)
            {
                $packageData = [
                    'id'                => $oitem->item_id,
                    "name"              => $oitem->item->stit_itemName,
                    "gst_liability"     => "vendor",
                    "item_discount"     => $oitem->item_discount,
                    "price"             => $oitem->item_price,
                    "quantity"          => $oitem->item_order_qty,
                    "description"       => "",
                    "variation_name"    => "",
                    "variation_id"      => "",
                    "AddonItem"         => [
                        "details"   => []
                    ],
                ];

                $packageData["item_tax"] = [];
                if($oitem->order_item_cgst > 0)
                {
                    $packageData["item_tax"][] = [
                        'id'        => "{$oitem->item_id}_cgst",
                        'name'      => "CGST",
                        'amount'    => $oitem->order_item_cgst
                    ];
                }
                if($oitem->order_item_sgst > 0)
                {
                    $packageData["item_tax"][] = [
                        'id'        => "{$oitem->item_id}_sgst",
                        'name'      => "SGST",
                        'amount'    => $oitem->order_item_sgst
                    ];
                }
                if($oitem->order_item_igst > 0)
                {
                    $packageData["item_tax"][] = [
                        'id'        => "{$oitem->item_id}_igst",
                        'name'      => "IGST",
                        'amount'    => $oitem->order_item_igst
                    ];
                }
                if($oitem->order_item_ugst > 0)
                {
                    $packageData["item_tax"][] = [
                        'id'        => "{$oitem->item_id}_ugst",
                        'name'      => "UGST",
                        'amount'    => $oitem->order_item_ugst
                    ];
                }
                $data['package_details']['products'][] = $packageData;
            }
            $data['Tax'] = [];
            if($orderData->order_total_cgst > 0)
            {
                $data['Tax'][] = [
                    'id'                    => "{$orderData->order_order_id}_cgst",
                    'title'                 => "CGST",
                    "type"                  => "F",
                    "price"                 => "0",
                    "tax"                   => $orderData->order_total_cgst,
                    "restaurant_liable_amt" => "0.00"
                ];
            }
            if($orderData->order_total_sgst > 0)
            {
                $data['Tax'][] = [
                    'id'                    => "{$orderData->order_order_id}_sgst",
                    'title'                 => "SGST",
                    "type"                  => "F",
                    "price"                 => "0",
                    "tax"                   => $orderData->order_total_sgst,
                    "restaurant_liable_amt" => "0.00"
                ];
            }
            if($orderData->order_total_igst > 0)
            {
                $data['Tax'][] = [
                    'id'                    => "{$orderData->order_order_id}_igst",
                    'title'                 => "IGST",
                    "type"                  => "F",
                    "price"                 => "0",
                    "tax"                   => $orderData->order_total_igst,
                    "restaurant_liable_amt" => "0.00"
                ];
            }
            if($orderData->order_total_utgst > 0)
            {
                $data['Tax'][] = [
                    'id'                    => "{$orderData->order_order_id}_utgst",
                    'title'                 => "UTGST",
                    "type"                  => "F",
                    "price"                 => "0",
                    "tax"                   => $orderData->order_total_utgst,
                    "restaurant_liable_amt" => "0.00"
                ];
            }
            if($orderData->order_cess > 0)
            {
                $data['Tax'][] = [
                    'id'                    => "{$orderData->order_order_id}_cess",
                    'title'                 => "CESS",
                    "type"                  => "F",
                    "price"                 => "0",
                    "tax"                   => $orderData->order_cess,
                    "restaurant_liable_amt" => "0.00"
                ];
            }
            $data['Discount'] = [[
                'id'                    => "{$orderData->order_order_id}_discount",
                'title'                 => "Discount",
                "type"                  => "F",
                "price"                 => $orderData->order_discount_amount,
            ]];
            $hasRestaurant = array_column($orderData->orderItems->toArray(), 'is_restaurant');
            $data['hasRestaurant'] = (in_array(1, $hasRestaurant)) ? 1 : 0;
            $data['payment_mode'] = 'ONLINE';
            $data['pending_amount'] = 0;
            if(($orderData->payment_mode == 1) || ($orderData->payment_mode == 4) || ($orderData->payment_mode == 7))
            {
                $data['payment_mode'] = 'COD';
                $data['pending_amount'] = $orderData->order_amount_payable;
            }
            $outs['message'] = 'success';
            $outs['status'] = 'success';
            $outs['data'] = $data;
        }
        return $outs;
    }
}