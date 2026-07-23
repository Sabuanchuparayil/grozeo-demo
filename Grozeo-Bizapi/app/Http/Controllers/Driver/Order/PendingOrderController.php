<?php
namespace App\Http\Controllers\Driver\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\PendingOrderRequest;
use App\Http\Repositories\Driver\PendingOrderRepository;

class PendingOrderController extends Controller
{
    protected $orderRepo;

    public function __construct(PendingOrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }
    
    /**
     * Fetch Pending orders
     *
     */
    public function pendingOrders()
    {
     
        return $this->orderRepo->pendingOrders();
    }
}
