<?php

namespace App\Http\Controllers;

use App\Modules\Checkout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\{
    OrderAddress,
    Order,
    Cart
};
use App\Http\Controllers\Controller;
use App\Http\Responses\{
    ErrorResponse,
    ErrorWithData,
    SuccessResponse,
    SuccessWithData
};
use App\Events\OrderHistory;
use App\Modules\Payment\InterfacePayment;
use BackOffice\Status\CustomerOrderStatus;
use App\Http\Requests\Checkout\CheckoutRequest;
use App\Http\Requests\Payment\InstamojoRequest;
use App\Http\Controllers\PaymentResultController;
use App\Http\Requests\Payment\PaymentDetailsRequest;

use App\Http\Repositories\Coupon\Coupon;

class CheckoutController extends Controller
{
    private $checkout;

    private $interfacePayment;

    public function __construct(Checkout $checkout, InterfacePayment $interfacePayment)
    {

        $this->checkout = $checkout;
        $this->interfacePayment = $interfacePayment;
    }

    public function checkout(CheckoutRequest $request)
    {
        $request['branch_id']= getCurrentUserBranch();
        $request['nearest_retailer_branch']= $request['branch_id'];

        $data = $this->checkout->create($request->validated());

        $hasStock =  'true';
        $totalOrders = count($data['orders']);
        $orderIdCount = count(array_column($data['orders'], 'order_id'));

        if($totalOrders != $orderIdCount)
        {
            $hasStock =  'false';
        }else{
            $hasStock =  'true';
        }

        $isOrder = @$data['orders'][0]->order_id?$data['orders'][0]->order_id:0;
        if($hasStock == 'false'){
            $data['stock_available'] = false;
            $data['sufficient_available'] = false;
            $data['message'] = "Stock not available";
        }
        if (is_array($data)) {
            if (array_key_exists("order", $data) && $data['order'] == 0) {
                $error = [
                    "type" => $data['type'],
                    "items" => $data['data'],
                ];
                return new ErrorWithData($data['msg'], $error, 406);
            }
        }
        $styles = @$data['orders'][0]->order_id?(new Coupon)->getWalletOnlyDetails(['order_id' => $data['orders'][0]->order_id]) : [];
        $data['style'] = @$data['orders'][0]->order_id?$styles['style']:[];

        $data['delivery_details'] = (@$data['orders'][0]->order_id && @$data['orders'][0]->order_customer_id) ? $this->myOrderDeliveryDetails($data['orders'][0]->order_id, $data['orders'][0]->order_customer_id) : (object)[];

        return new SuccessWithData($data);
    }

    private function myOrderDeliveryDetails($order_id, $order_customer_id)
    {
        $deliveryAddress = OrderAddress::select(
                'order_customer_name',
                'order_customer_email',
                'order_house_no',
                'order_house_name',
                'order_house_no',
                'order_house_name',
                'order_address',
                'order_address2',
                'order_land_mark',
                'order_city',
                'order_post',
                'order_state',
                'order_pin',
                'order_country',
                'order_deli_note',
                'order_contact_no'
            )->where([
                ['customer_order_id', $order_id],
                ['order_customer_id', $order_customer_id]
            ])->first();
        return $deliveryAddress;
    }

    public function confirmorder(CheckoutRequest $request)
    {
        $request['branch_id']= getCurrentUserBranch();
        $request['nearest_retailer_branch']= $request['branch_id'];

        $data = $this->checkout->confirmorder($request);
        return new SuccessWithData($data);
    }
    public function generatePaymentGatewayLink(Request $request)
    {
        $orderId = $request->orderId;
        $data = $this->checkout->podToOnlineGenerateLink($orderId);
        if($data)
        {
            $refid = @$data['details']['id'] ? $data['details']['id'] : @$data['details']['key'];
            Order::where('order_id', $data['order_id'])
                ->where('order_customer_id', auth_user()->cust_id)
                ->update([
                'order_payment_gateway'             => $data['payment_gateway'],
                'order_payment_gateway_req_refid'   => $refid
            ]);
        }
        return new SuccessWithData($data);
    }


    public function verifyInstamojo(InstamojoRequest $request)
    {
        $payment_request_id = $request->validated();
        return new SuccessWithData(
            $this->interfacePayment->verifyPayment([
                "id" => $payment_request_id['payment_request_id']
            ])
        );
    }
    
    public function postpayment(PaymentDetailsRequest $request)
    {
        return new SuccessWithData(
           (new PaymentResultController)->getpaymentstatus($request)
        );
    }
    public function instamojoStatus(InstamojoRequest $request)
    {
        return new SuccessWithData(
            $this->interfacePayment->instamojoStatus($request->validated())
        );
    }


    // REMOVE NOT DELIVERABLE ORDERS
    public function removeNotDeliverableOrders(Request $request)
    {
        try
        {
            $custID = auth()->user()->cust_id;
            $storegroupID = getHeaderStoreGroup();
            $orderID = $request->get('order_id');
            $orderData = Order::where([
                ['order_order_id', $orderID],
                ['order_customer_id', $custID],
                ['status_id', 54]
            ])->with('orderItems:customer_order_id,item_product_id')->first();
            if($orderData)
            {
                $orderItems = $orderData->orderItems;
                $productIDs = array_column($orderItems->toArray(), 'item_product_id');

                $orderGroupID = $orderData->order_group_id;
                $orderGroupID .= '_nd';
                $response = $orderData->update([
                    'order_group_id'    => $orderGroupID,
                    'status_id'         => CustomerOrderStatus::CANCELLED
                ]);
                if($response)
                {
                    $cartData = Cart::where([
                        ['cart_customer_id', $custID],
                        ['storegroup_id', $storegroupID]
                    ])->whereIn('cart_product_id', $productIDs)->delete();
                    event(new OrderHistory($orderData->order_id, CustomerOrderStatus::CANCELLED, "Not Deliverable Order Cancelled"));
                    return new SuccessResponse('Order removed');
                }
                return new ErrorResponse('Operation failed.');
            }
            return new ErrorResponse('Order not found');
        }
        catch (\Exception $e)
        {
            // info("CheckoutController removeNotDeliverableOrders ERROR");info($e);
            return new ErrorResponse($e->getMessage());
        }
    }
}
