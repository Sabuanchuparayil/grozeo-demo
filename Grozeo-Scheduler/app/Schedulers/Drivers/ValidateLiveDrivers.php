<?php

namespace App\Schedulers\Drivers;

use App\Models\ProcessLock;
use App\Helpers\HttpCurlCalls;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;
use App\Models\Drivers\QugeoDriver;

class ValidateLiveDrivers
{
    public function __invoke()
    {
        try
        {
            $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
            $keepaliveTime = config('drivers.keepalive_timeout') ?? 14400;
            $isLive = "1";
            $cutOffTime = date("YmdHis", strtotime(date("YmdHis")) - $keepaliveTime);
            $params = [
                'TableName'                 => config('aws.prefix').'QugeoLiveVehicles',
                'FilterExpression'          => 'LocationUpdateddatetime < :LocationUpdateddatetime AND Is_Live = :isLive',
                'ExpressionAttributeValues' => [
                    ':LocationUpdateddatetime'  => ['N' => (string)$cutOffTime],
                    ':isLive'                   => ['N' => $isLive]
                ],
            ];
            $liveVehicles = [];
            do
            {
                $result = $dynamoClient->scan($params);
                $liveVehicles = array_merge($liveVehicles, $result['Items']);
                $params['ExclusiveStartKey'] = $result['LastEvaluatedKey'];
            } while (!empty($params['ExclusiveStartKey']));
            foreach ($liveVehicles as $lv)
            {
                $datetime = date("YmdHis");
                // LOGOUT USER
                $result = $dynamoClient->updateItem([
                    'TableName'                 => config('aws.prefix').'QugeoLiveVehicles',
                    'Key'                       => [
                        'apikey'    => $lv['apikey']
                    ],
                    'ExpressionAttributeNames' => [
                        '#Is_Live'          => 'Is_Live',
                        '#LoggedOutAt'      => 'LoggedOutAt',
                        '#IsCleanLogout'    => 'IsCleanLogout',
                    ],
                    'ExpressionAttributeValues' => [
                        ':Is_Live'          => ['N' => '0'],
                        ':LoggedOutAt'      => ['S' => (string)$datetime],
                        ':IsCleanLogout'    => ['N' => '3']
                    ],
                    'UpdateExpression'          => 'SET #Is_Live=:Is_Live, #LoggedOutAt=:LoggedOutAt, #IsCleanLogout=:IsCleanLogout'
                ]);
                //UPDATE APIHistory
                $result = $dynamoClient->updateItem([
                    'TableName'                 => config('aws.prefix').'APIHistory',
                    'Key'                       => [
                        'apikey'    => $lv['apikey']
                    ],
                    'ExpressionAttributeNames' => [
                        '#HasLoggedOut'     => 'HasLoggedOut',
                        '#LoggedOutAt'      => 'LoggedOutAt',
                        '#IsCleanLogout'    => 'IsCleanLogout',
                    ],
                    'ExpressionAttributeValues' => [
                        ':HasLoggedOut'     => ['N' => '1'],
                        ':LoggedOutAt'      => ['S' => (string)$datetime],
                        ':IsCleanLogout'    => ['N' => '0']
                    ],
                    'UpdateExpression'          => 'SET #HasLoggedOut=:HasLoggedOut, #LoggedOutAt=:LoggedOutAt, #IsCleanLogout=:IsCleanLogout'
                ]);
                //UPDATE APISession
                $result = $dynamoClient->updateItem([
                    'TableName'                 => config('aws.prefix').'APISession',
                    'Key'                       => [
                        'usertype'  => ["N" => "2"],
                        'id'        => ["S" => $lv['DriverId']['N']]
                    ],
                    'ExpressionAttributeNames' => [
                        '#apikey'     => 'apikey'
                    ],
                    'ExpressionAttributeValues' => [
                        ':apikey'     => ['S' => " - "]
                    ],
                    'UpdateExpression'          => 'SET #apikey = :apikey'
                ]);
                //UPDATE QugeoDriver
                $result = QugeoDriver::where('d_ID', $lv['DriverId']['N'])->update([
                    'd_apikey'  => '-'
                ]);
            }
            ProcessLock::updateColData("BizAPI_ValidateLiveDrivers", 0);
        }
        catch (\Exception $e)
        {
            info("ValidateLiveDrivers SCHEDULER => {$e->getMessage()}");
            info($e);
            ProcessLock::updateColData("BizAPI_ValidateLiveDrivers", 1);
        }
    }
}
