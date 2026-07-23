<?php
namespace App\Http\Controllers\Driver\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\ProceedRequest;
use App\Http\Repositories\Driver\ProceedRepository;

class ProceedPolledOrderController extends Controller
{
    protected $orderRepo;

    public function __construct(ProceedRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }
    
    /**
     * Fetch Pending orders
     *
     */
    public function proceedOrder(ProceedRequest $request)
    {
     
        return $this->orderRepo->proceedOrder($request);
    }
}
