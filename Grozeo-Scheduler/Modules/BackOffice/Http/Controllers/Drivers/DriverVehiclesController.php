<?php

namespace BackOffice\Http\Controllers\Drivers;

use BackOffice\Http\Requests\Drivers\CreateVehicleRequest;
use BackOffice\Http\Repositories\Drivers\DriverVehiclesRepository;

class DriverVehiclesController
{
    protected $vehicleRepo;

    public function __construct(DriverVehiclesRepository $vehicleRepo)
    {
        $this->vehicleRepo = $vehicleRepo;
    }
    
    /**
     * driver list all vehicles
     *
     * @return array
     */
    public function listAllVehicles()
    {
        return $this->vehicleRepo->listAllVehicles();
    }
    
    /**
     * driver list all vehicle types
     *
     * @return array
     */
    public function listAllVehicleTypes()
    {
        return $this->vehicleRepo->listAllVehicleTypes();
    }
    
    /**
     * driver create vehicle
     *
     * @return array
     */
    public function createNewVehicle(CreateVehicleRequest $request)
    {
        return $this->vehicleRepo->createNewVehicle($request->validated());
    }
}