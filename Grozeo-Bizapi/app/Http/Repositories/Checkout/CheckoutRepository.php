<?php

namespace App\Http\Repositories\Checkout;

use Illuminate\Support\Arr;
use App\Models\BlockedItems;
use App\Http\Repositories\Cart\CartRepository;
use App\Http\Repositories\Checkout\CheckoutOrder;
use Illuminate\Support\Facades\Log;

class CheckoutRepository
{
    private $blockedItem;

    public function __construct(BlockedItems $blockedItem)
    {
        $this->blockedItem = $blockedItem;
    }

    public function checkoutProcess()
    {
        $this->removeBlockedItem();
        $cart = app(CartRepository::class)->get();
        $stock = (count($cart) > 0) ? $this->checkStockAvailable(Arr::wrap($cart)) : false;
        $order = (count($cart) > 0) ? $this->checkOrderAvailable(Arr::wrap($cart)) : false;
        $msg =  [
            "stock_available" => false,
            "sufficient_available" => false,
            "message" => "Stock is not Available",
            "customer" => new \stdClass,
            "item" => $stock,

        ];

        $msgOrder =  [
            "stock_available" => true,
            "sufficient_available" => false,
            "message" => "Stock is not Available Based on your Order Qty",
            "customer" => new \stdClass,
            "item" => $order,

        ];

        return $stock ? $msg : ($order ? $msgOrder : $this->orderInitiate(Arr::wrap($cart)));
    }

    private function removeBlockedItem()
    {
        $customer_id = auth_user()->cust_id ?? 0;
        return $this->blockedItem->where('customer_id', $customer_id)
                          ->delete();
    }

     /**
     * Check Stock available or not
     *
     * @param array $item
     * @return array
     */
    private function checkStockAvailable(array $item)
    {
         $itemNotAvailable = [];
          foreach ($item as $key => $itm) {
            $count = $item[$key]['item']['item_master'] ?
            count($item[$key]['item']['item_master']) : 0;
            for ($i = 0; $i < $count; $i++)
            {
                $flag = empty($item[$key]['item']['item_master'][$i]['stock_available']) ?
                1 : 0;
                if($flag && $item[$key]['cart_product_id'] == $item[$key]['item']['item_master'][$i]['stit_ID'])
                {
                   $itemNotAvailable[$i]['item_id'] = $item[$key]['cart_product_id'];
                   $itemNotAvailable[$i]['name'] = $item[$key]['item']['item_name'];
                   $itemNotAvailable[$i]['quantity'] = $item[$key]['item']['item_master'][$i]['quantity'];
                   $itemNotAvailable[$i]['stock_available'] = $item[$key]['item']['item_master'][$i]['stock_available'];
                    $flag = 0;
                }
            }

        }
        return $itemNotAvailable;
    }

     /**
     * Check user required Order qty with stock available...
     *
     * @param array $item
     * @return array
     */
    private function checkOrderAvailable(array $item)
    {
        $orderNotAvailable = [];
        foreach ($item as $key => $itm) {
          $count = $item[$key]['item']['item_master'] ?
          count($item[$key]['item']['item_master']) : 0;
          for ($i = 0; $i < $count; $i++)
          {
              $flag = ($item[$key]['cart_order_qty'] > $item[$key]['item']['item_master'][$i]['stock_available']) ?
              1 : 0;
              if($flag && $item[$key]['cart_product_id'] == $item[$key]['item']['item_master'][$i]['stit_ID'])
              {
                  $orderNotAvailable[$i]['item_id'] = $item[$key]['cart_product_id'];
                  $orderNotAvailable[$i]['name'] = $item[$key]['item']['item_name'];
                  $orderNotAvailable[$i]['quantity'] = $item[$key]['item']['item_master'][$i]['quantity'];
                  $orderNotAvailable[$i]['stock_available'] = $item[$key]['item']['item_master'][$i]['stock_available'];
                  $flag = 0;
              }
          }

      }
      return $orderNotAvailable;
    }

    private function orderInitiate(array $cart)
    {
      if(count($cart) > 0)
        {
        $customer = CheckoutOrder::customerCheckout($cart);
        return [
            "stock_available" => True,
            "sufficient_available" => True,
            "message" => "Stock is Available",
            "customer" => $customer,
            "item" => [],
           ];
        }
        else
        {
            return [
                "stock_available" => false,
                "sufficient_available" => false,
                "message" => "Cart is Empty",
                "customer" => new \stdClass,
                "item" => [],
               ];
        }
}

}
