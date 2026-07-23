<?php
namespace App\Traits\Driver;

use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;

trait LiveVehicleTrait
{
    // add vehicle to the dynamodb tables
    public function updateLiveVehicleData($driver, $location)
    {
        $vehicle = $driver->primaryVehicle; // get primary vehicle
        if(!$vehicle)
        {
            return;
        }
        // initialize dynamodb class
        $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));

        // generate item lict for table
        $item = $this->generateDDBData($driver, $vehicle, $location);

        // Add vehicle details to dynamodb QugeoLiveVehicles
        $result = $dynamoClient->putItem([
            'TableName' => config('aws.prefix') . 'QugeoLiveVehicles',
            'Item'      => $item
      
        ]);
        // Add vehicle details to dynamodb QugeoLiveVehiclesHistory
        $liveHistory=$dynamoClient->putItem([
            'TableName' => config('aws.prefix') . 'QugeoLiveVehiclesHistory',
            'Item'      => $item
        ]);
    }
    private function generateDDBData($driver, $vehicle, $location)
    {
        $valdate = date("Ymd");
        $valdatetime = date("YmdHis");
        $item= [
            'apikey'                    => ['S' => (string) $driver->d_apikey],
            'createddatetime'           => ['N' => (string) $valdatetime],
            'createddate'               => ['N' => (string) $valdate],
            'LocationUpdateddatetime'   => ['N' => (string) $valdatetime],
            'latitude'                  => ['N' => (string) $location["latitude"]],
            'longitude'                 => ['N' => (string) $location["longitude"]],
            'v_id'                      => ['S' => (string) $vehicle->v_ID],
            'v_no'                      => ['S' => (string) $vehicle->v_No],
            'Is_Live'                   => ['N' => '1'],
            'AWS_SNS_ARN'               => ['S' => (string) $driver->awssnsarn],
            'FCM_ID'                    => ['S' => (string) $driver->gcmregstid],
            'DriverId'                  => ['N' => (string) $driver->d_ID],
            'DriverBranchId'            => ['N' => (string) $driver->br_id],
            'DriverName'                => ['S' => (string) $driver->d_Name],
            'DriverPhone'               => ['S' => (string) $driver->d_Ph1],  
            'v_type'                    => ['N' => (string) $vehicle->vhty_id],  
            'v_capacity'                => ['N' => (string) $vehicle->vehicleType->vhty_MaxCapacity],  
            'v_typename'                => ['S' => (string) $vehicle->vehicleType->vhty_name], 
            'v_MapIcon'                 => ['S' => (string) $vehicle->vehicleType->vhty_Icon], 
            'CurrentLoadedWeight'       => ['N' => '0'],
            'CurrentLoadedVolume'       => ['N' => '0'],
            'AssignedLoadedWeight'      => ['N' => '0'],
            'AssignedLoadedVolume'      => ['N' => '0'],
            'RatePerKm'                 => ['N' => '0'], 
            'Home_Latitude'             => ['N' => (string) $driver->d_HomeLati], 
            'Home_Longitude'            => ['N' => (string) $driver->d_HomeLong],
            'Rating'                    => ['S' => (string) $driver->d_Rating],  
            'mobno'                     => ['S' => (string) $driver->d_Ph1],  
            'ReportingBranch'           => ['N' => (string) $driver->br_id],  
            'DeliveryRange'             => ['N' => (string) $driver->d_DeliveryRange],  
            'MarkedNextBkId'            => ['N' => '0'],  
            'MarkedNextBrId'            => ['N' => '0'], 
            'IsEngaged'                 => ['N' => '0'], 
            'OnJobCompletionLatitude'   => ['N' => '0'], 
            'OnJobCompletionLongitude'  => ['N' => '0'], 
            'isallowManualSchedule'     => ['N' => (string) $driver->d_isallowManualSchedule], 
            'isallowAutoSchedule'       => ['N' => (string) $driver->d_isallowAutoSchedule], 
            'createdBy'                 => ['N' => (string) $driver->createdBy], 
            'sourceId'                  => ['N' => (string) $driver->sourceId],  
        ];
        return $item;
    }
}
