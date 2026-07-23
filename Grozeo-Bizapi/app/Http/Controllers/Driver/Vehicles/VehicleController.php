<?php
namespace App\Http\Controllers\Driver\Vehicles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\{
    VehicleRequest,
    AddVehicleRequest,
    SelectVehicleRequest
};
use Illuminate\Http\Request;
use App\Http\Responses\{
    SuccessResponse,
    SuccessWithData,
    ErrorResponse
};
use App\Http\Repositories\Driver\VehicleRepository;

class VehicleController extends Controller
{
    protected $vehicleRepo;

    public function __construct(VehicleRepository $vehicleRepo)
    {
        $this->vehicleRepo = $vehicleRepo;
    }
    
    /**
     * Fetch all vehicle types from repository
     *
     */
    public function getVehicleTypes()
    {
        return $this->vehicleRepo->getVehicleTypes();
    }
    public function getLastVehicles(Request $request) // old version. New api is below
    {
        return $this->vehicleRepo->getLastVehicles($request);
    }
    public function selectVehicle(SelectVehicleRequest $request) // old version. New api is below. split into two apis.
    {
        return $this->vehicleRepo->selectVehicle($request);

    }


    /**
     * Vehicle New APIs for listing, creating and selecting vehicle.
    */
    // List Vehicle
    public function vehicleList()
    {
        return $this->vehicleRepo->vehicleList();
    }
    // Add vehicle
    public function addVehicle(AddVehicleRequest $request)
    {
        return $this->vehicleRepo->addVehicle($request);
    }
    // Select Vehicle
    public function chooseVehicle($vehicleID)
    {
        return $this->vehicleRepo->chooseVehicle($vehicleID);
    }
}
