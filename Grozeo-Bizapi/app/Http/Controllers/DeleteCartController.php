<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\Cart\DeleteCartRepository;
use App\Http\Controllers\CartController;

class DeleteCartController extends Controller
{
    private $cart;
    public function __construct(DeleteCartRepository $cart)
    {
        $this->cart = $cart;
    }

    public function delete(Request $request)
    {
        $this->cart->deleteCart($request);
        return app(CartController::class)->cartorder($request);
    }
}
