<?php

namespace App\Http\Repositories\Cart;


use App\Models\Cart;


class DeleteCartRepository
{

    protected $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function deleteCart($request)
    {
        $values = array();
        $val = $request->data;

        foreach ($val as $values) {

            $count = Cart::select('cart_order_qty')->where('id', $values["id"])->first();
            if ($values["quantity"] == $count['cart_order_qty']) {

                $this->cart->where('id', $values["id"])
                    ->delete();
            } else {
                $qty =  $count['cart_order_qty'] - $values["quantity"];
                $this->cart->where('id', $values["id"])
                    ->update(['cart_order_qty' => $qty]);
            }
        }
    }
}
