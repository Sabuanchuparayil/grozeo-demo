<?php

namespace BackOffice\Http\Repositories\Drivers;

use App\Http\Responses\{
    SuccessResponse,
    SuccessWithData,
    ErrorResponse
};
use BackOffice\Models\Drivers\{
    QugeoDriver,
    QugeoVehicleType,
    DriverVehicles
};
use Illuminate\Support\Facades\DB;
use Aws\DynamoDb\DynamoDbClient;

class DriverVehiclesRepository
{

    public function __construct()
    {
    }

    /**
    * Driver all vehicles
    *
    * @return string
    */
    public function listAllVehicles()
    {
        $driver = auth_user();
        try
        {
            $vehicles = DriverVehicles::where('driver_id', $driver->d_ID)->with('vehicleType:vhty_id,vhty_name')->get();
            return new SuccessWithData($vehicles);
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage());
        }
    }

    /**
    * Driver all vehicles
    *
    * @return string
    */
    public function listAllVehicleTypes()
    {
        try
        {
            $vehicleTypes = QugeoVehicleType::select('vhty_id as id', 'vhty_name as name')
            ->where('vhty_Active', 1)->get();
            return new SuccessWithData($vehicleTypes);
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage());
        }
    }

    /**
    * Create New Vehicle
    *
    * @return string
    */
    public function createNewVehicle($request)
    {
        $driver = auth_user();
        try
        {
            $create = DriverVehicles::create([
                "driver_id"     => $driver->d_ID,
                "vehicle_type"  => $request['vehicle_type'],
                "vehicle_no"    => $request['vehicle_number']
            ]);
            if($create)
            {
                return new SuccessResponse("New Vehicle Added");
            }
            return new ErrorResponse("Some error occured.");
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage());
        }
    }

    /**
    * Driver select a vehicle
    *
    * @return string
    */
    public function selectVehicle($request)
    {
        $driver = auth_user();
        try
        {
            $vehicle = DriverVehicles::where([
                ['driver_id', $driver->d_ID],
                ['id', $request['vehicle_id']]
            ])->first();
            if($vehicle)
            {
                $vehicleUpdateAll = DriverVehicles::where('driver_id', $driver->d_ID)->update(['status' => '0']);
                $vehicleUpdate = DriverVehicles::where('id', $request['vehicle_id'])->update(['status' => '1']);
                if($vehicleUpdate && $vehicleUpdateAll)
                {
                    return new SuccessResponse("Vehicle Selected: {$vehicle->vehicle_no}");
                }
                return new ErrorResponse("Unable to select this vehicle");
            }
            return new ErrorResponse("Vehicle not found associated with this driver");
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage());
        }
    }
}