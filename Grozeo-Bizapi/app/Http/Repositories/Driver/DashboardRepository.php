<?php
namespace App\Http\Repositories\Driver;

use App\Models\Order;
use App\Http\Responses\{
    ErrorResponse,
    SuccessResponse,
    SuccessWithData
};
use Aws\DynamoDb\DynamoDbClient;
use BackOffice\Models\{
    QugeoOrder,
    Drivers\QugeoDriver
};
use BackOffice\Status\CustomerOrderStatus;

class DashboardRepository
{
    protected $dynamoClient;
    public function __construct()
    {
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
    }

    public function getDashboardData()
    {
        $driver = auth_user();

        if (!$driver)
        {
            return new ErrorResponse("Driver not available.");
        }
        if ($driver->createdBy <= 1)
        {
            $pendingOrders = $this->getStoreOrderList($driver);
        }
        elseif ($driver->createdBy == 2)
        {
            $pendingOrders = $this->getOtherOrderList($driver);
        }
        $inProgressOrders = $this->getInProgressOrders($driver);
        // $inProgressOrdersToday = $this->getInProgressOrders($driver, 1);
        $deliveredToday = $this->getDeliveredOrders($driver, date('Y-m-d'));
        $delivered = $this->getDeliveredOrders($driver);
        $outs = [
            "inHandToday"       => $inProgressOrders,
            "deliveredToday"    => @$deliveredToday->count ?? 0,
            "delivered"         => @$delivered->count ?? 0,
            "cashInHandToday"   => @$deliveredToday->cashInHand ?? 0,
            "cashInHand"        => @$delivered->cashInHand ?? 0,
            "pending"           => $pendingOrders,
            "accepted"          => $inProgressOrders,
            "rejected"          => 0,
            "missed"            => 0,
            "pickupDistance"    => 0,
            "deliveryDistance"  => 0,
            "returnDistance"    => 0,
            "totalDistance"     => $this->totalDistanceTravelled($driver)
        ];
        return new SuccessWithData($outs);
    }

