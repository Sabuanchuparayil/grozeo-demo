<?php

namespace App\Partners;

use Illuminate\Support\Facades\DB;
use App\Models\{
    Order,
    TransferOrder
};

class BaseDeliveryRequest
{
    protected $orderHistory;
    protected $shippingConsignment;
    protected $packDetails;

    function __construct()
    {
        $this->packDetails = DB::table('retaline_transfer_order_pack_details');
        $this->shippingConsignment = DB::table('shipping_consignment');
    }
    public function createConsignmentRequest($fsto_id)
    {
        $outs = [
            'status'    => 'error',
            'data'      => [],
            'message'   => 'Order not found'
        ];
        $data = [];
        $package = TransferOrder::where('fsto_id', $fsto_id)->first();
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
                        $data['delivery_charge_et'] = @$orderData->order_delivery_charge_et;
                        $branchData = $orderData->branchDetails;

                        $shipmentExists = $this->shippingConsignment->where([
                            ['order_id', $orderData->order_order_id],
                            ['shipping_type', 'worldoptions'],
                        ])->whereNotIn('consignment_status', [4,5])->first();
                        $outs['message'] = 'Order already registered for shipping.';
                        if(empty($shipmentExists))
                        {
                            $data['fsto_id'] = $fsto_id;
                            $data['orderID'] = @$orderData->order_id;
                            $data['order_id'] = @$orderData->order_order_id;
                            $data['from_details'] = [
                                'store_name'    => $branchData->br_Name,
                                'address1'      => @$branchData->br_Address,
                                'address2'      => @$branchData->br_Address2,
                                'address3'      => @$branchData->br_Address3,
                                'city'          => $branchData->br_City,
                                'district'      => @$branchData->district->dst_Name,
                                'state'         => @$branchData->state->st_name,
                                'pincode'       => $branchData->br_pincode,
                                'phone'         => $branchData->br_Phone,
                                'email'         => $branchData->br_Email,
                                'latitude'      => $branchData->br_Lat,
                                'longitude'     => $branchData->br_Lng,
                            ];
                            $data['to_details'] = [
                                'name'          => @$orderData->deliveryAddress->order_customer_name,
                                'email'         => @$orderData->deliveryAddress->order_customer_email,
                                'phone'         => @$orderData->deliveryAddress->order_contact_no,
                                'address1'      => @$orderData->deliveryAddress->order_address,
                                'address2'      => @$orderData->deliveryAddress->order_address2,
                                'house_no'      => @$orderData->deliveryAddress->order_house_no,
                                'house_name'    => @$orderData->deliveryAddress->order_house_name,
                                'landmark'      => @$orderData->deliveryAddress->order_land_mark,
                                'city'          => @$orderData->deliveryAddress->order_city,
                                'state'         => @$orderData->deliveryAddress->order_state,
                                'pincode'       => @$orderData->deliveryAddress->order_pin,
                                'latitude'      => @$orderData->deliveryAddress->order_latitude,
                                'longitude'     => @$orderData->deliveryAddress->order_longitude,
                            ];
                            $data['exp_pickup'] = $package->fsto_updateon;
                            $data['package_details'] = [
                                'package'       => [],
                                'products'      => [],
                            ];

                            $packagedItems = $this->packDetails->where('rtopd_fstoId', $fsto_id)->get();
                            $x = 0;
                            $totalWeight = 0;
                            foreach ($packagedItems as $pItem)
                            {
                                $x++;
                                $packageMaster = DB::table('retaline_package_master')->where('rpckm_id', $pItem->rtopd_packaging)->first();
                                $Length = (@$packageMaster->rpckm_length > 0) ? $packageMaster->rpckm_length : @$pItem->rtopd_length;
                                $Breadth = (@$packageMaster->rpckm_breadth > 0) ? $packageMaster->rpckm_breadth : @$pItem->rtopd_breadth;
                                $Height  = (@$packageMaster->rpckm_height > 0) ? $packageMaster->rpckm_height : @$pItem->rtopd_height;
                                $data['package_details']['package'][] = [
                                    'id'            => $pItem->rtopd_id,
                                    'weight'        => floatval($pItem->rtopd_packetweigh),
                                    'length'        => @floatval($Length) ?? 1,
                                    'width'         => @floatval($Breadth) ?? 1,
                                    'height'        => @floatval($Height) ?? 1,
                                ];
                                $totalWeight += floatval($pItem->rtopd_packetweigh);
                            }
                            $p = 0;
                            foreach ($orderData->orderItems as $oitem)
                            {
                                $data['package_details']['products'][] = [
                                    'id'        => $oitem->item_id,
                                    "name"      => $oitem->item->stit_itemName,
                                    "price"     => $oitem->item_price,
                                    "discount"  => $oitem->order_item_seller_discount,
                                    "total"     => $oitem->item_sales_price,
                                    "qty"       => ($oitem->item_order_qty_scanned > 0) ? $oitem->item_order_qty_scanned : 1,
                                    "sku"       => $oitem->item->stit_SKU,
                                    "hsn"       => $oitem->item->stit_HSN_code,
                                    "tax"       => ($oitem->item_is_taxable > 0) ? ($oitem->item_cgst+$oitem->item_sgst+$oitem->item_igst+$oitem->item_kfc) : 0,
                                    "weight"    => @$oitem->item->stit_courierWt,
                                    "length"    => @$oitem->item->item_length,
                                    "width"     => @$oitem->item->item_breadth,
                                    "height"    => @$oitem->item->item_height,
                                ];
                                $p++;
                            }
                            $data['total'] = $orderData->total;
                            $data['totalWeight'] = $totalWeight;
                            $data['payment_mode'] = 'online';
                            $data['pending_amount'] = 0;
                            if(in_array($orderData->payment_mode, [1, 4, 7]))
                            {
                                $data['payment_mode'] = 'cod';
                                $data['pending_amount'] = $orderData->order_amount_payable;
                            }
                            if(($x > 0) && ($p > 0))
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