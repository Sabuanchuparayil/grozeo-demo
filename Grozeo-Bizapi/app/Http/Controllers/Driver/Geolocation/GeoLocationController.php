<?php

namespace App\Http\Controllers\Driver\Geolocation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\GeolocationRequest;
use App\Http\Repositories\Driver\GeoLocationRepository;
use Illuminate\Http\Request;

class GeoLocationController extends Controller
{
    protected $locationRepo;

    public function __construct(GeoLocationRepository $locationRepo)
    {
        $this->locationRepo = $locationRepo;
    }
    
    /**
     * Update driver's location
     *
     */
    public function updateLocation(GeolocationRequest $request)
    {
        return $this->locationRepo->updateLocation($request->geocoords);
    }
}
