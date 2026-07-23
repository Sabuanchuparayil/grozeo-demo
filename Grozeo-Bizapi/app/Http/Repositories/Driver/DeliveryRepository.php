<?php
namespace App\Http\Repositories\Driver;

use App\Models\{
    Order,
    DeliveryInfo,
    OrderAddress,
    Drivers\QugeoOrder
};
use App\Http\Responses\{
    ErrorResponse,
    SuccessResponse,
    SuccessWithData
};
use BackOffice\Status\{
    QugeoStatus,
    CustomerOrderStatus
};
use App\Events\OrderHistory;
use Illuminate\Http\Request;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;
use App\Traits\Driver\LocationTrait;
use App\Events\DelayedOrderActions;
use App\Http\Repositories\PostingRepository;
use App\Http\Repositories\PartnerOrderUpdateRepository;
use BackOffice\Http\Repositories\ActivityLogRepository;

class DeliveryRepository
{
    use LocationTrait;
    public function __construct()
    {
        $this->postingRepo = new PostingRepository();
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
        $this->activtyLog = new ActivityLogRepository();
    }

    public function startDelivery($request)
    {
        try
        {
            $driver = auth_user();
            $order = $this->checkOrder($request->order_id, $driver, true);
            if(!$order)
            {
                return new ErrorResponse("Order not available");
            }
            $this->updateLocation($request->location, [
                "order_id"  => $request->order_id,
                "event"     => "Delivery Start",
                "driver"    => $driver
            ]);
            $order->status_id = CustomerOrderStatus::OUT_FOR_DELIVERY;
            $order->save();
            event(new OrderHistory($order->order_id, CustomerOrderStatus::OUT_FOR_DELIVERY, "Order picked up"));

            $fields = [
                'quor_PickedupTime'     => date("Y-m-d H:i:s"),
                'quor_DeliveryDriverId' => $driver->d_ID
            ];
            $this->updateDrive($order, $driver, QugeoStatus::ORDER_PICKUP_PICKEDUP_TODST_DLS_ID, $fields);
            $this->updateEvent($order, config("event_master.pickupForDelivery"));
            event(new DelayedOrderActions(@$order->order_id, 6));

            return new SuccessResponse("Order delivery started");
        }
        catch(\Exception $e)
        {
            info("DeliveryRepository => startDelivery() Error");info($e);
            return new ErrorResponse("Operation failed");
        }
    }
    public function failedDelivery($request)
    {
        try
        {
            $driver = auth_user();
            $order = $this->checkOrder($request->order_id, $driver);
            if(!$order)
            {
                return new ErrorResponse("Order not available");
            }
            if(!in_array($request->failure_id, $this->failureIDs()))
            {
                return new ErrorResponse("Invalid Status");
            }
            $this->updateLocation($request->location, [
                "order_id"  => $request->order_id,
                "event"     => "Delivery Failed",
                "driver"    => $driver
            ]);
            QugeoOrder::where('quor_id', $order->drive->quor_id)->update([
                'quor_Status'           => $request->failure_id,
                'quor_PickedupTime'     => date("Y-m-d H:i:s"),
                'quor_DeliveryDriverId' => 0
            ]);
            $order->status_id = CustomerOrderStatus::DELIVERY_FAILED;
            $order->save();
            // Update DDB
            $DDBOrderID = $this->getDDB($order->drive->quor_id);
            if($DDBOrderID)
            {
                $this->dynamoClient->updateItem([
                    'TableName' =>config('aws.prefix') . 'QugeoOrderDetails',
                    'Key' => [
                        'orderid' => ['S' => $DDBOrderID],
                    ],
                    'ExpressionAttributeValues' => [
                            ':OrderStatus'      => ['N' => (string)$request->failure_id],
                            ':FailedReasonID'   => ['N' => (string)$request->failure_id]
                        ],
                    'UpdateExpression'          => 'SET OrderStatus=:OrderStatus, FailedReasonID=:FailedReasonID'
                ]);
            }
            return new SuccessResponse("Order marked as failed");
        }
        catch(\Exception $e)
        {
            info("DeliveryRepository => failedDelivery() Error");info($e);
            return new ErrorResponse("Operation failed");
        }
    }
    public function completeDelivery($request)
    {
        try
        {
            $driver = auth_user();
            $order = $this->checkOrder($request->order_id, $driver);
            if(!$order)
            {
                return new ErrorResponse("Order not available");
            }
            $this->updateLocation($request->location, [
                "order_id"  => $request->order_id,
                "event"     => "Delivery Completed",
                "driver"    => $driver
            ]);
            $payMode = $request->payments["mode"];
            switch($payMode)
            {
                case -1:
                    return $this->orderDeliveryComplete($order, $request, $driver);
                    break;
                case 0:
                case 1:
                    return $this->partialDeliveryComplete($order, $request, $driver);
                    break;
                
                default:
                    return new ErrorResponse("Operation failed");
                    break;
            }
        }
        catch(\Exception $e)
        {
            info("DeliveryRepository => completeDelivery() Error");info($e);
            return new ErrorResponse("Operation failed");
        }
    }
    public function updateDeliveryLocation($request)
    {
        try
        {
            $driver = auth_user();
            $order = $this->checkOrder($request->order_id, $driver);
            if(!$order)
            {
                return new ErrorResponse("Order not available");
            }
            $updateAddr = 0;
            DB::transaction(function () use ($request, $order, &$updateAddr)
            {
                $updateAddr += OrderAddress::where('order_id', $order->order_order_id)->update([
                    "order_latitude"    => $request->location['latitude'],
                    "order_longitude"   => $request->location['longitude']
                ]);
                $updateAddr += DeliveryInfo::where('deli_id', $order->deliveryAddress->deli_id)->update([
                    "deli_latitude"    => $request->location['latitude'],
                    "deli_longitude"   => $request->location['longitude']
                ]);
            });
            if($updateAddr != 2)
            {
                return new ErrorResponse("Some error occured");
            }
            $description = "Customer Address Change: Driver {$driver->d_Name} {$driver->l_Name}({$driver->d_ID}) updated delivery location of Order ID: {$order->order_order_id} from ({$order->deliveryAddress->order_latitude}, {$order->deliveryAddress->order_longitude}) to ({$request->location['latitude']}, {$request->location['longitude']}).";
            $this->activtyLog->insertActivityLog([
                "source"        => "Grozeo Drive",
                "User"          => "{$driver->d_Name} {$driver->l_Name}({$driver->d_ID})",
                "Description"   => $description,
            ]);
            return new SuccessResponse("Delivery Location Updated");

        }
        catch(\Exception $e)
        {
            info("DeliveryRepository => updateDeliveryLocation() Error");info($e);
            return new ErrorResponse("Operation failed");
        }
    }

