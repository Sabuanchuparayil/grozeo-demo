<?php
namespace App\Http\Controllers\Driver\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\PreConcludeRequest;
use App\Http\Repositories\Driver\PreConcludeRepository;

class PreConcludeOrderController extends Controller
{
    protected $orderRepo;

    public function __construct(PreConcludeRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }
    
    /**
     * Fetch Pending orders
     *
     */
    public function preConcludeOrder(PreConcludeRequest $request)
    {
     
        return $this->orderRepo->preConcludeOrder($request);
    }
}
