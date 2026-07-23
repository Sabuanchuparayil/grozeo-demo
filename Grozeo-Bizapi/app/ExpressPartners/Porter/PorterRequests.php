<?php

namespace App\ExpressPartners\Porter;

use Illuminate\Support\Facades\DB;
use App\Models\{
    Order,
    Branch
};
use BackOffice\Models\TransferOrder;

class PorterRequests
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
                        $branchData = Branch::where('br_ID', $orderData->order_branch_id)->first();

                        $shipmentExists = $this->shippingConsignment->where([
                            ['order_id', $orderData->order_order_id],
                            ['shipping_type', 'porter'],
                        ])->whereNotIn('consignment_status', [4,5])->first();
                        $outs['message'] = 'Order already registered for shipping.';
                        if(empty($shipmentExists))
                        {
                            $store_addr = (@$branchData->br_Address != "") ? [$branchData->br_Address] : [@$branchData->br_City, @$branchData->district->dst_Name, @$branchData->state->st_name];

                            $cust_addr1 = (@$orderData->deliveryAddress->order_address != "") ? [$orderData->deliveryAddress->order_address] : [@$orderData->deliveryAddress->order_house_no, @$orderData->deliveryAddress->order_house_name];
                            $cust_addr2 = (@$orderData->deliveryAddress->order_address2 != "") ? [$orderData->deliveryAddress->order_address2] : [@$orderData->deliveryAddress->order_land_mark, @$orderData->deliveryAddress->order_city, @$orderData->deliveryAddress->order_state];
                            $data['fsto_id'] = $fsto_id;
                            $data['status_id'] = $fsto_id;
                            $data['orderID'] = @$orderData->order_id;
                            $data['order_id'] = @$orderData->order_order_id;
                            $data['invoiceNo'] = @$orderData->order_invoiceno;
                            $data['invoiceDate'] = @$orderData->order_invoicedate;
                            $data['invoiceAmount'] = @$orderData->order_invoiceamt;
                            $data['from_details'] = [
                                'name'      => $branchData->br_Name,
                                'phone'     => $branchData->br_Phone,
                                'email'     => $branchData->br_Email,
                                'address'   => implode(', ', array_filter($store_addr)),
                                'city'      => $branchData->br_City,
                                'district'  => @$branchData->district->dst_Name,
                                'state'     => @$branchData->state->st_name,
                                'country'   => config('expresspartners.porter.country'),
                                'pincode'   => $branchData->br_pincode,
                                'latitude'  => $branchData->br_Lat,
                                'longitude' => $branchData->br_Lng,
                                'tin'       => @$branchData->br_GST
                            ];
                            $cust_addr = implode(', ', array_filter($cust_addr1)).", ".implode(', ', array_filter($cust_addr2));
                            $data['to_details'] = [
                                'name'      => @$orderData->deliveryAddress->order_customer_name,
                                'email'     => @$orderData->deliveryAddress->order_customer_email,
                                'phone'     => @$orderData->deliveryAddress->order_contact_no,
                                'address'   => $cust_addr,
                                'landmark'  => @$orderData->deliveryAddress->order_land_mark,
                                'city'      => @$orderData->deliveryAddress->order_city,
                                'state'     => @$orderData->deliveryAddress->order_state,
                                'country'   => config('expresspartners.porter.country'),
                                'pincode'   => @$orderData->deliveryAddress->order_pin,
                                'latitude'  => @$orderData->deliveryAddress->order_latitude,
                                'longitude' => @$orderData->deliveryAddress->order_longitude,
                            ];
                            $pickup_date = date('Y-m-d', strtotime($package->fsto_updateon));
                            $pickup_time = date('H:i:s', strtotime($package->fsto_updateon));
                            if(strtotime($package->fsto_updateon) < strtotime('now'))
                            {
                                $pickup_date = date("Y-m-d", time()+86400);
                                $pickup_time = '11:00:00';
                            }
                            if (in_array(date('w', strtotime($pickup_date)), [0, 6]))
                            {
                                $pickup_date = (date('w', strtotime($pickup_date)) == 6) ? date("Y-m-d", strtotime('+3 days')) : date("Y-m-d", strtotime('+1 days'));
                                $pickup_time = '11:00:00';
                            }
                            $data['from_details']['pickup_date'] = $pickup_date;
                            $data['from_details']['pickup_time'] = $pickup_time;
                            $data['package_details'] = [
                                'package'       => [],
                                'products'      => []
                            ];

                            $packagedItems = $this->packDetails->where('rtopd_fstoId', $fsto_id)->get();
                            $x = 0;
                            foreach ($packagedItems as $pItem)
                            {
                                $x++;
                                $packageMaster = DB::table('retaline_package_master')->where('rpckm_id', $pItem->rtopd_packaging)->first();
                                $Length = (@$packageMaster->rpckm_length > 0) ? $packageMaster->rpckm_length : @$pItem->rtopd_length;
                                $Breadth = (@$packageMaster->rpckm_breadth > 0) ? $packageMaster->rpckm_breadth : @$pItem->rtopd_breadth;
                                $Height  = (@$packageMaster->rpckm_height > 0) ? $packageMaster->rpckm_height : @$pItem->rtopd_height;
                                $Weight = ($pItem->rtopd_packetweigh > 0) ? $pItem->rtopd_packetweigh * 1000 : 1000;
                                $data['package_details']['package'][] = [
                                    'length'        => (@floatval($Length) > 0) ? floatval($Length) : 1,
                                    'width'         => (@floatval($Breadth) > 0) ? floatval($Breadth) : 1,
                                    'weight'        => @floatval($Weight),
                                    'height'        => (@floatval($Height) > 0) ? floatval($Height) : 1,
                                ];
                            }
                            $p = 0;
                            foreach ($orderData->orderItems as $oitem)
                            {
                                $data['package_details']['products'][] = [
                                    "sku"           => $oitem->item->stit_SKU,
                                    "price"         => $oitem->item_sales_price,
                                    "weight"        => (@$oitem->item->stit_courierWt > 0) ? $oitem->item->stit_courierWt * 1000 : 1000,
                                    "hsn"           => $oitem->item->stit_HSN_code,
                                    "qty"           => $oitem->item_order_qty_scanned,
                                    "description"   => $oitem->item->stit_Description,
                                    "name"          => $oitem->item->stit_itemName,
                                    "tax"           => ($oitem->item_is_taxable > 0) ? ($oitem->item_cgst+$oitem->item_sgst+$oitem->item_igst+$oitem->item_kfc) : 0,
                                    'length'        => 0,
                                    'width'         => 0,
                                    'height'        => 0,
                                ];
                                $p++;
                            }
                            $data['payment_mode'] = 'PREPAID';
                            $data['pending_amount'] = 0;
                            $data["currency"] = "";
                            $data['orderDate'] = $orderData->order_confirm_date;
                            if(($orderData->payment_mode == 1) || ($orderData->payment_mode == 4) || ($orderData->payment_mode == 7))
                            {
                                $data['payment_mode'] = 'COD';
                                $data["currency"] = config('expresspartners.porter.currency');
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