    private function checkOrder($orderID, $driver, $start = false)
    {
        $order = Order::from("retaline_customer_order as rc")
        ->join("retaline_customer_order_status as rs", "rs.status_id", "rc.status_id")
        ->join('qugeo_order as qo', 'qo.quor_RefNo', 'rc.order_order_id')
        ->where([
            ['order_order_id', $orderID],
            ["rs.stage_id", 6]
        ])
        ->where(function ($query) use ($driver) {
            $query->where('qo.quor_Pickupbr_id', $driver->br_id)->orWhere('qo.quor_Deliverybr_id', $driver->br_id);
        });
        if($start)
        {
            $order->where(function ($query) use ($driver) {
                $query->whereIn('qo.quor_DeliveryDriverId', [NULL, 0, $driver->d_ID]);
            });
        }
        else
        {
            $order->where('quor_DeliveryDriverId', $driver->d_ID);
        }
        return $order->first();
    }
    private function getDDB($quor_id)
    {
        $params = [
            "TableName"                 => config("aws.prefix")."QugeoOrderDetails",
            "IndexName"                 => "quor_id-index",
            "KeyConditionExpression"    => "quor_id = :quor_id",
            "ExpressionAttributeValues" => [
                ":quor_id"  => ["N" => (string)$quor_id]
            ],
        ];
        $response = $this->dynamoClient->query($params);
        if($response['Count'] > 0)
        {
            $item = reset($response['Items']);
            return @$item['orderid']['S'];
        }
        return NULL;
    }
    private function failureIDs()
    {
        return [
            QugeoStatus::ORDER_PICKUP_FAILED_DOOR_LOCKED_DLS_ID,
            QugeoStatus::ORDER_PICKUP_FAILED_ADDRESS_NOT_FOUND_DLS_ID,
            QugeoStatus::ORDER_PICKUP_FAILED_PARCEL_NOT_READY_DLS_ID,
            QugeoStatus::ORDER_DELIVERY_FAILED_DOOR_LOCKED_DLS_ID,
            QugeoStatus::ORDER_DELIVERY_FAILED_REFUSED_DLS_ID,
            QugeoStatus::ORDER_DELIVERY_FAILED_ADDRESS_NOT_FOUND_DLS_ID,
            QugeoStatus::ORDER_INCOMPLETE_DELIVERY,
            QugeoStatus::ORDER_DELIVERY_FAILED_DAMAGED_DLS_ID
        ];
    }
    private function updateEvent($order, $eventID)
    {
        $postReq = new Request();
        $postReq->setMethod('POST');
        $postReq->request->add([
            'order_id'              => $order->order_id,
            'finascopEventRefId'    => $eventID,
            'storegroup_id'         => (@$order->storegroup_id ? $order->storegroup_id : 0)
        ]);
        $this->postingRepo->finascopPosting($postReq);
    }
    private function orderDeliveryComplete($order, $request, $driver)
    {
        $order->status_id = CustomerOrderStatus::DELIVERED;
        $order->order_DeliveryDriver = @$driver->d_ID;
        $order->order_DeliveryDriverNumber = @$driver->d_Ph1;
        $order->save();
        event(new OrderHistory($order->order_id, CustomerOrderStatus::DELIVERED, "Order delivered by Drive => {$driver->d_ID}"));
        $deliveredDate = date("Y-m-d H:i:s");
        $fields = [
            "quor_Type"             => 1,
            "quor_DeliveredTime"    => $deliveredDate,
            "quor_signature"        => @$request->verification["signature"],
            "quor_image"            => @$request->verification["photo"]
        ];
        $ddbFields = [
            "expressionVals"    => [
                ":IsPickup"  => ['N' => "0"],
                ":Signature" => ['S' => (@$request->verification["signature"] ?? "")],
                ":Photo"     => ['S' => (@$request->verification["photo"] ?? "")]
            ],
            "expression"        => "IsPickup=:IsPickup, Signature=:Signature, Photo=:Photo"
        ];
        $this->updateDrive($order, $driver, QugeoStatus::ORDER_DELIVERY_COMPLETED_DLS_ID, $fields, $ddbFields);

        DB::select("CALL UpdateDeliveryStatus({$order->drive->quor_id}, {$order->order_id}, '{$deliveredDate}')");

        $this->updateEvent($order, config("event_master.deliveryConfirmation"));
        event(new DelayedOrderActions(@$order->order_id, 7));

        return new SuccessResponse("Order Delivered");

    }
    private function partialDeliveryComplete($order, $request, $driver)
    {
        $order->status_id = CustomerOrderStatus::DELIVERED_NOT_CONFIRMED;
        $order->order_DeliveryDriver = @$driver->d_ID;
        $order->order_DeliveryDriverNumber = @$driver->d_Ph1;
        $order->order_ondel_bankref_id = @$request->payments["reference_id"];
        $order->save();
        event(new OrderHistory($order->order_id, CustomerOrderStatus::DELIVERED_NOT_CONFIRMED, "Order delivered by Drive => {$driver->d_ID}"));
        $deliveredDate = date("Y-m-d H:i:s");
        $fields = [
            "quor_Type"             => 1,
            "quor_DeliveredTime"    => $deliveredDate,
            "quor_signature"        => @$request->verification["signature"],
            "quor_image"            => @$request->verification["photo"]
        ];
        $ddbFields = [
            "expressionVals"    => [
                ":IsPickup"  => ['N' => "0"],
                ":Signature" => ['S' => (@$request->verification["signature"] ?? "")],
                ":Photo"     => ['S' => (@$request->verification["photo"] ?? "")]
            ],
            "expression"        => "IsPickup=:IsPickup, Signature=:Signature, Photo=:Photo"
        ];
        $this->updateDrive($order, $driver, QugeoStatus::ORDER_DELIVERY_MARKED_DLS_ID, $fields, $ddbFields);
        event(new DelayedOrderActions(@$order->order_id, 7));

        return new SuccessResponse("Order marked as delivered.");
    }
    private function updateDrive($order, $driver, $status, $fields = [], $ddbFields = [])
    {
        $fields["quor_Status"] = $status;
        QugeoOrder::where('quor_id', $order->drive->quor_id)->update($fields);

        // Update DDB
        $DDBOrderID = $this->getDDB($order->drive->quor_id);
        if($DDBOrderID)
        {
            $expressionVals = @$ddbFields["expressionVals"];
            $expressionVals[":OrderStatus"] = ['N' => (string)$status];

            $expression = [@$ddbFields["expression"]];
            array_push($expression, "OrderStatus=:OrderStatus");
            $expression = implode(", ", array_filter($expression));

            $this->dynamoClient->updateItem([
                'TableName' =>config('aws.prefix') . 'QugeoOrderDetails',
                'Key' => [
                    'orderid' => ['S' => $DDBOrderID],
                ],
                'ExpressionAttributeValues' => $expressionVals,
                'UpdateExpression'          => "SET {$expression}"
            ]);
        }
    }
}
