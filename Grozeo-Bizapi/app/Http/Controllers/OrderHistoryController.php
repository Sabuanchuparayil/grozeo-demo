<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use App\Http\Requests\Order\RatingRequest;
use App\Http\Repositories\OrderHistoryRepository;

class OrderHistoryController extends Controller
{
    protected $order;

    public function __construct(OrderHistoryRepository $order)
    {
        $this->order = $order;
    }

    public function list($order_method)
    {

        return new SuccessWithData(
            $this->order->orderList($order_method)
        );
    }

    public function summary($order_id)
    {
        return new SuccessWithData(
            $this->order->orderSummary($order_id)
        );
    }

    public function orderDetails($order_id)
    {
        return new SuccessWithData(
            $this->order->orderDetails($order_id)
        );
    }

    public function trackUrl($order_id)
    {
        return new SuccessWithData(
            $this->order->trackUrl($order_id)
        );
    }

    public function addRating(RatingRequest $request)
    {
        $this->order->addRating($request->validated());
        return new SuccessResponse(
            'Updated Successfully.'
        );
    }
    public function groupOrders($order_group_id)
    {

        return new SuccessWithData(
            $this->order->groupOrders($order_group_id)
        );
    }

}
