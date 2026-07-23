<?php
namespace App\Http\Controllers\Driver\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\LiveOrderRequest;
use App\Http\Repositories\Driver\LiveOrderRepository;

class LiveOrderController extends Controller
{
    protected $orderRepo;

    public function __construct(LiveOrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }
    
    /**
     * Fetch Pending orders
     *
     */
    public function liveOrder(LiveOrderRequest $request)
    {
     
        return $this->orderRepo->liveOrder($request);
    }
}
