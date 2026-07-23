<?php

namespace App\Http\Repositories\Driver;

use App\Models\Drivers\QugeoOrder;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
use Aws\DynamoDb\DynamoDbClient;
use App\Sms\SmsSender;
class MileStoneRepository
{
    /**
     * Get driver's delivered orders
     */
    public function __construct()
    {
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
    }
    public function milestone($request)
    {
        try {
            $arrSession = [];
            if ($request['milestone'] == 50) {

                $nors = $this->dynamoClient->getItem([
                    'TableName' =>config('aws.prefix') . 'QugeoOrderDetails',
                    'Key' => [
                        'orderid' => ['S' => $request['orderid']],
                    ],
                    'AttributesToGet' => [
                        'pickupmobile', 'quor_RefNo', 'pickupname', 'deliveryname', 'deliverymobile', 'IsPickup', 'pickupOTP', 'IsMilestoneLock', 'deliveryOTP'
    
                    ]
                ]);
                
                if ($nors != false) {
                    $IsMilestoneLock  = (@$nors['Item']['IsMilestoneLock']['N'] ? $nors['Item']['IsMilestoneLock']['N'] : @$nors['Item']['IsMilestoneLock']['S']);
                    if ($IsMilestoneLock == '0') {
                     
                        $item=[];
                        $item['IsMilestoneLock'] = ['Action' => 'PUT', 'Value' => ['N' => (string)1]];
                   
                        $NewOrder = $this->dynamoClient->updateItem([
                            'TableName' =>config('aws.prefix') . 'QugeoLiveVehicleOrders',
                            'Key' => [
                                'apikey' => ['S' => (string) auth_user()->d_apikey],
                                'orderid' => ['S' => $request['orderid']]
                            ],
                            'AttributeUpdates' =>$item
                        ]);

                    }
                    $str = "Your " . config('constant.PROJECT_NAME') . " Order No." . $nors['Item']['quor_RefNo']['S'] . " is arriving to you soon. Please provide the OTP " . $nors['Item']['deliveryOTP']['S'] . " to our delivery partner  on request.";
                  
                    $templatedata['order_order_id'] = $nors['Item']['quor_RefNo']['S'];
                    $templatedata['otp'] = $nors['Item']['deliveryOTP']['S'];
                    app(SmsSender::class)->fetchContentSendSms($templatedata, $nors['deliverymobile'], 7);

                    $item = [];
                    $item['IsMilestoneLock'] = ['Action' => 'PUT', 'Value' => ['N' => (string)1]];
                    $item['MilestoneCovered'] = ['Action' => 'PUT', 'Value' => ['N' => (string)$request['milestone']]];
                   
                    $this->dynamoClient->updateItem([
                        'TableName' =>config('aws.prefix') . 'QugeoOrderDetails',
                        'Key' => [
                            'orderid' => ['S' => $request['orderid']],
                        ],
                        'AttributeUpdates' =>$item
                    ]);

                    $arrSession['success'] = true;
                    $arrSession['msg'] = 'Milestone action done';
                }
                else {
                    $arrSession['msg'] = 'Milestone action error';
                }

            }
            else
            {
                $arrSession['success'] = true;
                $arrSession['msg'] = 'Milestone action completed';
            }

            $arrSession['Data']['milestone'] = array();
            return $arrSession;
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
    }
}
