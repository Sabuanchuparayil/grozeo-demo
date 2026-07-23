<?php

namespace App\Traits\Driver;
use App\Http\Responses\ErrorResponse;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;
trait LocationTrait
{
    public function updateLocation($geocoords, $extrainfo, $driver = NULL)
    {
        $driver = is_null($driver) ? auth_user() : $driver;
        $apikey = $driver->d_apikey;
        $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
        $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
        $tableName =config('aws.prefix') . 'QugeoEventGeoLocations';
        $valdate = date("Ymd");
        $valdatetime = date("YmdHis");
        $result = $dynamoClient->putItem([
            'TableName' => $tableName,
            'Item' =>  [
                'uuid' => ['S' => (string) $uuid],
                'apikey' => ['S' => (string) $apikey],
                'latitude' => ['N' => (string) $geocoords['latitude']],
                'longitude' => ['N' => (string) $geocoords['longitude']],
                'date' => ['N' => $valdate],
                'usertype' => ['N' => '2'],
                'userid' => ['N' => (string) $driver->d_ID],
                'extrainfo' => ['S' => json_encode($extrainfo)],
                'tstamp' => ['N' => $valdate],
            ]
        ]);
        if(!$result)
        {
            return new ErrorResponse("Failed to update location");
        }

    }
   
}
