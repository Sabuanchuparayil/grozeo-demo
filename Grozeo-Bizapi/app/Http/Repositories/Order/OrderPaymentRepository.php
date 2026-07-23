<?php

namespace App\Http\Repositories\Order;

use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\BackOffice\BO_Order;
use App\Jobs\SendOrderConfirmedMail;
use App\Contracts\PaymentGatewayInterface;

class OrderPaymentRepository
{
    protected $cart;

    protected $payment;

    protected $order;

    const SUCCESS = 'SUCCESS';

    const ORDER_SUCCESS = 2;

    public function __construct(Cart $cart, PaymentGatewayInterface $payment, Order $order)
    {
        $this->cart = $cart;
        $this->payment = $payment;
        $this->order = $order;
    }

    /**
     * Confirm the payment status.
     *
     * @param \Illuminate\Http\Request $request
     * @return boolean
     */
    public function confirm($request)
    {
        $response = $this->payment->getPaymentStatus(
            $request->all()
        );

        if ($response->getStatusCode() == 200) {
            $responseBody = $response->getBody()->getContents();
            $responseBody = json_decode($responseBody, true);

            if ($responseBody['STATUS'] == 'TXN_SUCCESS') {
                $this->saveResponseToDb($responseBody);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Save the response from the payment gateway to DB.
     *
     * @param array $responseBody
     * @return void
     */
    public function saveResponseToDb($responseBody)
    {
        $order = $this->order->find($responseBody['ORDERID']);

        $boOrder = BO_Order::where('order_generated_id', $responseBody['ORDERID'])->first();
        
        DB::transaction(function () use ($order, $boOrder, $responseBody) {

            $this->updateOrder($order, $responseBody);
            $this->updateOrderStatus(
                $order->status()->first(),
                $responseBody
            );
            $this->storeTransactionDetails($responseBody);
            $this->updateBackOfficeDb($boOrder);
            $this->clearCartItems();
            
        });

        // SendOrderConfirmedMail::dispatch($order);
    }

    /**
     * Update the order table
     *
     * @param \App\Models\Order $order
     * @param array $responseBody
     * @return void
     */
    public function updateOrder($order, $responseBody)
    {
        $order->order_status = $responseBody['STATUS'] == 'TXN_SUCCESS' ?
            static::SUCCESS :
            $responseBody['STATUS'];
        $order->save();
    }

    /**
     * Update order status table
     *
     * @param \App\Models\OrderStatus $orderStatus
     * @param array $responseBody
     * @return void
     */
    public function updateOrderStatus($orderStatus, $responseBody)
    {
        $orderStatus->stat_order_status = $responseBody['STATUS'] == 'TXN_SUCCESS' ?
            static::SUCCESS : 
            $responseBody['STATUS'];
        $orderStatus->stat_order_description = $responseBody['RESPMSG'];
        $orderStatus->save();
    }

    /**
     * Store transaction details to DB.
     *
     * @param array $responseBody
     * @return void
     */
    public function storeTransactionDetails($responseBody)
    {
        PaymentTransaction::create([
            'txn_id' => $responseBody['TXNID'],
            'bank_txn_id' => $responseBody['BANKTXNID'],
            'order_id' => $responseBody['ORDERID'],
            'txn_amount' => $responseBody['TXNAMOUNT'],
            'txn_status' => $responseBody['STATUS'],
            'txn_type' => $responseBody['TXNTYPE'],
            'gateway' => $responseBody['GATEWAYNAME'],
            'response_code' => $responseBody['RESPCODE'],
            'response_msg' => $responseBody['RESPMSG'],
            'bank_name' => $responseBody['BANKNAME'],
            'm_id' => $responseBody['MID'],
            'payment_mode' => $responseBody['PAYMENTMODE'],
            'refund_amount' => $responseBody['REFUNDAMT'],
            'txn_date' => $responseBody['TXNDATE'],
        ]);
    }

    /**
     * Update the order table in back office
     *
     * @param \App\Models\BackOffice\BO_Order $order
     * @return void
     */
    public function updateBackOfficeDb($order)
    {
        $order->order_status = static::ORDER_SUCCESS;
        $order->save();
    }

    /**
     * Clear all exisiting items in the cart.
     *
     * @return void
     */
    public function clearCartItems()
    {
        $this->cart
            ->where('cart_customer_id', auth_user()->cust_id)
            ->delete();
    }
}
