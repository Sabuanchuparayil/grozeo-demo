<?php

namespace BackOffice\Http\Controllers\Boy;

use BackOffice\Models\{
    TransferOrder,
    BoyOrderRequest
};
use App\Http\Responses\{
    ErrorResponse,
    SuccessWithData
};
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use BackOffice\Status\TransferOrderStatus;
use BackOffice\Http\Requests\BoyOrderRerackRequest;

class BoyOrdeRerackController
{
    public function __invoke(BoyOrderRerackRequest $request)
    {
        try
        {
            $outs = [];
            $boy = auth_user()->id;
            $transferOrder = TransferOrder::where([
                ['fsto_id', $request->order_id],
                ['fsto_polled_boy', $boy],
                ['fsto_status', TransferOrderStatus::CANCELLED]
            ])->first();
            if($transferOrder)
            {
                $order = Order::select('order_id', 'order_order_id', 'order_customer_id')
                ->where('order_id', $transferOrder->fstr_id)
                ->with([
                    'orderItems:item_id,item_order_id,customer_order_id,item_product_id,item_order_qty,item_price',
                    'orderItems.item:stit_ID,stit_SKU',
                    'customer:cust_id,cust_customer_name,cust_email,cust_mobile',
                    'deliveryAddress:customer_order_id,order_customer_name,order_customer_email,order_contact_no,order_house_no,order_house_name,order_address,order_address2,order_land_mark,order_city,order_state,order_post'
                ])->first();
                $outs['order'] = $order;
                $outs['order_pk_id'] = $transferOrder->fsto_id;
                $outs['basket_number'] = $transferOrder->fsto_pickingNumber;
                $orderReqID = BoyOrderRequest::select('id')->where([
                    ['boy_id', $boy],
                    ['order_pk_id', $transferOrder->fsto_id]
                ])->first();
                $outs['order_request_id'] = @$orderReqID->id;
            }
            return new SuccessWithData($outs);
        }
        catch (\Exception $e)
        {
            // info("BoyOrdeRerackController--Error");info($e);
            return new ErrorResponse($e->getMessage()); 
        }
    }
}
