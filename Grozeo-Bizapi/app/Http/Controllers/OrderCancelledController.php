<?php

namespace App\Http\Controllers;

use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
use App\Http\Requests\Order\OrderCancelRequest;
use App\Http\Repositories\Order\OrderCancelledRepository;

class OrderCancelledController extends Controller
{
    protected $orderCancelled;

    public function __construct(OrderCancelledRepository $orderCancelled)
    {
        $this->orderCancelled = $orderCancelled;
    }

    public function store(OrderCancelRequest $request)
    {
        //need to add logic for cancelling only unshipped orders.
        return $this->orderCancelled->create($request->validated());   
    }
    
}