    private function getStoreOrderList($driver, $type = 0)
    {
        $orders = Order::from("retaline_customer_order as rc")
        ->join("retaline_customer_order_status as rs", "rs.status_id", "rc.status_id")
        ->join('qugeo_order as qo', 'qo.quor_RefNo', 'rc.order_order_id')
        ->join('finascop_branch as fb', 'fb.br_ID', 'rc.order_branch_id');
        $this->getConditionByType($driver, $orders);
        return $orders->count();
    }
    private function getConditionByType($driver, $query)
    {
        $query->where("rs.stage_id", 5);
        if($driver->createdBy <= 1)
        {
            $query->where("rc.order_branch_id", $driver->br_id);
        }
        elseif($driver->createdBy == 2)
        {
            $driverDetails = $this->dynamoClient->getItem([
                'TableName' =>config('aws.prefix') . 'QugeoLiveVehicles',
                'Key' => [
                'apikey' => ['S' => $driver->d_apikey],
                ],
                'ProjectionExpression' => 'Latitude, Longitude, Home_Latitude, Home_Longitude, DeliveryRange, AssignedLoadedWeight, AssignedLoadedVolume, CurrentLoadedVolume, CurrentLoadedWeight, TotalJobs, DriverName, mobno',
            ]);

            if(isset($driverDetails['Item']['Latitude']['N']))
            {
                $lat = $driverDetails['Item']['Latitude']['N'];
                $lon = $driverDetails['Item']['Longitude']['N'];
                $distance = $driverDetails['DeliveryRange'];
                $R = 6371;
                $maxLat = $lat + rad2deg($distance / $R);
                $minLat = $lat - rad2deg($distance / $R);
                $maxLon = $lon + rad2deg(asin($distance / $R) / cos(deg2rad($lat)));
                $minLon = $lon - rad2deg(asin($distance / $R) / cos(deg2rad($lat)));

                $query->whereBetween('fb.br_Lat', [$minLat, $maxLat])->whereBetween('fb.br_Lng', [$minLon, $maxLon]);
            }
        }
    }
    private function getOtherOrderList($driver, $type = 0)
    {
        $outs = 0;
        $driverDetails = $this->dynamoClient->getItem([
            'TableName' =>config('aws.prefix') . 'QugeoLiveVehicles',
            'Key' => [
                'apikey' => ['S' => $driver['d_apikey']],
            ],
            'ProjectionExpression' => 'Latitude, Longitude, Home_Latitude, Home_Longitude, DeliveryRange, AssignedLoadedWeight, AssignedLoadedVolume, CurrentLoadedVolume, CurrentLoadedWeight, TotalJobs, DriverName, mobno',
        ]);

        if(isset($driverDetails['Item']['Latitude']['N']))
        {
            $lat = $driverDetails['Item']['Latitude']['N'];
            $lon = $driverDetails['Item']['Longitude']['N'];
            $distance = $driverDetails['DeliveryRange'];
            $R = 6371;
            $maxLat = $lat + rad2deg($distance / $R);
            $minLat = $lat - rad2deg($distance / $R);
            $maxLon = $lon + rad2deg(asin($distance / $R) / cos(deg2rad($lat)));
            $minLon = $lon - rad2deg(asin($distance / $R) / cos(deg2rad($lat)));

            $outs = QugeoOrder::join('finascop_branch', 'finascop_branch.br_ID', '=', 'qugeo_order.quor_Pickupbr_id')
            ->join('retaline_delivery_rules as rdr', function ($join) {
                $join->on('rdr.rdr_id', '=', 'finascop_branch.br_rdrIdExpress')
                    ->where('rdr.rdr_ruleFor', '=', 1);
            })
            ->where('qugeo_order.quor_slot_id', 0)
            ->where('qugeo_order.quor_DeliveryMethodsAllowed', '&', 1);
            if($type == 0)
            {
                $outs = $outs->where(function ($query, $driver) use ($minLat, $minLon, $maxLat, $maxLon) {
                    $query->whereBetween('finascop_branch.br_Lat', [$minLat, $maxLat])
                    ->whereBetween('finascop_branch.br_Lng', [$minLon, $maxLon])
                    ->orWhere(function ($query, $driver) {
                        $query->whereIn('qugeo_order.quor_Status', [22, 24, 27, 31])
                            ->orWhere(function ($query, $driver) {
                                $query->whereIn('qugeo_order.quor_Status', [23, 27, 9, 32])
                                    ->where('qugeo_order.quor_DeliveryDriverId', auth_user()->d_ID);
                            });
                    });
                })->count();
            }
            else
            {
                $outs = $outs->where(function ($query, $driver) use ($minLat, $minLon, $maxLat, $maxLon) {
                    $query->whereBetween('finascop_branch.br_Lat', [$minLat, $maxLat])
                    ->whereBetween('finascop_branch.br_Lng', [$minLon, $maxLon])
                    ->orWhere(function ($query, $driver)
                    {
                        $query->where('qugeo_order.quor_Status', 9)
                        ->where('qugeo_order.quor_DeliveryDriverId', $driver->d_ID);
                    });
                })->count();
            }
        }
        return $outs;
    }
    private function getInProgressOrders($driver, $type = 0)
    {
        $orders = Order::from("retaline_customer_order as rc")->select('rc.order_id')
        ->join("retaline_customer_order_status as rs", "rs.status_id", "rc.status_id")
        ->join('qugeo_order as qo', 'qo.quor_RefNo', 'rc.order_order_id')
        ->where("rs.stage_id", 6)
        ->whereNotIn("rc.status_id", [CustomerOrderStatus::DELIVERY_FAILED, CustomerOrderStatus::DELIVERED_NOT_CONFIRMED, CustomerOrderStatus::DELIVERED])
        ->where(function ($query) use ($driver) {
            $query->where('qo.quor_DeliveryDriverId', $driver->d_ID)->orWhere('qo.quor_PickupDriverId', $driver->d_ID);
        });
        if($type == 0)
        {
            return $orders->count();
        }
        return $orders->whereDate('qo.quor_CreatedOn', date("Y-m-d"))->count();
    }

    private function getDeliveredOrders($driver, $today = "")
    {
        $delivered = Order::from("retaline_customer_order as rc")
        ->selectRaw("
            COUNT(rc.order_id) as count,
            SUM(
                IF(rc.status_id=".CustomerOrderStatus::DELIVERED_NOT_CONFIRMED.", quor_AmountCollectible, 0)
            ) AS cashInHand"
        )
        ->join('qugeo_order as qo', 'qo.quor_RefNo', 'rc.order_order_id')
        ->where([
            ['qo.quor_Type', 1],
            ['qo.quor_DeliveryDriverId', $driver->d_ID],
        ])
        ->whereIn('rc.status_id', [CustomerOrderStatus::DELIVERED_NOT_CONFIRMED, CustomerOrderStatus::DELIVERED]);
        if($today != "")
        {
            $delivered = $delivered->whereDate('qo.quor_DeliveredTime', $today);
        }
        return $delivered->first();
    }

    private function totalDistanceTravelled($driver)
    { 
        $params = [
            'TableName'                 => config('aws.prefix').'QugeoLiveVehicles',
            'FilterExpression'          => "DriverId = :DriverId",
            'ExpressionAttributeValues' => [
                ':DriverId' => ['N' => (string)$driver->d_ID],
            ]
        ];
        $vehicleDetails = $this->dynamoClient->scan($params);
        $vehicleDetails = [];
        do
        {
            $result = $this->dynamoClient->scan($params);
            $vehicleDetails = array_merge($vehicleDetails, $result['Items']);
            $params['ExclusiveStartKey'] = $result['LastEvaluatedKey'];
        } while (!empty($params['ExclusiveStartKey']));
        $sum = 0;
        foreach ($vehicleDetails as $vd)
        {
            $sum += $vd['N']['KmsCovered'] ?? 0;
        }
        return $sum;
    }
}
