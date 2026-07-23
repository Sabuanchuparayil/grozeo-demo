<?php

namespace App\Http\Controllers\Driver\Authentication;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\DriverDetailsRequest;
use Illuminate\Http\Request;
use App\Http\Resources\DriverDetailsResource;
use App\Http\Repositories\Driver\DriverDetailRepository;

class DriverDetailsController extends Controller
{
    protected $detailsRepo;

    public function __construct(DriverDetailRepository $detailsRepo)
    {
        $this->detailsRepo = $detailsRepo;
    }
    
    /**
     * Fetch driver details based on mobile number
     *
     * @param  DriverDetailsRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function driverDetails(DriverDetailsRequest $request)
    {
        // Fetch driver details from repository
        return $this->detailsRepo->getDriverDetails($request->mobile_number);
        
    }
}
