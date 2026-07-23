<?php
namespace App\Http\Controllers\Driver\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\MileStoneRequest;
use App\Http\Repositories\Driver\MileStoneRepository;

class MileStoneController extends Controller
{
    protected $orderRepo;

    public function __construct(MileStoneRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }
    
    /**
     * Fetch Pending orders
     *
     */
    public function milestone(MileStoneRequest $request)
    {
     
        return $this->orderRepo->milestone($request);
    }
}
