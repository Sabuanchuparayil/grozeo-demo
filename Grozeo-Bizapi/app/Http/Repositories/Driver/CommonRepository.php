<?php
namespace App\Http\Repositories\Driver;

use App\Models\Order;
use App\Models\Drivers\QugeoDeliveryStatus;
use App\Models\Drivers\FirebaseLog;
use App\Models\Drivers\QugeoDriver;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
use App\Http\Repositories\Driver\PullPendingOrderRepository;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Aws\S3\S3Client;
use Aws\DynamoDb\DynamoDbClient;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use App\Traits\Driver\CommonTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\DriverResource;

class CommonRepository
{
    use CommonTrait;
    public function __construct()
    {
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
    }

    public function failedStatuses($request)
    {
     
        try {
        
            $failedStatusIds = $request['ispickup'] === 'true' ? [37, 36, 35] : [10, 11, 12, 13, 14];
    
            $failedStatuses = QugeoDeliveryStatus::whereIn('dls_ID', $failedStatusIds)
                ->orderBy('dls_DelStatus', 'asc')
                ->select(['dls_ID as id', 'dls_DelStatus as name'])
                ->get();

            return new SuccessWithData($failedStatuses->toArray());
    
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage()); 
        }
        
    }
    
    public function getNotifications($request)
    {
        try {

            $currentTime = Carbon::now();
            $threeMinutesAgo = $currentTime->subMinutes(3);

            $polledNotification = FirebaseLog::select('rfir_payload as pollednotification')->where('rfir_StatusId', 1)
                ->where('rfir_token', $request->input('token'))
                ->where('rfir_date', '>=', $threeMinutesAgo->format('Y-m-d H:i:s'))
                ->orderByDesc('id')
                ->first();
                    
            if ($polledNotification !== null) {
               
                return new SuccessWithData($polledNotification->pollednotification);
            } else {
                return new SuccessResponse('No recent notification found');
            }
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function s3Details()
    {
        try {
            $urls = [];
    
            $file_name = sha1(microtime(true) . mt_rand(10000, 90000));
            $s3 = new S3Client([
                'region'        => Config::get('constant.AWSS3ASSETUPLOADREGION'),
                'version'       => 'latest',
                'credentials'   => [
                    'key'           => Config::get('constant.AWSS3ASSETUPLOADACCESSID'),
                    'secret'        => Config::get('constant.AWSS3ASSETUPLOADSECRETKEY'),
                ]
            ]);
    
            $cmdSignature = $s3->getCommand('PutObject', [
                'Bucket' => Config::get('constant.AWSBUCKETNAME'),
                'Key'       => 'drive/' . md5('ymdHisu') . '.jpg',
                'ACL' => 'public-read'
            ]);
    
            $requestSignature = $s3->createPresignedRequest($cmdSignature, '+20 minutes');
            $presignedSignatureUrl = (string)$requestSignature->getUri();
            $signatureUrl = strtok($presignedSignatureUrl, '?');
            $urls[] = ["type" => "signature", "presignedUrl" => $presignedSignatureUrl, "imageurl" => $signatureUrl];
    
            $cmdPhoto = $s3->getCommand('PutObject', [
                'Bucket' => Config::get('constant.AWSBUCKETNAME'),
                'Key'       => 'drive/' . md5('ymdHisu') . '.jpg',
                'ACL' => 'public-read'
            ]);
            $requestImage = $s3->createPresignedRequest($cmdPhoto, '+20 minutes');
            $presignedImageUrl = (string)$requestImage->getUri();
            $imageUrl = strtok($presignedImageUrl, '?');
            $urls[] = ["type" => "photo", "presignedUrl" => $presignedImageUrl, "imageurl" => $imageUrl];
    
            $jsonString = json_encode($urls);
            $object = json_decode($jsonString);
    
            return new SuccessWithData($object);
        } catch (AwsException $e) {
            // Log the error for debugging purposes
            \Log::error('AWS S3 Error: ' . $e->getMessage());
    
            return new ErrorResponse('Failed to create URL: ' . $e->getMessage());
        }
    }

    public function logout()
    {
               
        $item= [

            'apikey' => [
                'Action' => 'PUT',
                'Value' => ['S' => '-']
            ],
        ];
        
        $uprs= $this->dynamoClient->updateItem([
            'TableName' =>config('aws.prefix') . 'APISession',
            'Key' => [
                'usertype' => ['N' => '2'],
                'id' => ['S' => (string) auth_user()->d_ID],
            ],

            'AttributeUpdates' =>$item
        ]);

        $datetime = date("YmdHis");
        $arrSession =[];
        $arrSession= [

            'HasLoggedOut' => [
                'Action' => 'PUT',
                'Value' => ['N' => (string)1]
            ],
            'LoggedOutAt' => [
                'Action' => 'PUT',
                'Value' => ['S' => (string)$datetime]
            ],
            'IsCleanLogout' => [
                'Action' => 'PUT',
                'Value' => ['N' =>(string)1]
            ],
        ];
        $uprs= $this->dynamoClient->updateItem([
            'TableName' =>config('aws.prefix') . 'APIHistory',
            'Key' => [
                'apikey' => ['S' => (string) auth_user()->d_apikey],
            ],

            'AttributeUpdates' =>$arrSession
        ]);

        $kmscovered = $this->getKMInaTrip(auth_user()->d_apikey);
        $arrUpdate=[];

        $arrUpdate= [

            'Is_Live' => [
                'Action' => 'PUT',
                'Value' => ['N' => (string)0]
            ],
            'LoggedOutAt' => [
                'Action' => 'PUT',
                'Value' => ['S' => (string)$datetime]
            ],
            'IsCleanLogout' => [
                'Action' => 'PUT',
                'Value' => ['N' =>(string)1]
            ],
            'KmsCovered' => [
                'Action' => 'PUT',
                'Value' => ['N' =>(string)$kmscovered]
            ],
        ];
       
        $uprs= $this->dynamoClient->updateItem([
            'TableName' =>config('aws.prefix') . 'QugeoLiveVehicles',
            'Key' => [
                'apikey' => ['S' => (string) auth_user()->d_apikey],
            ],

            'AttributeUpdates' =>$arrUpdate
        ]);

      
        $driver=QugeoDriver::where('d_ID',auth_user()->d_ID)->first();
        $driver->Update(['d_apikey'=>'-']);

        JWTAuth::invalidate(JWTAuth::getToken());
        
        return new SuccessResponse('You have been successfully logged out');
				
    }
    public function getKMInaTrip($apikey)
    {
        $kmscovered =0; 
        $nors = $this->dynamoClient->getItem([
            'TableName' => config('aws.prefix') . 'QugeoLiveVehicles',
            'Key' => [
                'apikey' => ['S' => $apikey],
            ],
            'AttributesToGet' => [
                'Home_Latitude','Home_Longitude'
            ]
            ]);
        if(isset($nors) && count($nors) > 0 ){    	
            $HomeLatitude = $nors['Item']['Home_Latitude']['N']??0;
            $HomeLongitude = $nors['Item']['Home_Longitude']['N']??0;
        }
      
        $params = [
            'TableName' =>config('aws.prefix') . 'QugeoLiveVehicleOrders',
            'KeyConditionExpression' => 'apikey = :val',
            'ExpressionAttributeValues' => [
                ':val' => ['S' => $apikey],
            ],
            'ProjectionExpression' => 'orderid, #o, Latitude, Longitude, IsClosed, IsLiveOrder, IsPickup, IsMilestoneLock', // Using '#o' as an expression attribute name for 'order'
            'ExpressionAttributeNames' => [
                '#o' => 'order', 
            ],
        ];

        $result = $this->dynamoClient->query($params);
        $items=$result['Items']??[];
        if(isset($items) && count($items) > 0 )
        {  
           
            foreach ($items as $nors)
            {
                
                $CurrentOrders = [];
                
                $CurrentOrders[] = [
                    'Latitude' => $nors['Latitude']['N']??0,
                    'Longitude' => $nors['Longitude']['N']??0,
                    'IsClosed' => $nors['IsClosed']['N'],
                    'order' => $nors['order']['N'] ?? $nors['order']['S'] ?? 0,
                ];
                
                
                $prevlatlong =array();
                $prevlatlong['Latitude'] = 0;
                $prevlatlong['Longitude'] = 0;	
                $pull= new PullPendingOrderRepository()	;			
            
                if($nors['IsClosed']['N']=='1'||$nors['IsClosed']['N']==1){							
                    if ($prevlatlong['Latitude'] !=0){
                        $dist = $pull->GetDrivingDistance($prevlatlong['Latitude'],$nors['Latitude']['N']??0,$prevlatlong['Longitude'],$nors['Longitude']['N']??0);
                        $kmscovered = $kmscovered + $dist;
                    }
                    $prevlatlong['Latitude'] =$nors['Latitude']['N']??0;
                    $prevlatlong['Longitude'] =$nors['Longitude']['N']??0;
                }
            
                if($kmscovered>0 && $HomeLatitude !=0){
                    $dist = $pull->GetDrivingDistance($prevlatlong['Latitude'],$HomeLatitude,$prevlatlong['Longitude'],$HomeLongitude);
                    $kmscovered = $kmscovered + $dist;
                }
            }
        }


        return $kmscovered;
    }
    public function getSnapRoad($request)
    {
        try {
            $apikey = $request->vehapikey;
            $params = [
                'TableName' => config('aws.prefix') . 'QugeoEventGeoLocations',
                'KeyConditionExpression' => 'apikey = :val',
                'ExpressionAttributeValues' => [
                    ':val' => ['S' => $apikey],
                ],
                'ProjectionExpression' => 'longitude, latitude, tstamp, userdatetime, disttravled, provider, version',
            ];
    
            $result = $this->dynamoClient->query($params);
            $items = $result['Items'];
    
            $arr = [];
            foreach ($items as $item) {
                $latitude = $item['latitude']['N'];
                $longitude = $item['longitude']['N'];
                $tstamp = substr($item['tstamp']['N'], 0, 14);
                $apptime = $item['userdatetime']['S'];
                $disttravled = $item['disttravled']['N'];
                $version = $item['version']['S'];
                $provider = $item['provider']['S'];
    
                $arr[] = [
                    'Latitude' => $latitude,
                    'Longitude' => $longitude,
                    'tstamp' => $tstamp,
                    'apptime' => $apptime,
                    'disttravled' => $disttravled,
                    'version' => $version,
                    'provider' => $provider,
                ];
            }
    
            // Sort the array by 'apptime'
            usort($arr, function ($a, $b) {
                return strtotime($a['apptime']) - strtotime($b['apptime']);
            });
    
            $batchSize = 100;
            $chunks = array_chunk($arr, $batchSize);
    
            $road_arr = [];
            foreach ($chunks as $chunk) {
                $param = implode('|', array_map(function ($value) {
                    return $value['Latitude'] . ',' . $value['Longitude'];
                }, $chunk));
    
                $road_data = $this->getSnapToRoad($param);
                if (!empty($road_data)) {
                    $road_arr = array_merge($road_arr, $road_data);
                }
            }
    
            $response = [];
            foreach ($road_arr as $key => $val) {
                array_push($response, [
                    'Latitude' => $val['location']['latitude'],
                    'Longitude' => $val['location']['longitude'],
                    'tstamp' => '20161104131415',
                    'apptime' => '20161104131415',
                    'disttravled' => 0,
                    'version' => '1',
                    'provider' => 'ABC',
                ]);
            }
    
            if (!empty($response)) {
                $polyLine = json_encode($response);
    
                return response()->json(['success' => true, 'polylines' => $polyLine]);
            } else {
                return response()->json(['success' => true, 'polylines' => '[]']);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getSnapToRoad($geocoords)
    {
        $url = "https://roads.googleapis.com/v1/snapToRoads?&interpolate=true&key=" . config('constant.GMAP_DIST_API_KEY') . "&path=" . $geocoords;
    
        $response = $this->curlGetRequest($url);
    
        $response_a = json_decode($response, true);

        if (isset($response_a['snappedPoints'])) {
            return $response_a['snappedPoints'];
        } else {
            throw new \Exception('Invalid response from Google Maps API');
        }
    }
    
    public function getOrderDetails($orderID)
    {
        try
        {
            $order = Order::from("retaline_customer_order as rc")
            ->select($this->orderSelectFields())
            ->join('qugeo_order as qo', 'qo.quor_RefNo', 'rc.order_order_id')
            ->join('retaline_customer_order_status as rs', 'rs.status_id', 'rc.status_id')
            ->where('order_order_id', $orderID)
            ->with([
                'orderStatus:status_id,customer_description,stage_id',
                'branchDetails:br_ID,br_Name,br_Address,br_Address2,br_Address3,br_City,br_District,br_State,br_Phone,br_Lat,br_Lng',
                'branchDetails.state:st_ID,st_name',
                'branchDetails.district:dst_Id,dst_Name',
                'deliveryAddress:id,customer_order_id,order_customer_name,order_contact_no,order_house_no,order_house_name,order_address,order_address2,order_land_mark,order_city,order_state,order_pin,order_latitude,order_longitude',
                'drive:quor_id,quor_RefNo,quor_TransferOrder_id,quor_PacketCount,quor_Status,quor_DeliveredTime',
                'drive.deliveryStatus:dls_ID,dls_DelStatus',
                'drive.details',
                'packing:fsto_id,fstr_id',
                'packing.packDetails:rtopd_id,rtopd_fstoId,rtopd_packets,rtpod_length,rtpod_breadth,rtpod_height,rtopd_packetweigh'
            ])
            ->first();
            if($order)
            {
                $params = [
                    "TableName"                 => config("aws.prefix")."QugeoOrderDetails",
                    "IndexName"                 => "quor_id-index",
                    "KeyConditionExpression"    => "quor_id = :quor_id",
                    "ExpressionAttributeValues" => [
                        ":quor_id"  => ["N" => (string)$order->drive->quor_id]
                    ],
                ];
                $response = $this->dynamoClient->query($params);
                $item = reset($response['Items']);
                $order->DDBid = @$item['orderid']['S'];
            }
            return new SuccessWithData($order);
        }
        catch(\Excepion $e)
        {
            return new ErrorResponse("Operation failed");
        }
    }
    /**
     * Get current logged in driver details
    */
    public function getDriverDetails()
    {
        $driver = auth_user();
        return new SuccessWithData(new DriverResource($driver));
    }

    private function orderSelectFields()
    {
        return [
            'order_id',
            'order_order_id',
            'order_branch_id',
            'order_customer_id',
            'order_amount_payable',
            'total',
            'rc.status_id',
            DB::raw('
                CASE 
                    WHEN rs.stage_id = 5 THEN qo.quor_PickupSMS
                    WHEN rs.stage_id = 6 THEN qo.quor_DeliverySMS
                    ELSE 0
                END AS otp
            ')
        ];
    }
}
