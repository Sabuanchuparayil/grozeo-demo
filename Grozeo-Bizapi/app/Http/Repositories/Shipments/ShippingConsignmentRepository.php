<?php
namespace App\Http\Repositories\Shipments;

use App\Models\{
    Order,
    OrderHistory,
    FinanceAutopostingValues
};
use Illuminate\Http\Request;
use Aws\DynamoDb\DynamoDbClient;
use BackOffice\Models\QugeoOrder;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\{
    ErrorResponse,
    SuccessResponse,
    SuccessWithData
};
use App\Models\CourierDelivery\{
    ShippingConsignment as SCModel,
    ConsignmentTracking as TrackModel
};
use App\Http\Repositories\PostingRepository;


class ShippingConsignmentRepository
{
    public function shipmentDeliveredWebhook($request, $partner)
    {
        /*try
        {*/
            $partner = config('courierpartners.default');
            $shipping = config("courierpartners.{$partner}.sClass");
            $shipper = new $shipping();

            $consignment = SCModel::select('order_id', 'shipping_id', 'tracking_id')->where([
                ['tracking_id', $request['tracking_no']],
                ['shipping_type', $partner]
            ])->first();
            $orderID = NULL;
            $status = "Repeating";
            if($consignment)
            {
                $orderID = $consignment->order_id;
                $order = Order::where([
                    ['order_order_id', $consignment->order_id],
                    ['status_id', '!=', 18]
                ])->first();
                if($order)
                {
                    $status = "Delivered";
                    $qugeoOrderUpdate = QugeoOrder::where('quor_RefNo', $consignment->order_id)->update([
                        'quor_Type'             => 4,
                        'quor_DeliveryConfTime' => $request['delivery_date'],
                        'quor_Status'           => 15,
                        'quor_UpdateOn'         => now()
                    ]);

                    $qugeoOrder = QugeoOrder::where('quor_RefNo', $consignment->order_id)->first();
                    $orderProcedure = DB::query("CALL UpdateDeliveryStatus({$qugeoOrder->quor_id}, {$order->order_id}, '{$request['delivery_date']}')");
                    $orderHistory = OrderHistory::create([
                        'order_id'      => $order->order_id,
                        'order_action'  => "Shipment Delivered {$consignment->shipping_id}",
                        'order_status'  => 18
                    ]);

                    $order->status_id=18;
                    $order->save();

                    $consignment->update(['consignment_status' => 3]);
                    TrackModel::create([
                        'shipping_type' => $partner,
                        'tracking_id'   => $request['tracking_no'],
                        'status_id'     => 4,
                        'status_value'  => 'Delivered',
                        'location'      => $request['location'],
                        'status_date'   => $request['delivery_date']
                    ]);

                    /* $defaultFinance = config('finance.default');
                    $financeClass = config("finance.{$defaultFinance}");
                    $financeObj = new $financeClass();

                    $financeObj->financeAutopostings($order, 'courier'); */
                    
                    $postReq = new Request();
                    $postReq->setMethod('POST');
                    $postReq->request->add([
                        'order_id'              => $order->order_id,
                        'finascopEventRefId'    => config("event_master.deliveryConfirmation"),
                        'storegroup_id'         => (@$order->storegroup_id ? $order->storegroup_id : 0)
                    ]);
                    (new PostingRepository)->finascopPosting($postReq);
                    
                    return new SuccessResponse('Order Delivered');
                }
                return new ErrorResponse("Order not found.");
            }
            $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
            $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
            $dynamoClient->putItem([
            'TableName' => config('aws.prefix').'tp_courier_delivered_log',
                'Item'      => [
                    'uuid'          => ['S' => (string)$uuid],
                    'tstamp'        => ['S' => (string)date('Y-m-d H:i:s')],
                    'trackingID'    => ['S' => (string)$request['tracking_no']],
                    'response'      => ['S' => json_encode($request)],
                    'orderID'       => ['S' => (string)$orderID],
                    'status'        => ['S' => $status]
                ]
            ]);
            return new ErrorResponse("Consignment not available in our system.");
        /*}
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage());
        }*/
    }
}