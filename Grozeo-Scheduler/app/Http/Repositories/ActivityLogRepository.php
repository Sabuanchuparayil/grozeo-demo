<?php

namespace App\Http\Repositories;

use Illuminate\Support\Facades\DB;
use Aws\DynamoDb\DynamoDbClient;

class ActivityLogRepository
{
    public function insertActivityLog($request)
    {
        try
        {
            $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
		
            $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
            $dynamoClient->putItem([
            'TableName' => config('aws.prefix').'activitylogs',
                'Item'      => [
                    'uuid'          => ['S' => (string)$uuid],
                    'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                    'source'        => ['S' => (string)$request['source']],
                    'User'          => ['S' => (string)$request['User']],
                    'Description'   => ['S' => (string)$request['Description']],
                    'storegroupid'  => ['N' => '0']
                ]
            ]);
        }
        catch (\Exception $e)
        {
            info("ActivityLogRepository ERROR---");info($e);
        }
    }
}
