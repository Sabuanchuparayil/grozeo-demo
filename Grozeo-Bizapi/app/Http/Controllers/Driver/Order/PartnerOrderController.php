<?php
namespace App\Http\Controllers\Driver\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\PartnerOrderRequest;
use App\Http\Requests\Driver\PartnerLiveVehicleRequest;
use App\Http\Requests\Driver\PartnerLoadVehicleRequest;
use App\Http\Repositories\Driver\PartnerOrderRepository;

class PartnerOrderController extends Controller
{
    protected $orderRepo;

    public function __construct(PartnerOrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }
    
    /**
     * Assign Order to driver
     *
     */
    public function partnerOrder(PartnerOrderRequest $request)
    {
        return $this->orderRepo->partnerOrder($request);
    }
    public function listLiveVehicles(PartnerLiveVehicleRequest $request)
    {
        return $this->orderRepo->listLiveVehicles($request);
    }
    public function loadVehicleDetails(PartnerLoadVehicleRequest $request)
    {
        return $this->orderRepo->loadVehicleDetails($request);
    }
}
