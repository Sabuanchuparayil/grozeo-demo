<?php

namespace App\ExpressPartners\Tookan;

use App\Models\{
    Order,
    Branch,
    TransferOrder
};
use Illuminate\Support\Facades\DB;

class TookanConsignmentRequest
{
    protected $orderHistory;
    protected $shippingConsignment;
    protected $packDetails;

    function __construct()
    {
        $this->packDetails = DB::table('retaline_transfer_order_pack_details');
        $this->shippingConsignment = DB::table('shipping_consignment');
    }

    public function getConsignmentData($fstoID)
    {
        $outs = [
            'status'    => 'error',
            'data'      => [],
            'message'   => 'Order not found'
        ];
        $data = [];
        $package = TransferOrder::where('fsto_id', $fstoID)->first();
        if($package)
        {
            $data['fsto_id'] = $fstoID;
            $outs['message'] = 'Order not packed';
            if($package->fsto_status == 10)
            {
                $productData = $package->packedtransferorderDetails;
                $outs['message'] = 'No packed products found.';
                if($productData)
                {
                    $orderData = Order::where('order_id', $package->fstr_id)->with('orderItems')->first();
                    $outs['message'] = 'Branch not available.';
                    if(@$orderData->order_branch_id > 0)
                    {
                        $branchData = Branch::where('br_ID', $orderData->order_branch_id)->first();
                        $settings = $branchData->settings->toArray();
                        $data['api_key'] = "";
                        if($settings)
                        {
                            $apiKeyData = array_values(array_filter($settings, function($item) {
                                return ($item['tp_name'] == "tookan" && $item['tp_type'] == "apiKey" && $item['is_default'] == 1);
                            }));
                            $data['api_key'] = @$apiKeyData[0]['tp_value'];
                        }

                        $shipmentExists = $this->shippingConsignment->where([
                            ['order_id', $orderData->order_order_id],
                            ['shipping_type', 'uber'],
                        ])->whereNotIn('consignment_status', [4,5])->first();
                        $outs['message'] = 'Order already registered for shipping.';
                        if(empty($shipmentExists))
                        {
                            $outs['status'] = 'success';
                            $outs['message'] = "";
                            $store_addr = [@$branchData->br_Address, @$branchData->br_City, @$branchData->district->dst_Name, @$branchData->state->st_name, @$branchData->br_pincode];

                            $cust_addr = [@$orderData->deliveryAddress->order_customer_name, @$orderData->deliveryAddress->order_house_no, @$orderData->deliveryAddress->order_house_name, @$orderData->deliveryAddress->order_address, @$orderData->deliveryAddress->order_land_mark, @$orderData->deliveryAddress->order_city, @$orderData->deliveryAddress->order_state, @$orderData->deliveryAddress->order_pin];

                            $data['orderID'] = @$orderData->order_id;
                            $data['order_id'] = @$orderData->order_order_id;
                            $data['from_details'] = [
                                'name'          => @$branchData->br_Name,
                                'email'         => @$branchData->br_Email,
                                'address'       => implode(', ', array_filter($store_addr)),
                                'phone'         => $branchData->br_Phone,
                                'latitude'      => $branchData->br_Lat,
                                'longitude'     => $branchData->br_Lng
                            ];
                            $data['to_details'] = [
                                'name'      => @$orderData->deliveryAddress->order_customer_name,
                                'email'     => @$orderData->deliveryAddress->order_customer_email,
                                'phone'     => @$orderData->deliveryAddress->order_contact_no,
                                'address'   => implode(', ', array_filter($cust_addr)),
                                'latitude'  => @$orderData->deliveryAddress->order_latitude,
                                'longitude' => @$orderData->deliveryAddress->order_longitude,
                            ];
                            $hasRestaurant = array_column($orderData->orderItems->toArray(), 'is_restaurant');
                            $data['hasRestaurant'] = (in_array(1, $hasRestaurant)) ? 1 : 0;
                            $data['pickup_date'] = date('Y-m-d H:i:s');
                            $timeAdd = (in_array(1, $hasRestaurant)) ? config("expresspartners.tookan.hyperLocalDiff") : config("expresspartners.tookan.lastMileDiff");
                            $data['delivery_date'] = date("Y-m-d H:i:s", time() + $timeAdd);
                            $data['order_slot_id'] = $orderData->order_slot_id;
                            if(@$orderData->order_slot_id > 0)
                            {
                                $slotData = DB::table('retaline_branch_delivery_slot')->where('rbds_id', $orderData->order_slot_id)->first();
                                $data['delivery_date'] = @$orderData->order_slot_date." ".@$slotData->rbds_time_from;
                            }
                            $data['delivery_charge'] = @$orderData->order_delivery_charge_et;
                            $data['payment_mode'] = 'PREPAID';
                            if(($orderData->payment_mode == 1) || ($orderData->payment_mode == 4) || ($orderData->payment_mode == 7))
                            {
                                $data['payment_mode'] = 'CASH ON DELIVERY';
                                $data['pending_amount'] = $orderData->order_amount_payable;
                            }
                        }
                    }
                }
            }
        }
        $outs['data'] = $data;
        return $outs;
    }
}