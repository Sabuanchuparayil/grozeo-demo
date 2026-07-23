<?php
namespace App\Http\Repositories\Driver;

use App\Models\Order;
use App\Models\Drivers\QugeoDriver;
use App\Models\Drivers\QugeoOrder;
use App\Http\Responses\{
    SuccessResponse,
    SuccessWithData,
    ErrorResponse
};
use Illuminate\Support\Facades\DB;
use Aws\DynamoDb\DynamoDbClient;

class PendingOrderRepository
{

    /**
     * Get driver's pending orders
     */
    public function __construct()
    {
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
    }
    public function pendingOrders()
    {
        $driver = auth_user();

        $orders = Order::from("retaline_customer_order as rc")
        ->select($this->selectFields())
        ->join("retaline_customer_order_status as rs", "rs.status_id", "rc.status_id")
        ->join('qugeo_order as qo', 'qo.quor_RefNo', 'rc.order_order_id')
        ->join('finascop_branch as fb', 'fb.br_ID', 'rc.order_branch_id')
        ->with([
            'orderStatus:status_id,customer_description',
            'branchDetails:br_ID,br_Name,br_District,br_State,br_Phone,br_Lat,br_Lng',
            'branchDetails.state:st_ID,st_name',
            'branchDetails.district:dst_Id,dst_Name',
            'deliveryAddress:id,customer_order_id,order_customer_name,order_contact_no,order_latitude,order_longitude',
        ]);
        $this->getConditionByType($driver, $orders);
        $orders = $orders->paginate(10);

        return new SuccessWithData($orders);
    }

    private function selectFields()
    {
        return [
            "order_id",
            "order_order_id",
            "rc.status_id",
            "order_branch_id",
            'order_customer_id',
            'total',
            "rs.stage_id",
            DB::raw('
                qo.quor_Status, IF(qo.quor_Status=22, "PICKUP", "DELIVERY") as drivetype,
                qo.quor_CreatedOn as createdDate
            '),
        ];
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
}
