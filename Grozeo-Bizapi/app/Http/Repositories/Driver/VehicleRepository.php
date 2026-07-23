<?php

namespace App\Http\Repositories\Driver;
use App\Models\Drivers\QugeoVehicleType;
use App\Models\Drivers\DriverVehicles;
use App\Models\Drivers\QugeoVehicle;
use App\Http\Responses\{
    SuccessResponse,
    SuccessWithData,
    ErrorResponse
};
use App\Models\QugeoEventGeoLocations;
use App\Models\QugeoLiveVehicles;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;
use App\Traits\Driver\LocationTrait;
use App\Http\Repositories\Driver\GeoLocationRepository;
use App\Http\Resources\{
    VehicleResource,
    VehiclesResource
};
class VehicleRepository
{
    use LocationTrait;
    protected $vehicle;

    public function __construct(QugeoVehicleType $vehicle)
    {
        $this->vehicle = $vehicle;
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));

    }

    public function getVehicleTypes()
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
    public function getLastVehicles()
    {
        $driver = auth_user();
        try
        {
            $lastVehicles = DriverVehicles::select('v_ID as id', 'v_no as regno', 'qugeo_vehicletype.vhty_id as v_type', 'vhty_name')
            ->join('qugeo_vehicletype', 'qugeo_vehicletype.vhty_id', '=', 'qugeo_drivervehicle.vhty_id')
            ->where('d_ID', auth_user()->d_ID)
            ->where('v_active', 1)
            ->groupBy('qugeo_vehicletype.vhty_id')
            ->orderByDesc('dv_id')
            ->get();
        
        if (!$lastVehicles->isEmpty()) {
            return response()->json([
                'status' => 'ok',
                'msg' => 'Success',
                'data' => $lastVehicles,
                'cleanlogout' => true
            ]);
        } else {
            return response()->json(['status' => 'error', 'error' => ['msg' => 'No vehicles found']], 404);
        }
        }
        catch (\Exception $e)
        {
        return new ErrorResponse($e->getMessage());
        }
    }

    public function selectVehicle($request)
    {
        try {
            $driverDetails = auth_user();

            DB::beginTransaction();
            $extrainfo = ["event" => "vehicleselected"];
       
            $this->updateLocation($request->geocoords,$extrainfo);
            $this->updateAPISession($request);
            $this->updateAPISessionHistory($request);
            $this->updateLiveVehicle($request);

            $response = [
                ['title' => 'Your license is valid till', 'value' => $driverDetails['license']]
            ];
            if ($request->input("vehicleid") > 0) {
                $availableVehicle = QugeoVehicle::select('dt_insurance as insurance', 'dt_fitness as fitness')
                    ->where('v_ID', $request->input("vehicleid"))
                    ->first();

                if ($availableVehicle) {
                    $response[] = ['title' => 'Vehicle Insurance expires on ', 'value' => $availableVehicle->insurance];
                    $response[] = ['title' => 'Vehicle Fitness is valid till ', 'value' => $availableVehicle->fitness];
                }
            }

            DriverVehicles::create([
                'd_ID' => $driverDetails['d_ID'],
                'v_ID' => $request->input("vehicleid"),
                'v_No' => $request->input("vehicleregno"),
                'lastused' => now(),
                'vhty_id' => $request->input('vehicletype')
            ]);

            DB::commit();
            return new SuccessWithData($response);
        } catch (\Exception $e) {
            DB::rollBack();
            return new ErrorResponse($e->getMessage());
        }
    }

    /**
     * List vehicles based on auth_user()
     * List based on driver id(d_ID) and active(v_active)
     * sort by dv_id
     * Format the api response using VehiclesResource
    */
    public function vehicleList()
    {
        try
        {
            $driver = auth_user();
            $getVehicles = DriverVehicles::where([
                ['d_ID', auth_user()->d_ID],
                ['v_active', 1]
            ])->paginate(10);

            // transform vehicle list data to user friendly VehicleResource response.
            $getVehicles->getCollection()->transform(function ($vehicle) {
                return new VehiclesResource($vehicle);
            });
            return new SuccessWithData($getVehicles);
        }
        catch(\Exception $e)
        {
            info("vehicleList() Exception => ");info($e);
            return new ErrorResponse("Operation failed");
        }
    }

    /**
     * Add vehicle repo
     * recieves request(vehicle reg no, vehicle type id and location[lat and long])
     * Response success/failed status
    */
    public function addVehicle($request)
    {
        try
        {
            $driver = auth_user();
            $addVehicle = DriverVehicles::create([
                'd_ID'          => $driver->d_ID,
                'v_ID'          => 0,
                'v_No'          => $request->input("registration"),
                'lastused'      => now(),
                'vhty_id'       => $request->input('type'),
                'is_primary'    => 0
            ]);
            if(!$addVehicle)
            {
                return new ErrorResponse("Unable to add vehicle. Some error occured.");
            }
            $extrainfo = ["event" => "vehicleselected"];
       
            $this->updateLocation($request->location, $extrainfo);
            $this->updateAPISession(["vehicleid" => 0, "vehicleregno" => $request->input("registration")]);
            $this->updateAPISessionHistory(["vehicleid" => 0, "vehicleregno" => $request->input("registration")]);
            $this->updateLiveVehicleData($driver, $addVehicle, $request);
            
            $this->updatePrimaryVehicle($addVehicle);
            return new SuccessResponse("New Vehicle Added");
        }
        catch(\Exception $e)
        {
            info("addVehicle() Exception => ");info($e);
            return new ErrorResponse("Operation failed");
        }
    }

    /**
     * Select vehicle repo
     * recieves vehicle id
     * Response success/failed status
    */
    public function chooseVehicle($vehicleID)
    {
        try
        {
            $driver = auth_user();
            $checkVehicle = DriverVehicles::where([
                ['dv_id', $vehicleID],
                ['d_ID', $driver->d_ID],
                ['v_active', 1]
            ])->first();
            if(!$checkVehicle)
            {
                return new ErrorResponse("Vehicle not available.");
            }
            $this->updateAPISession(["vehicleid" => 0, "vehicleregno" => $checkVehicle->v_No]);
            $this->updateAPISessionHistory(["vehicleid" => 0, "vehicleregno" => $checkVehicle->v_No]);
            $this->updatePrimaryVehicle($checkVehicle);
            return new SuccessResponse("Set Vehicle {$checkVehicle->v_No} to Primary.");
        }
        catch(\Exception $e)
        {
            info("chooseVehicle() Exception => ");info($e);
            return new ErrorResponse("Operation failed");
        }
    }
    // Setting selected vehicle as primary
    private function updatePrimaryVehicle($vehicle)
    {
        $driver = auth_user();
        DB::transaction(function () use ($driver, $vehicle) {
            // Reset all vehicles
            $driver->vehicles()->update(['is_primary' => 0]);

            // Mark this one as primary
            $vehicle->update(['is_primary' => 1]);
        });
    }
    // add vehicle to the dynamodb tables
    private function updateLiveVehicleData($driver, $vehicle, $request)
    {
        $item = $this->generateDDBData($driver, $vehicle, $request);
        // Add vehicle details to dynamodb QugeoLiveVehicles
        $result = $this->dynamoClient->putItem([
            'TableName' => config('aws.prefix') . 'QugeoLiveVehicles',
            'Item'      => $item
      
        ]);
        // Add vehicle details to dynamodb QugeoLiveVehiclesHistory
        $liveHistory=$this->dynamoClient->putItem([
            'TableName' => config('aws.prefix') . 'QugeoLiveVehiclesHistory',
            'Item'      => $item
        ]);
    }
    private function generateDDBData($driver, $vehicle, $request)
    {
        $valdate = date("Ymd");
        $valdatetime = date("YmdHis");
        $item= [
            'apikey'                    => ['S' => (string) $driver->d_apikey],
            'createddatetime'           => ['N' => (string) $valdatetime],
            'createddate'               => ['N' => (string) $valdate],
            'LocationUpdateddatetime'   => ['N' => (string) $valdatetime],
            'latitude'                  => ['N' => (string) $request->location["latitude"]],
            'longitude'                 => ['N' => (string) $request->location["longitude"]],
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

    // Update APISession table with vehicle used
    private function updateAPISession($request)
    {
        $driver = auth_user();
       
        $result=$this->dynamoClient->updateItem([
            'TableName' => config('aws.prefix') . 'APISession',
            'Key' => [
                'usertype' => ['N' => '2'],
                'id' => ['S' => (string) $driver->d_ID],
            ],
            'ExpressionAttributeValues' => [
                ':val' => ['M' => [
                    'v_id' => ['S' => (string) $request["vehicleid"]],
                    'v_no' => ['S' => (string) $request["vehicleregno"]],
                ]],
            ],
            'UpdateExpression' => 'SET extrainfo = :val',
        ]);

        if(!$result)
        {
            return new ErrorResponse("Failed to update location");
        }
    }

    
    //Update APISession History table with vehicle used
    private function updateAPISessionHistory($request)
    {
        
        $driver = auth_user();
        $history=$this->dynamoClient->updateItem([
            'TableName' => config('aws.prefix') . 'APIHistory',
            'Key' => [
                'apikey' => ['S' =>(string) $driver->d_apikey],
            ],
            'ExpressionAttributeValues' => [
                ':val' => ['M' => [
                    'v_id' => ['S' => (string) $request["vehicleid"]],
                    'v_no' => ['S' => (string) $request["vehicleregno"]],
                ]],
            ],
            'UpdateExpression' => 'SET extrainfo = :val',
        ]);

        if (!$history) {
            return new ErrorResponse("Unable to update API Session Archive");
        }

    }

    // Insert into live vehicle
    private function updateLiveVehicle($request)  // to be removed in later versions
    {
        
        $driverdetails = auth_user();
        $arrSession = array();
        $arrSession['Data'] = array();
        $valdate = date("Ymd");
        $valdatetime = date("YmdHis");
        $vehicledetails = QugeoVehicleType::select('vhty_MaxCapacity as capacity', DB::raw('0 as Rate'), 'vhty_name as name', 'vhty_Icon')
        ->where('vhty_id', $request->input('vehicletype'))
        ->first();

        $item= [
            'apikey' => ['S' => (string) $driverdetails['d_apikey']],
            'createddatetime' => ['N' => (string) $valdatetime],
            'createddate' => ['N' => (string) $valdate],
            'LocationUpdateddatetime' => ['N' => (string) $valdatetime],
            'latitude' => ['N' => (string) $request["geocoords"]['latitude']],
            'longitude' => ['N' => (string)$request["geocoords"]['longitude']],
            'v_id' => ['S' => (string) $request["vehicleid"]],
            'v_no' => ['S' => (string) $request["vehicleregno"]],
            'Is_Live' => ['N' => '1'],
            'AWS_SNS_ARN' => ['S' => (string) $driverdetails['awssnsarn']],
            'FCM_ID' => ['S' => (string) $driverdetails['gcmregstid']],
            'DriverId' => ['N' => (string) $driverdetails['d_ID']],
            'DriverBranchId' => ['N' => (string) $driverdetails['br_id']],
            'DriverName' => ['S' => (string) $driverdetails['d_Name']],
            'DriverPhone' => ['S' => (string) $driverdetails['d_Ph1']],  
            'v_type' => ['N' => (string) $request['vehicletype']],  
            'v_capacity' => ['N' => (string) $vehicledetails['capacity']],  
            'v_typename' => ['S' => (string) $vehicledetails['name']], 
            'v_MapIcon' => ['S' => (string) $vehicledetails['vhty_Icon']], 
            'CurrentLoadedWeight' => ['N' => '0'],
            'CurrentLoadedVolume' => ['N' => '0'],
            'AssignedLoadedWeight' => ['N' => '0'],
            'AssignedLoadedVolume' => ['N' => '0'],
            'RatePerKm' => ['N' => (string) $vehicledetails['Rate']], 
            'Home_Latitude' => ['N' => (string)  $driverdetails['d_HomeLati']], 
            'Home_Longitude' => ['N' => (string)  $driverdetails['d_HomeLong']],
            'Rating' => ['S' => (string)  $driverdetails['d_Rating']],  
            'mobno' => ['S' => (string)  $driverdetails['d_Ph1']],  
            'ReportingBranch' => ['N' => (string)  $driverdetails['br_id']],  
            'DeliveryRange' => ['N' => (string)  $driverdetails['d_DeliveryRange']],  
            'MarkedNextBkId' => ['N' => '0'],  
            'MarkedNextBrId' => ['N' => '0'], 
            'IsEngaged' => ['N' => '0'], 
            'OnJobCompletionLatitude' => ['N' => '0'], 
            'OnJobCompletionLongitude' => ['N' => '0'], 
            'isallowManualSchedule' => ['N' => (string)  $driverdetails['d_isallowManualSchedule']], 
            'isallowAutoSchedule' => ['N' => (string)  $driverdetails['d_isallowAutoSchedule']], 
            'createdBy' => ['N' => (string)  $driverdetails['createdBy']], 
            'sourceId' => ['N' => (string)  $driverdetails['sourceId']],  
    
        ];

      

        $result = $this->dynamoClient->putItem([
            'TableName' => config('aws.prefix') . 'QugeoLiveVehicles',
            'Item' =>$item
      
        ]);

        if(!$result)
        {
            return new ErrorResponse("Failed to update Live Vehicle");
        }

        //  inserting to history table
        $liveHistory=$this->dynamoClient->putItem([
            'TableName' => config('aws.prefix') . 'QugeoLiveVehiclesHistory',
            'Item' =>$item
        ]);
        if(!$liveHistory)
        {
            return new ErrorResponse("Failed to update location");
        }
        
    }

}
