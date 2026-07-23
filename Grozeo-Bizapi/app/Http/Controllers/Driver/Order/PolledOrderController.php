<?php
namespace App\Http\Controllers\Driver\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\PolledOrderRequest;
use App\Http\Repositories\Driver\PolledOrderRepository;

class PolledOrderController extends Controller
{
    protected $orderRepo;

    public function __construct(PolledOrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }
    
    /**
     * Fetch Pending orders
     *
     */
    public function pollOrder(PolledOrderRequest $request)
    {
     
        return $this->orderRepo->pollOrder($request);
    }
    /**
     * Deny Pending orders
     *
     */
    public function denyPolledOrder($orderID)
    {
        return $this->orderRepo->denyPolledOrder($orderID);
    }
}
