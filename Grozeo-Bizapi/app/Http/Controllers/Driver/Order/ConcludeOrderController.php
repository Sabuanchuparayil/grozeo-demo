<?php
namespace App\Http\Controllers\Driver\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\ConcludeOrderRequest;
use App\Http\Repositories\Driver\ConcludeOrderRepository;

class ConcludeOrderController extends Controller
{
    protected $orderRepo;

    public function __construct(ConcludeOrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }
    
    /**
     * Fetch Pending orders
     *
     */
    public function concludeOrder(ConcludeOrderRequest $request)
    {
     
        return $this->orderRepo->concludeOrder($request);
    }
}
