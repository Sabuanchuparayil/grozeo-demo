<?php

namespace App\Http\Repositories\Driver;
use App\Models\Drivers\QugeoDriver;
use App\Http\Responses\{
    ErrorResponse
};
use App\Http\Resources\DriverDetailsResource;

class DriverDetailRepository
{
    protected $driver;

    public function __construct(QugeoDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Get driver details by mobile number
     *
     * @param  string  $mobileNumber
     * @return \App\Models\Drivers\QugeoDriver|null
     */
    public function getDriverDetails($mobileNumber)
    {
        try
        {
            $driverDetails=$this->driver->select('d_Name','d_Add1','d_Add2','d_Add3','d_licence','d_licenceexpairy')
            ->where('d_Ph1', $mobileNumber)
            ->where('d_Active', 1)
            ->first();
              // Return formatted response
            if ($driverDetails) {
                return new DriverDetailsResource($driverDetails);
                }
                return new ErrorResponse("Sorry that we could not find your details in our delivery agent list.. please contact " . config('constant.PROJECT_NAME') . " administrator");
            }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage());
        }
       
    }
}
