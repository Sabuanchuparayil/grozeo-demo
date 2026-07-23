<?php

namespace App\Http\Repositories\Driver;
use Aws\DynamoDb\Exception\DynamoDbException;
use App\Http\Responses\{
    SuccessResponse,
    SuccessWithData,
    ErrorResponse
};
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class GeoLocationRepository
{

    /**
     * Update driver's location
     *
     * @param  string  $geocords
     * @return \App\Http\Responses\ErrorResponse
     */

     public function updateLocation($geocords)
     {
         try {
             $driver = auth_user();
             $apikey = $driver->d_apikey;
             $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
             $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
             $extrainfo = ["event" => "locationupdate"];
             $tableName = config('aws.prefix') . 'QugeoEventGeoLocations';
             $valdate = date("Ymd");
             $valdatetime = date("YmdHis");
     
             $geocordsarr = json_decode(json_encode($geocords));
             foreach ($geocordsarr->details as $geocord) {
                 if (intval($geocord->latitude) == 0 || intval($geocord->longitude) == 0) {
                     throw new \Exception('Invalid Geo-Coords  Lat - "' . intval($geocord->latitude) . '" - Long - "' . intval($geocord->longitude) . '"');
                 }
     
                 // Put item into grozeodev_QugeoEventGeoLocations DynamoDB
                 $result = $dynamoClient->putItem([
                     'TableName' => $tableName,
                     'Item' =>  [
                         'uuid' => ['S' => (string) $uuid],
                         'apikey' => ['S' => (string) $apikey],
                         'latitude' => ['N' => (string) $geocord->latitude],
                         'longitude' => ['N' => (string) $geocord->longitude],
                         'bearing' => ['N' => (string) $geocord->bearing],
                         'date' => ['N' => $valdate],
                         'usertype' => ['N' => '2'],
                         'userid' => ['N' => (string) $driver->d_ID],
                         'extrainfo' => ['S' => json_encode($extrainfo)],
                         'userdatetime' => ['S' => (string) $geocord->userdatetime],
                         'disttravled' => ['N' => (string) $geocord->disttravled],
                         'tstamp' => ['N' => $valdate],
                         'currentdatetime' => ['S' => (string) $geocordsarr->currentdatetime],
                         'provider' => ['S' => (string) $geocord->provider],
                         'version' => ['S' => (string) $geocordsarr->version],
                     ]
                 ]);
     
                 // Update live vehicles
                 $tableName =  config('aws.prefix') . 'QugeoLiveVehicles';
                 $dynamoClient->updateItem([
                     'TableName' => $tableName,
                     'Key' => [
                         'apikey' => ['S' => $apikey],
                     ],
                     'AttributeUpdates' => [
                         'LocationUpdateddatetime' => [
                             'Action' => 'PUT',
                             'Value' => ['N' => (string) $valdatetime]
                         ],
                         'Latitude' => [
                             'Action' => 'PUT',
                             'Value' => ['N' => (string) $geocord->latitude]
                         ],
                         'Longitude' => [
                             'Action' => 'PUT',
                             'Value' => ['N' => (string) $geocord->longitude]
                         ],
                         'bearing' => [
                             'Action' => 'PUT',
                             'Value' => ['N' => (string) $geocord->bearing]
                         ]
                     ]
                 ]);
     
                 return new SuccessResponse('Location Updated');
             }
         } catch (\Exception $e) {
             return new ErrorResponse($e->getMessage());
         }
     }
     

}