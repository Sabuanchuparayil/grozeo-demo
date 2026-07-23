<?php

namespace BackOffice\Http\Repositories\Drivers;

use App\Http\Responses\{
    SuccessResponse,
    SuccessWithData,
    ErrorResponse
};
use BackOffice\Models\Drivers\{
    QugeoDriver,
    DriverVehicles
};
use Illuminate\Support\Facades\DB;
use Aws\DynamoDb\DynamoDbClient;

class DriverStatusUpdateRepository
{

    public function __construct(QugeoDriver $driver)
    {
        $this->driver = $driver;
    }


    /**
     * update online status
     *
     * @return string
     */
    public function driverUpdateOnlineStatus($request)
    {
        $driver = auth_user();
        try
        {
            $update['is_online'] = $request['status'];
            $message = "Driver is now offline";
            if($request['status'] == 1)
            {
                $update['gcmregstid'] = $request['gcm_id'];
                $message = 'Driver is now online';
            }
            $update = QugeoDriver::where('d_ID', $driver->d_ID)->update($update);
            if($update)
            {
                if($request['status'] == 1)
                {
                    $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
                    $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
                    $dynamoClient->putItem([
                        'TableName' => config('aws.driver_location'),
                        'Item'      => [
                            'uuid'      => ['S' => (string)$uuid],
                            'tstamp'    => ['S' => (string)date('Y-m-d H:i:s')],
                            'driver_id' => ['S' => (string)$driver->d_ID],
                            'latitude'  => ['S' => (string)$request['latitude']],
                            'longitude' => ['S' => (string)$request['longitude']],
                            'gcm_id'    => ['S' => (string)$request['gcm_id']]
                        ]
                    ]);
                }
                if($request['status'] == 2)
                {
                    $updateVehicles = DriverVehicles::where('driver_id', $driver->d_ID)->update(['status' => '0']);
                }
                return new SuccessResponse($message);
            }
            return new ErrorResponse("Unable to update. Some error occured.");
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage());
        }
    }
}