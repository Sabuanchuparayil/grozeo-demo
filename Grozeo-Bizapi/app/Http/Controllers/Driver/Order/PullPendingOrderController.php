<?php
namespace App\Http\Controllers\Driver\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\PullPendingOrderRequest;
use App\Http\Repositories\Driver\PullPendingOrderRepository;

class PullPendingOrderController extends Controller
{
    protected $orderRepo;

    public function __construct(PullPendingOrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }
    
    /**
     * Pull Pending order
     *
     */
    public function pullPendingOrder(PullPendingOrderRequest $request)
    {
     
        return $this->orderRepo->pullPendingOrder($request);
    }
}
