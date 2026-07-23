<?php
namespace App\ExpressPartners\Porter;

use Carbon\Carbon;
use App\Models\{
	Order,
	QugeoOrderCourier
};
use BackOffice\Models\{
    QugeoOrder,
    TransferOrder
};
use App\Helpers\EmailHelper;
use App\Models\CourierDelivery\{
    ShippingConsignment,
    ConsignmentTracking,
    CancelConsignment
};
use Illuminate\Support\Facades\DB;
use App\ExpressPartners\Porter\{
	PorterApiOperations,
	PorterRequests
};
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\{
    ShippingLogRepository,
    PartnerOrderUpdateRepository
};

class Porter
{
    function __construct()
    {
        $this->operations = new PorterApiOperations;
        $this->requests = new PorterRequests;
        $this->orderMethod = config("expresspartners.porter.orderMethod") ?? 1;
    }
    public function checkIfDeliveryAgentAvailable($orderDetails)
    {
        try
        {
            $phoneCode = config('app.phonecode') ?? "+91";
            $number = preg_replace('/[^A-Za-z0-9+]/', '', $orderDetails["to_phone"]);
            $phone = str_replace($phoneCode, '', $number);
            
            $request = [
                'from_details'  => [
                    'latitude'      => $orderDetails['from_latitude'],
                    'longitude'     => $orderDetails['from_longitude']
                ],
                'to_details'    => [
                    "name"          => $orderDetails["to_name"],
                    'latitude'      => $orderDetails['to_latitude'],
                    'longitude'     => $orderDetails['to_longitude'],
                    'phone'         => $orderDetails['to_phone']
                ]
            ];
            $deliveryData = $this->operations->checkDeliveryAvailable($request);
            return @$deliveryData['status'] ?? false;
        }
        catch (\Exception $e)
        {
            info("porter checkIfDeliveryAvailable Error");info($e);
            return false;
        }
    }
    public function createNewConsignment($package)
    {
        try
        {
            $getReqData = $this->requests->createConsignmentRequest($package->fsto_id);
            // return $getReqData;
            if(@$getReqData['status'] == "success")
            {
                $deliveryData = $this->operations->checkDeliveryAvailable($getReqData['data']);
                if($deliveryData['status'] == 'success')
                {
                    $createConsignment = $this->operations->createNewConsignment($getReqData['data']);
                    if($createConsignment['status'] == 'success')
                    {
                        $consignment = $createConsignment['data'];
                        $pickup = Carbon::createFromTimestamp($consignment->estimated_pickup_time);

                        $createShipping = ShippingConsignment::create([
                            'order_id'              => $consignment->request_id,
                            'order_method'          => 1,
                            'shipping_type'         => 'porter',
                            'shipping_id'           => $consignment->order_id,
                            'tracking_id'           => $consignment->order_id,
                            'shipment_label'        => "",
                            'tracking_link'         => $consignment->tracking_url,
                            'shipping_partner'      => NULL,
                            'shipping_charge'       => ($consignment->estimated_fare_details / 100),
                            'pickupdate'            => $pickup,
                            'consignment_status'    => 1,
                            'consignment_request'   => json_encode($createConsignment['request']),
                            'consignment_response'  => json_encode($consignment)
                        ]);
                        ConsignmentTracking::create([
                            'shipping_type' => 'porter',
                            'tracking_id'   => $consignment->order_id,
                            'status_id'     => 0,
                            'status_value'  => "",
                            'location'      => $consignment->tracking_url,
                            'status_date'   => now()
                        ]);
                        $updateOrder = Order::where('order_order_id', $consignment->request_id)->update([
                            'order_trackID'     => $consignment->order_id,
                            'order_trackURL'    => $consignment->tracking_url
                        ]);
                        $transferOrderUpdate = TransferOrder::where('fsto_id', $package->fsto_id)->update([
                            'fsto_hasShipmentCreated'   => 1
                        ]);
                        $sendEmail = (new EmailHelper)->sendEmail('ShipmentConfirmation', [
                            'Customersname'     => @$getReqData['data']['to_details']['name'],
                            'email'             => @$getReqData['data']['to_details']['email'],
                            'order_order_id'    => @$consignment->request_id
                        ]);
                    }
                }
            }
        }
        catch (\Exception $e)
        {
            info("PORTER ERROR");info($e);
            return [
                'status'    => 'error',
                'message'   => $e->getMessage()
            ]; 
        }
    }
    public function cancelConsignment($order_id, $reason = '')
    {
        try
        {
            $shippingData = ShippingConsignment::select('id', 'order_id', 'shipping_id', 'tracking_id')->where([
                ['order_id', $order_id],
                ['consignment_status', '<', 3]
            ])->get();
            $cancelled = 0;
            foreach ($shippingData as $ship)
            {
                $cancelConsignment = $this->operations->cancelConsignment($ship->shipping_id);
                if(@$cancelConsignment['status'] == 'success')
                {
                    ShippingConsignment::where([
                        'shipping_type' => 'porter',
                        'id'            => $ship->id
                    ])->update([
                        'consignment_status'    => 4
                    ]);
                    ConsignmentTracking::create([
                        'shipping_type' => 'porter',
                        'tracking_id'   => $ship->id,
                        'status_id'     => 4,
                        'status_value'  => "Cancelled",
                        'location'      => $ship->tracking_url,
                        'status_date'   => now()
                    ]);
                    CancelConsignment::create([
                        'order_id'      => $ship->order_id,
                        'shipping_id'   => $ship->shipping_id,
                        'cancel_reason' => $reason
                    ]);
                    $cancelled++;
                }
            }
            if($cancelled > 0)
            {
                (new PartnerOrderUpdateRepository)->orderCancel(['orderID' => $order_id]);
            }
        }
        catch (\Exception $e)
        {
            return [
                'status'    => 'error',
                'message'   => $e->getMessage()
            ]; 
        }
    }
    public function webhook($data)
    {
        try
        {
            $ship = ShippingConsignment::where([
                ['shipping_id', $data->order_id],
                ['order_method', 1],
                ['consignment_status', '<', 3]
            ])->first();

            (new ShippingLogRepository)->createLog([
                'order_id'      => $ship->order_id,
                'order_method'  => 1,
                'type'          => config('expresspartners.porter.local_id'),
                'APIName'       => "webhook",
                "APIURL"        => "",
                "APIHeaders"    => "",
                'request'       => json_encode($data->all()),
                'response'      => ""
            ]);
            $setTracking = $this->updateTrackingDetails($ship, $data);
            return $data->order_id;
        }
        catch (\Exception $e)
        {
            info("PORTER WEBHOOK ERROR");info($e);
        }
    }

