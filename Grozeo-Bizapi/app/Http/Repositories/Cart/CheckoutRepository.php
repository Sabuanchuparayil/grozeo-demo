<?php

namespace App\Http\Repositories\Cart;

use App\Models\Cart;
use App\Product;
use Illuminate\Support\Facades\DB;
use App\Domains\Cart\GenerateOrder;
use App\Jobs\SendOrderConfirmedMail;
use App\Domains\Cart\PrepareCartItem;
use App\Domains\Instamojo\InstamojoToken;
use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ProductExistInCartException;

class CheckoutRepository
{
    protected $cart;

    protected $payment;

    public function __construct(Cart $cart, PaymentGatewayInterface $payment)
    {
        $this->cart = $cart;
        $this->payment = $payment;
    }

    public function tempCheckout()
    {
        $instamojo = new InstamojoToken("test_zIaXjbsuGs4oI7cyf8ySlZkxB4DWcyuPNj0", "test_1V8wtH77xY4eg2CWEWWVGSQKdcpjG9aCfrxtrT9X3D2ZHZNnqjVETEXCsk5hBbQnM9ydDVDpgaiK8uAXpwnqqqUhIgXUUrCbfJAmry8saqDTnkHfDajrHCQTMKP");
        $accessToken = $instamojo->getToken()->access_token;
        $response = app(\App\Http\Controllers\InstamojoController::class)->demo();
        $paymentRequestId = $response['id'];
        return [
            'access_token' => $accessToken,
            'payment_request_id' => $paymentRequestId,
        ];
    }

    public function checkout()
    {
        $totalAmount = $this->getAmount();
        $cartItems = $this->fetchCartItems();
        $order = $this->initiateOrder($cartItems, $totalAmount);
        // $this->clearCartItems();

        return [
            'amount' => $totalAmount,
            'cart_details' => $cartItems,
            'order_id' => $order->order_order_id,
            'payment_details' => $this->payment->getPaymentDetails(
                $order->order_order_id,
                $totalAmount
            ),
        ];
    }

    /**
     * Retrieve the ids of added products to the cart
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAmount()
    {
        return $this->cart
                    ->where('cart_customer_id', auth_user()->cust_id)
                    ->sum(DB::raw('cart_price * cart_order_qty'));
    }

    public function initiateOrder($cartItems, $amount)
    {
        //create an order
        $order = GenerateOrder::create($cartItems, $amount);
        //return order.
        return $order;
    }

    /**
     * Retrieve all items in the cart.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function fetchCartItems()
    {
        return $this->cart
            ->with(['product' => function ($query) {
                $query->withoutGlobalScope('isAddedToCart')
                    ->select('product_id', 'product_name');
            }])
            ->where('cart_customer_id', auth_user()->cust_id)
            ->get();
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
