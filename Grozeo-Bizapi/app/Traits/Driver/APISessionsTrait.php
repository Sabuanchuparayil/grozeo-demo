<?php
namespace App\Traits\Driver;

use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;

trait APISessionsTrait
{
    // add vehicle to the dynamodb tables
    private function addAPISessions($driver, $location)
    {
        // initialize dynamodb class
        $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
        // default vehicle number
        $vehRegNo = @$driver->primaryVehicle->v_No ? $driver->primaryVehicle->v_No : "-";
        // add api session
        $result = $this->dynamoClient->putItem([
            'TableName' => config('aws.prefix').'APISession',
            'Item'      => [
                'lastlogindate'     => ['S' => (string)now()],
                'apikey'            => ['S' => (string)$driver->d_apikey],
                'lastlogindatetime' => ['S' => (string)now()],
                'validtill'         => ['S' => (string)now()->addHours(1)->timestamp],
                'date'              => ['S' => (string)now()],
                'usertype'          => ['N' =>  '2'],
                'id'                => ['S' => (string)$driver->d_ID],
                'branchid'          => ['S' => (string)$driver->br_id],
                'extrainfo'         => ['M' => [
                    'v_id' => ['S' => '0'],
                    'v_no' => ['S' => (string) $vehRegNo],
                ]]
            ]
        ]);
        // add api session history
        $result = $this->dynamoClient->putItem([
            'TableName' => config('aws.prefix').'APIHistory',
            'Item'      => [
                'HasLoggedOut'      => ['N' => '0'],
                'apikey'            => ['S' => (string)$driver->d_apikey],
                'createddatetime'   => ['S' => (string)now()],
                'createddate'       => ['N' => (string)now()->timestamp],
                'date'              => ['S' => (string)now()],
                'usertype'          => ['N' => '2'],
                'id'                => ['S' => (string)$driver->d_ID],
                'extrainfo'         => ['M' => [
                    'v_id' => ['S' => '0'],
                    'v_no' => ['S' => (string)$vehRegNo],
                ]]
            ]
        ]);
    }
}
