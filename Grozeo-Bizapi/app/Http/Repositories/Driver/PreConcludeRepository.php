<?php

namespace App\Http\Repositories\Driver;

use App\Models\Drivers\QugeoOrder;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
use Aws\DynamoDb\DynamoDbClient;
use App\Sms\SmsSender;
class PreConcludeRepository
{
    /**
     * Get driver's delivered orders
     */
    public function __construct()
    {
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
    }
    public function preConcludeOrder($request)
    {
        try {
         
            $orderid = $request["orderid"];
            $item= [
                'IsPickup' => [
                    'Action' => 'PUT',
                    'Value' => ['N' => (string) 0]
                ],
                'Signature' => [
                    'Action' => 'PUT',
                    'Value' => ['S' => (string) $request['signature_path']]
                ],
                'Photo' => [
                    'Action' => 'PUT',
                    'Value' => ['S' => (string) $request['photo_path']]
                ],
            ];
            
            $uprs= $this->dynamoClient->updateItem([
                'TableName' =>config('aws.prefix') . 'QugeoOrderDetails',
                'Key' => [
                    'orderid' => ['S' => $orderid],
                ],

                'AttributeUpdates' =>$item
            ]);


            $nors = $this->dynamoClient->getItem([
                'TableName' =>config('aws.prefix') . 'QugeoOrderDetails',
                'Key' => [
                    'orderid' => ['S' => $orderid],
                ],
                'AttributesToGet' => [
                    'quor_id'

                ]
            ]);
            
            //save images of signature / image
            $order = QugeoOrder::where('quor_id', $nors['Item']['quor_id']['N'])->first();
            if ($order && !empty($request['signature_path'])) {
                $order->update(['quor_signature' => $request['signature_path']]);
            }
            
            if ($order && !empty($request['photo_path'])) {
                $order->update(['quor_image' => $request['photo_path']]);
            }
            
            if ($uprs == false) {
                throw new \Exception('No response of update on QugeoOrderDetails, throws error');
            }
            $arrSession['success'] = true;
            $arrSession['msg'] = 'Confirm Delivery';
            $arrSession['Data']['confirm'] = array();
            return $arrSession;

        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
    }
}
