<?php

namespace BackOffice\Http\Repositories\Drivers;

use App\Http\Responses\{
    SuccessResponse,
    SuccessWithData,
    ErrorResponse
};
use BackOffice\Models\Drivers\QugeoDriver;
use App\Sms\SmsSender;
use Illuminate\Support\Facades\DB;
use Aws\DynamoDb\DynamoDbClient;

class DriverRepository
{

    public function __construct(QugeoDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
    * Driver details
    *
    * @return string
    */
    public function driverDetails()
    {
        $driver = auth_user();
        try
        {
            $driver = $this->driver->select('d_ID', 'd_Name', 'l_Name', 'd_Add1', 'd_Add2', 'd_Add3', 'emp_id', 'emp_email_id', 'd_Ph1', 'd_dob', 'd_licence', 'd_licenceexpairy')
            ->where([
                ['d_ID', $driver->d_ID],
                ['d_Active', 1]
            ])->with('vehicles:id,driver_id,vehicle_no,status,vehicle_type', 'vehicles.vehicleType:vhty_id,vhty_name')->first();
            if($driver)
            {
                return new SuccessWithData($driver);
            }
            return new ErrorResponse("Driver not found");
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage());
        }
    }
}