<?php
namespace App\Http\Controllers\Driver\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\DeliveredOrderRequest;
use App\Http\Repositories\Driver\DeliveredOrderRepository;

class DeliveredOrderController extends Controller
{
    protected $orderRepo;

    public function __construct(DeliveredOrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }
    
    /**
     * Fetch Pending orders
     *
     */
    public function deliveredOrders(DeliveredOrderRequest $request)
    {
     
        return $this->orderRepo->deliveredOrders($request);
    }
}
