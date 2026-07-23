<?php

namespace App\Http\Repositories\Driver;

use App\Models\Drivers\QugeoDriver;
use App\Http\Responses\{
    SuccessResponse,
    SuccessWithData,
    ErrorResponse
};
use App\Http\Resources\DriverAuthResource;
use Aws\DynamoDb\DynamoDbClient;

class DriverAuthRepository
{
    protected $driver;

    public function __construct(QugeoDriver $driver)
    {
        $this->driver = $driver;
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
    }

    public function driverAuthentication($request)
    {
        try {
          
            $driverCheck = $this->driver->where([
                ['d_Ph1', $request['user_id']],
                ['d_Active', 1]
            ])->select('d_Name', 'd_ID', 'd_Add1', 'd_Add2', 'd_Add3', 'd_licence', 'd_licenceexpairy', 'gcmregstid', 'd_awssnsarn', 'd_apikey')
                ->first();

            if (!$driverCheck) {
                return new ErrorResponse('Driver not found');
            }

            if ($driverCheck->gcmregstid !== $request['fcm_token']) {
                return new ErrorResponse('Invalid GCM registration ID');
            }

            $token = createJwtToken($driverCheck);
            $driverCheck->update(['d_apikey' => $token]);

            $result = $this->dynamoClient->putItem([
                'TableName' => config('aws.prefix') . 'APISession',
                'Item' => [
                    'lastlogindate' => ['S' => (string) now()],
                    'apikey' => ['S' => (string) $token],
                    'lastlogindatetime' => ['S' => (string) now()],
                    'validtill' => ['S' => (string) now()->addHours(1)->timestamp],
                    'date' => ['S' => (string) now()],
                    'usertype' => ['N' =>  '2'],
                    'id' => ['S' => (string) $driverCheck['d_ID']],
                    'branchid' => ['S' => (string) $driverCheck['br_id']],
                ]
            ]);

            if (!$result) {
                return new ErrorResponse('API Key Generation failed for APISession');
            }


            $result = $this->dynamoClient->putItem([
                'TableName' => config('aws.prefix') . 'APIHistory',
                'Item' => [
                    'HasLoggedOut' => ['N' => (string) 0],
                    'apikey' => ['S' => (string) $token],
                    'createddatetime' => ['S' => (string) now()],
                    'createddate' => ['N' => (string) now()->timestamp],
                    'date' => ['S' => (string) now()],
                    'usertype' => ['N' => '2'],
                    'id' => ['S' => (string) $driverCheck['d_ID']],
                ]
            ]);

            if (!$result) {
                return new ErrorResponse('API Key Generation failed for APIHistory');
            }

            $arrAuth = [
                'apikey' => $token,
                'Name' => $driverCheck['d_Name'],
                'IsSessionResumed' => false,
                'HasOrder' => false,
                'nextorderdetails' => [],
                'istriprerouted' => false,
                'mapdetails' => []
            ];
            return new SuccessWithData($arrAuth);

        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
    }
}
