<?php

namespace App\CourierPartners\Shipyaari;

use Illuminate\Support\Facades\DB;
use BackOffice\Models\TransferOrder;
use App\Models\{
    Order,
    Branch
};

class ShipyaariRequests
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
                        $branchData = Branch::where('br_ID', $orderData->order_branch_id)->first();

                        $shipmentExists = $this->shippingConsignment->where([
                            ['order_id', $orderData->order_order_id],
                            ['shipping_type', 'shipyaari'],
                        ])->whereNotIn('consignment_status', [4,5])->first();
                        $outs['message'] = 'Order already registered for shipping.';
                        if(empty($shipmentExists))
                        {
                            $store_addr1 = @$branchData->br_Address;
                            $store_addr2 = [@$branchData->br_City, @$branchData->district->dst_Name, @$branchData->state->st_name];

                            $cust_addr1 = [@$orderData->deliveryAddress->order_house_no, @$orderData->deliveryAddress->order_house_name, @$orderData->deliveryAddress->order_address];
                            $cust_addr2 = [@$orderData->deliveryAddress->order_land_mark, @$orderData->deliveryAddress->order_city, @$orderData->deliveryAddress->order_state];

                            $data['order_id'] = @$orderData->order_order_id;
                            $data['from_details'] = [
                                'company_name'      => $branchData->br_Name,
                                'address1'          => $store_addr1 ?? implode(', ', array_filter($store_addr2)),
                                'address2'          => implode(', ', array_filter($store_addr2)),
                                'city'              => $branchData->br_City,
                                'pincode'           => $branchData->br_pincode,
                                'phone'             => $branchData->br_Phone,
                                'email'             => $branchData->br_Email,
                            ];
                            $data['to_details'] = [
                                'customer_name'     => @$orderData->deliveryAddress->order_customer_name,
                                'customer_email'    => @$orderData->deliveryAddress->order_customer_email,
                                'customer_phone'    => @$orderData->deliveryAddress->order_contact_no,
                                'customer_city'     => @$orderData->deliveryAddress->order_city,
                                'address1'          => implode(', ', array_filter($cust_addr1)),
                                'address2'          => implode(', ', array_filter($cust_addr2)),
                                'pincode'           => @$orderData->deliveryAddress->order_pin
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
                                'package'		=> [],
                                'products'		=> [],
                                'pickup_date'   => $pickup_date,
                                'pickup_time'   => $pickup_time
                            ];

                            $packagedItems = $this->packDetails->where('rtopd_fstoId', $fsto_id)->get();
                            $x = 0;
                            foreach ($packagedItems as $pItem)
                            {
                                $x++;
                                $data['package_details']['package'][] = [
                                    'weight'        => intval($pItem->rtopd_packetweigh),
                                    'length'        => @intval($pItem->rtpod_length),
                                    'width'       	=> @intval($pItem->rtpod_breadth),
                                    'height'        => @intval($pItem->rtpod_height),
                                ];
                            }
                            $p = 0;
                            foreach ($orderData->orderItems as $oitem)
                            {
                                $data['package_details']['products'][] = [
                                    'package_number'	=> 'package_'.$p,
                                    'id'				=> $oitem->item_id,
                                    "name"				=> $oitem->item->stit_itemName,
                                    "price"				=> $oitem->item_price,
                                    "discount"			=> 0,
                                    "total"				=> $oitem->item_sales_price,
                                    "qty"				=> $oitem->item_order_qty,
                                    "sku"				=> $oitem->item->stit_SKU,
                                    "hsn"				=> $oitem->item->stit_HSN_code,
                                    "tax"				=> ($oitem->item_is_taxable > 0) ? ($oitem->item_cgst+$oitem->item_sgst+$oitem->item_igst+$oitem->item_kfc) : 0,
                                    "weight"			=> $oitem->item->item_weight,
                                    "line_item"			=> 0

                                ];
                                $p++;
                            }
                            $data['payment_mode'] = 'online';
                            $data['pending_amount'] = 0;
                            if(($orderData->payment_mode == 1) || ($orderData->payment_mode == 4) || ($orderData->payment_mode == 7))
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