<?php

namespace App\Http\Repositories\Driver;

use App\Http\Responses\ErrorResponse;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\Log;
class ProceedRepository
{
    protected $dynamoClient;

    public function __construct()
    {
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
    }

    public function proceedOrder($request)
    {
        try {
            $arrSession = [];
            $sendOrderIds = json_decode($request->input("orderid"), true);
            $pollclosed = $this->pollResponse($request->input('msgid'), 1, false, $acceptedorder);
    
            if ($pollclosed && $acceptedorder) {
                $arrSession['success'] = true;
                $arrSession['msg'] = 'Poll closed';
                $arrSession['Data']['orderid'] = array_reverse($sendOrderIds['orderid']);
            }

            return $arrSession;
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
    }

    public function pollResponse($pollid, $pollresponse, $delivertobranch, &$acceptedorder)
    {
        $arrUpdate = [
            'isclosed' => ['Action' => 'PUT', 'Value' => ['N' => '1']],
            'closedat' => ['Action' => 'PUT', 'Value' => ['S' => date("YmdHis")]],
        ];

        if ($pollresponse == 1) {
            $arrUpdate['currentstatus'] = ['Action' => 'PUT', 'Value' => ['S' => 'ACCEPTED']];
            $arrUpdate['acceptedfor'] = ['Action' => 'PUT', 'Value' => ['S' => $delivertobranch ? 'BRANCH' : 'DIRECT']];
            $acceptedorder = true;
        } else {
            $acceptedorder = false;
            $arrUpdate['currentstatus'] = ['Action' => 'PUT', 'Value' => ['S' => $pollresponse == 2 ? 'REJECTED' : 'NORESPONSE']];
        }

        $response = $this->dynamoClient->updateItem([
            'TableName' =>config('aws.prefix') . 'QugeoOrderPollingDetails',
            'Key' => ['pollingid' => ['S' => $pollid]],
            'AttributeUpdates' => $arrUpdate,
        ]);

        return count($response) > 0;
    }
}
