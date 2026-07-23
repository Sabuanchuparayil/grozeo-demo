<?php

namespace App\ExpressPartners\Uber;

use App\Models\{
    Order,
    Branch,
    TransferOrder
};
use Illuminate\Support\Facades\DB;

class UberConsignmentRequest
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
            $outs['message'] = 'Order not packed';
            if($package->fsto_status == 10)
            {
                $productData = $package->packedtransferorderDetails;
                $outs['message'] = 'No packed products found.';
                if($productData)
                {
                    $orderData = Order::where('order_id', $package->fstr_id)->first();
                    $outs['message'] = 'Branch not available.';
                    if(@$orderData->order_branch_id > 0)
                    {
                        $branchData = Branch::where('br_ID', $orderData->order_branch_id)->first();

                        $shipmentExists = $this->shippingConsignment->where([
                            ['order_id', $orderData->order_order_id],
                            ['shipping_type', 'uber'],
                        ])->whereNotIn('consignment_status', [4,5])->first();
                        $outs['message'] = 'Order already registered for shipping.';
                        if(empty($shipmentExists))
                        {
                            $store_addr = [@$branchData->br_Address, @$branchData->br_City, @$branchData->district->dst_Name, @$branchData->state->st_name, @$branchData->br_pincode];

                            $cust_note = [@$orderData->deliveryAddress->order_customer_name, @$orderData->deliveryAddress->order_house_no, @$orderData->deliveryAddress->order_house_name];
                            $streetAddr = @$orderData->deliveryAddress->order_house_name ? explode(',', $orderData->deliveryAddress->order_house_name) : "";
                            $streetAddr = @$streetAddr[1] ? trim($streetAddr[1]) :  "";
                            $addressLine = implode(', ', array_filter([@$orderData->deliveryAddress->order_address, @$orderData->deliveryAddress->order_address2]));
                            $addressLine = ($addressLine != "") ? $addressLine : $streetAddr;
                            $cust_addr = [@$addressLine, @$orderData->deliveryAddress->order_city, @$orderData->deliveryAddress->order_land_mark,  @$orderData->deliveryAddress->order_state, @$orderData->deliveryAddress->order_pin];

                            $data['order_id'] = @$orderData->order_order_id;
                            $storeName = preg_replace('/[^A-Za-z0-9\s]/', '', @$branchData->br_Name);
                            $data['from_details'] = [
                                'name'          => @$branchData->br_Name,
                                'address'       => implode(', ', array_filter($store_addr)),
                                'phone'         => $branchData->br_Phone,
                                'latitude'      => $branchData->br_Lat,
                                'longitude'     => $branchData->br_Lng,
                                'extStoreID'    => @$orderData->order_branch_id."_".str_replace(" ", "_", @$storeName)
                            ];
                            $data['to_details'] = [
                                'name'      => @$orderData->deliveryAddress->order_customer_name,
                                'phone'     => @$orderData->deliveryAddress->order_contact_no,
                                'email'     => @$orderData->deliveryAddress->order_customer_email,
                                'notes'     => implode(', ', array_filter($cust_note)),
                                'address'   => implode(', ', array_filter($cust_addr)),
                                'latitude'  => @$orderData->deliveryAddress->order_latitude,
                                'longitude' => @$orderData->deliveryAddress->order_longitude,
                            ];
                            $pickup_date = date('d/m/Y', strtotime($package->fsto_updateon));
                            $dateCheck = date('Y-m-d', strtotime($package->fsto_updateon));
                            $pickup_time = date('H:i', strtotime($package->fsto_updateon));
                            if(strtotime($package->fsto_updateon) < strtotime('now'))
                            {
                                $pickup_date = date("d/m/Y", time()+86400);
                                $dateCheck = date("Y-m-d", time()+86400);
                                $pickup_time = '11:00';
                            }
                            if (in_array(date('w', strtotime($dateCheck)), [0, 6]))
                            {
                                $pickup_date = (date('w', strtotime($dateCheck)) == 6) ? date("d/m/Y", strtotime('+3 days')) : date("d/m/Y", strtotime('+1 days'));
                                $pickup_time = '11:00';
                            }
                            $data['package_details'] = [
                                'products'      => [],
                                'pickup_date'   => $pickup_date,
                                'pickup_time'   => $pickup_time
                            ];
                            $p = 0;
                            $isRestaurant = 0;
                            foreach ($orderData->orderItems as $oitem)
                            {
                                $length = (@$oitem->item->item_length > 0) ? $oitem->item->item_length : 1;
                                $height = (@$oitem->item->item_height > 0) ? $oitem->item->item_height : 1;
                                $depth = (@$oitem->item->item_breadth > 0) ? $oitem->item->item_breadth : 1;
                                $weight = (@$oitem->item->item_weight > 0) ? $oitem->item->item_weight : 1;
                                $amount = intval(($oitem->item_price * 100));
                                $data['package_details']['products'][] = [
                                    "name"          => $oitem->item->stit_SKU,
                                    "quantity"      => $oitem->item_order_qty_scanned,
                                    "size"          => "medium",
                                    "price"         => $amount,
                                    "weight"        => $weight,
                                    "dimensions"    => [
                                        "length"        => $length,
                                        "height"        => $height,
                                        "depth"         => $depth,
                                    ]
                                ];
                                if($oitem->is_restaurant > 0)
                                {
                                    $isRestaurant++;
                                }
                                $p++;
                            }
                            $data['isRestaurant'] = $isRestaurant;
                            $data['payment_mode'] = 'online';
                            $data['pending_amount'] = 0;
                            if(($orderData->payment_mode == 1) || ($orderData->payment_mode == 4) || ($orderData->payment_mode == 7))
                            {
                                $data['payment_mode'] = 'cod';
                                $data['pending_amount'] = $orderData->order_amount_payable;
                            }
                            if($p > 0)
                            {
                                $outs['message'] = 'success';
                                $outs['status'] = 'success';
                                $outs['data'] = $data;
                            }
                        }
                    }
                }
            }
        }
        return $outs;
    }
}