    private function updateTrackingDetails($ship, $deliveryData)
    {
        $orderID = $ship->order_id;
        $trackingID = $ship->tracking_id;
        $getTrackingData = ConsignmentTracking::where([
            ['tracking_id', $trackingID],
            ['shipping_type', 'porter']
        ])->latest()->first();
        if($getTrackingData)
        {
            if($getTrackingData->status_value != $deliveryData->status)
            {
                $statDate = Carbon::createFromTimestamp($deliveryData->order_details['event_ts']);
                $location = @$deliveryData->order_details['partner_location'] ? json_encode(@$deliveryData->order_details['partner_location']) : $ship->tracking_link;
                ConsignmentTracking::create([
                    'shipping_type' => 'porter',
                    'tracking_id'   => $deliveryData->order_id,
                    'status_id'     => 0,
                    'status_value'  => $deliveryData->status,
                    'location'      => $location,
                    'status_date'   => $statDate->toDateTimeString()
                ]);
            }
            $consignmentStatus = 0;
            switch($deliveryData->status)
            {
                case 'order_accepted':// driver accepted
                    $consignmentStatus = 1;
                    break;
                case 'order_reopen':// reassign pickup
                    $consignmentStatus = 1;
                    break;
                case 'order_start_trip':// order picked up
                    $consignmentStatus = 2;
                    $this->orderPickupComplete($ship, $deliveryData);
                    break;
                case 'order_end_job':// order delivered
                    $consignmentStatus = 3;
                    $this->updateDeliveryStatus($ship, $deliveryData);
                    break;
                case 'order_cancel':// order cancel
                    $consignmentStatus = 4;
                    $this->cancelDelivery($ship, $deliveryData);
                    break;
            }
            ShippingConsignment::where([
                ['shipping_type', 'porter'],
                ['shipping_id', $deliveryData->order_id]
            ])->update([
                'consignment_status' => $consignmentStatus
            ]);
            return $getTrackingData;
        }
        return false;
    }
    private function orderPickupComplete($ship, $deliveryData)
    {
        $request = [
            "orderID"       => $ship->order_id,
            "deliveryID"    => $deliveryData->order_id,
            "partner"       => "porter"
        ];
        (new PartnerOrderUpdateRepository)->orderPickupComplete($request);
    }
    private function updateDeliveryStatus($ship, $deliveryData)
    {
        $statDate = Carbon::createFromTimestamp($deliveryData->order_details['event_ts']);
        $request = [
            "orderDate"     => $statDate->toDateTimeString(),
            "orderID"       => $ship->order_id,
            "deliveryID"    => $deliveryData->order_id,
            "partner"       => "porter"
        ];
        (new PartnerOrderUpdateRepository)->orderDelivered($request);
    }
    private function cancelDelivery($ship, $deliveryData)
    {
        $statDate = Carbon::createFromTimestamp($deliveryData->order_details['event_ts']);
        $order = Order::where('order_order_id', $ship->order_id)->first();
        if($order)
        {
            $orderHistory = OrderHistory::create([
                'order_id'      => $order->order_id,
                'order_action'  => "Order Cancelled via Porter {$deliveryData->order_id} on {$statDate->toDateTimeString()}",
                'order_status'  => $order->status_id
            ]);
            TransferOrder::where('fstr_id', $order->order_id)->update([
                'fsto_hasShipmentCreated'   => 3
            ]);
        }
    }
}