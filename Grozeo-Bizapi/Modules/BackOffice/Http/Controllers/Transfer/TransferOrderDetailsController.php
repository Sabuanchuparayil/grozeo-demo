<?php
namespace BackOffice\Http\Controllers\Transfer;

use BackOffice\Models\{
	BoyOrder,
	TransferOrder,
	BoyOrderRequest
};
use App\Http\Responses\{
	ErrorResponse,
	SuccessResponse,
	SuccessWithData
};
use App\Models\Order;
use App\Events\OrderHistory;
use Illuminate\Support\Facades\DB;
use BackOffice\Http\Requests\TransferOrderDetailsRequest;

class TransferOrderDetailsController
{
	public function __construct(){}

	public function __invoke(TransferOrderDetailsRequest $request)
	{
		try
        {
			$outs = [];
			$domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        	$domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;
			$orderDetails = TransferOrder::where('fsto_id', $request->order_pk_id)
			->with([
				'transferorderDetails:fsto_id,fsto_ItemId,fsto_ItemQty,fsto_pkdQty,fstro_ItemMRP,fstro_ItemSPincTax',
				'transferorderDetails.item:stit_ID,stit_SKU,stit_ConvertCalcMode,stit_ConvertCalcRate,product_category',
				'transferorderDetails.image:product_id,image_url',
				'boy:id,name,lname,phone',
				'order',
				'order.orderItems',
				'order.salesOrder:customer_order_id,SONumber,SODate',
				'order.shipment:id,order_id,shipment_label,tracking_link',
				'order.customer',
				'order.deliveryAddress',
				'fstosStatus',
				'boyOrder:id,boy_id,branch_id,order_pk_id',
				'boyOrderRequest:id,boy_id,branch_id,order_pk_id',
				'packDetails',
				'packDetails.package'
			])
			->join('retaline_godown_boy', 'branch_id', 'fsto_source')
			->where('retaline_godown_boy.id', auth_user()->id)
			->first();
			$restCatCheck = 0;
			if($orderDetails)
			{
				$outs = [
					'order_id'			=> $orderDetails->fsto_id,
					'orderUID'			=> $orderDetails->fsto_uid,
					'order_order_id'	=> @$orderDetails->order->order_order_id,
					'status_id'			=> $orderDetails->fsto_status,
					'salesOrderNo'		=> @$orderDetails->order->salesOrder->SONumber,
					'salesOrderDate'	=> @$orderDetails->order->salesOrder->SODate,
					'orderNotes'		=> @$orderDetails->order->order_note,
					'alreadyPacked'		=> $orderDetails->fsto_isalreadypacked,
					'isCourier'			=> (@$orderDetails->order->order_method == 3) ? 1 : 0,
					'confirmedDate'		=> @$orderDetails->order->order_confirmed_on ?? "",
					'createdDate'		=> @$orderDetails->order->created_at ?? "",
					'key'				=> md5($orderDetails->fsto_updateon),
					'shipmentLabel'		=> @$orderDetails->order->shipment->shipment_label,
					'trackingLink'		=> @$orderDetails->order->shipment->tracking_link,
					'total'				=> (@$orderDetails->total_afterpacking > 0) ? $orderDetails->total_afterpacking : $orderDetails->order->total,
					'pickingNumber'		=> $orderDetails->fsto_pickingNumber
				];
				$itemList = [];
				foreach ($orderDetails->transferorderDetails as $item)
				{
					$itemData = $orderDetails->order->orderItems->where('item_product_id', $item->fsto_ItemId)->first();
					$itemDetails = [
						'id'				=> $item->fsto_ItemId,
						'name'				=> $item->item->stit_SKU,
						'mrp'				=> floatval($item->fstro_ItemMRP),
						'sellingPrice'		=> floatval(@$itemData->item_sales_price),
						'orderQty'			=> floatval($item->fsto_ItemQty),
						'packedQty'			=> floatval($item->fsto_pkdQty),
						'stockValue'		=> ($item->fsto_ItemQty * $item->item->stit_ConvertCalcRate) ?? "",
						'packedStockValue'	=> $item->fsto_stockValue ?? 0,
						'packPrice'			=> floatval($item->fstro_ItemSPincTax),
						'image'				=> ''
					];
					if(@$item->image)
		            {
		                $itemDetails['image'] =  $domain.'thumbnail-'.$item->image->image_url;
		            }
		            if(@$item->item->productCategory->hasRestaurantService == 1)
		            {
		                $restCatCheck++;
		            }
		            $itemDetails['erpID'] = DB::table('finascop_stock_itemmaster_product_codes')->where('fsipc_stit_id', $item->item->stit_ID)->value('fsipc_code');
		            $itemList[] = $itemDetails;
				}
				$packList = [];
				foreach ($orderDetails->packDetails as $packet)
				{
					$packDetails = [
						'id'		=> $packet->rtopd_id,
						'packetID'	=> $packet->rtopd_packets,
						'length'	=> $packet->rtpod_length,
						'breadth'	=> $packet->rtpod_breadth,
						'height'	=> $packet->rtpod_height,
						'weight'	=> $packet->rtopd_packetweigh,
						'package'	=> []
					];
					if($packet->package)
					{
						$packDetails['package'] = [
							'id'		=> $packet->package->rpckm_id,
							'name'		=> $packet->package->rpckm_name,
							'length'	=> $packet->package->rpckm_length,
							'breadth'	=> $packet->package->rpckm_breadth,
							'height'	=> $packet->package->rpckm_height,
						];
					}
					$packList[] = $packDetails;
				}
				$paymentDetails = $this->getPaymentDetails(@$orderDetails->order->payment_mode);
				$outs['paymentMode'] = @$paymentDetails['mode'];
				$outs['paymentStatus'] = @$paymentDetails['status'];
				$outs['hasRestaurant']	= ($restCatCheck > 0) ? 1 : 0;
				$outs['hasInvoice']	= (count(@$orderDetails->order->orderItems) == $restCatCheck) ? 0 : 1;
				$outs['invoiceNo'] = @$orderDetails->order->order_invoiceno;
				$outs['invoiceDate'] = @$orderDetails->order->order_invoicedate;
				$outs['invoiceAmount'] = @$orderDetails->order->order_invoiceamt;
				$outs['customerDetails'] = [
					'name'		=> @$orderDetails->order->customer->cust_customer_name,
					'phone'		=> @$orderDetails->order->customer->cust_mobile,
					'email'		=> @$orderDetails->order->customer->cust_email,
					'address'	=> [
						'name'		=> @$orderDetails->order->deliveryAddress->order_customer_name,
						'phone'		=> @$orderDetails->order->deliveryAddress->order_contact_no,
						'email'		=> @$orderDetails->order->deliveryAddress->order_customer_email,
						'houseNo'	=> @$orderDetails->order->deliveryAddress->order_house_no,
						'houseName'	=> @$orderDetails->order->deliveryAddress->order_house_name,
						'address1'	=> @$orderDetails->order->deliveryAddress->order_address,
						'address2'	=> @$orderDetails->order->deliveryAddress->order_address2,
						'city'		=> @$orderDetails->order->deliveryAddress->order_city,
						'landmark'	=> @$orderDetails->order->deliveryAddress->order_land_mark,
						'state'		=> @$orderDetails->order->deliveryAddress->order_state,
						'country'	=> @$orderDetails->order->deliveryAddress->order_country,
						'pinCode'	=> @$orderDetails->order->deliveryAddress->order_post
					]
				];
				$outs['item_details'] = $itemList;
				$outs['packDetails'] = $packList;
				$outs['boyDetails'] = $orderDetails->boy;
				$outs['boyOrder'] = $orderDetails->boyOrder;
				$outs['boyOrderRequest'] = $orderDetails->boyOrderRequest;
				$outs['status'] = $orderDetails->fstosStatus;
			}
			return new SuccessWithData($outs);
		}
		catch (\Exception $e)
        {
        	// info("TransferOrderDetailsController ERROR---");info($e);
            return new ErrorResponse("Operation failed"); 
        }
	}

	private function getPaymentDetails($payMode)
	{
		$outs = [
			"mode"		=> "",
			"status"	=> ""
		];
		switch ($payMode)
		{
			case 1:
				$outs['mode'] = "Pay on Delivery";
				$outs['status'] = "Not Paid";
				break;
			case 2:
				$outs['mode'] = "Online";
				$outs['status'] = "Paid";
				break;
			case 3:
				$outs['mode'] = "Wallet";
				$outs['status'] = "Paid";
				break;
			case 4:
				$outs['mode'] = "POD with Wallet";
				$outs['status'] = "Partially Paid";
				break;
			case 5:
				$outs['mode'] = "Online with Wallet";
				$outs['status'] = "Paid";
				break;
			case 6:
				$outs['mode'] = "Online on Delivery";
				$outs['status'] = "Not Paid";
				break;
			case 7:
				$outs['mode'] = "Cash on Delivery";
				$outs['status'] = "Not Paid";
				break;
		}
		return $outs;
	}
}