<?php

namespace App\ExpressPartners\Uber;

use App\Models\{
    Order,
    OrderHistory,
    CourierDelivery\CancelConsignment,
    CourierDelivery\ShippingConsignment,
    CourierDelivery\ConsignmentTracking
};
use BackOffice\Models\{
    QugeoOrder,
    TransferOrder
};
use DateTime, DateTimeZone;
use App\ExpressPartners\Uber\{
    UberAuthorization,
    UberApiOperations,
    UberConsignmentRequest
};
use App\Http\Repositories\{
    PostingRepository,
    ShippingLogRepository,
    PartnerOrderUpdateRepository
};
use App\Helpers\HttpCurlCalls;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;


class Uber
{
    protected $auth, $request;

    function __construct()
    {
        $this->auth = new UberAuthorization;
        $this->operations = new UberApiOperations;
        $this->request = new UberConsignmentRequest;
    }

    public function checkIfDeliveryAgentAvailable($orderDetails)
    {
        try
        {
            $storeName = preg_replace('/[^A-Za-z0-9\s]/', '', @$orderDetails['branch_name']);
            $request = [
                'from_details'  => [
                    'address'       => $orderDetails['from_address'],
                    'extStoreID'    => @$orderDetails['branch_id']."_".str_replace(" ", "_", @$storeName),
                    'latitude'      => $orderDetails['from_latitude'],
                    'longitude'     => $orderDetails['from_longitude'],
                    'phone'         => $orderDetails['from_phone']
                ],
                'to_details'    => [
                    'address'       => $orderDetails['to_address'],
                    'latitude'      => $orderDetails['to_latitude'],
                    'longitude'     => $orderDetails['to_longitude'],
                    'phone'         => $orderDetails['to_phone']
                ],
                'total'         => $orderDetails['total']
            ];

            $token = $this->auth->generateUberToken();
            $outs['message'] = "Uber Token not available";
            if(@$token->access_token)
            {
                $deliveryData = $this->operations->checkDeliveryAvailable($token->access_token, $request);
                return @$deliveryData['status'];
            }
            return false;
        }
        catch (\Exception $e)
        {
            // info("Uber checkIfDeliveryAvailable Error");info($e);
            return false;
        }
    }

    public function createNewConsignment($package)
    {
        try
        {
            $outs = [
                'status'    => 'error',
                'data'      => [],
                'message'   => ""
            ];
            $token = $this->auth->generateUberToken();
            $outs['message'] = "Uber Token not available";
            if(@$token->access_token)
            {
                $getReqData = $this->request->getConsignmentData($package->fsto_id);
                $outs['message'] = @$getReqData['message'];
                $outs['data'] = @$getReqData['data'];
                if(@$getReqData['status'] == "success")
                {
                    $deliveryData = $this->operations->checkDeliveryAvailable($token->access_token, $getReqData['data']);
                    $outs['message'] = @$deliveryData;
                    $outs['data'] = @$deliveryData['data'];
                    if($deliveryData['status'] == 'success')
                    {
                        $deliveryDetails = $deliveryData['data'];
                        $createConsignment = $this->operations->createNewConsignment($token->access_token, $getReqData['data'], $deliveryDetails->id);
                        $outs['message'] = @$createConsignment['message'];
                        $outs['data'] = @$createConsignment['data'];
                        if($createConsignment['status'] ==  'success')
                        {
                            $consignment = $createConsignment['data'];

                            $utc = $consignment->pickup_eta;
                            $date = new DateTime($utc, new DateTimeZone('UTC'));
                            $pickupdate = $date->format('Y-m-d H:i:s');
                            ShippingConsignment::create([
                                'order_id'              => $consignment->external_id,
                                'order_method'          => 1,
                                'shipping_type'         => 'uber',
                                'shipping_id'           => $consignment->id,
                                'tracking_id'           => $consignment->id,
                                'shipment_label'        => json_encode($consignment->manifest),
                                'shipping_partner'      => NULL,
                                'shipping_charge'       => ($consignment->fee / 100),
                                'pickupdate'            => $pickupdate,
                                'consignment_status'    => 1,
                                'consignment_request'   => json_encode($createConsignment['request']),
                                'consignment_response'  => json_encode($consignment)
                            ]);

                            $utc = $consignment->created;
                            $date = new DateTime($utc, new DateTimeZone('UTC'));
                            $created = $date->format('Y-m-d H:i:s');
                            ConsignmentTracking::create([
                                'shipping_type' => 'uber',
                                'tracking_id'   => $consignment->id,
                                'status_id'     => 0,
                                'status_value'  => $consignment->status,
                                'location'      => $consignment->tracking_url,
                                'status_date'   => $created
                            ]);
                            ConsignmentTracking::create([
                                'shipping_type' => 'uber',
                                'tracking_id'   => $consignment->id,
                                'status_id'     => 1,
                                'status_value'  => $consignment->status,
                                'location'      => $consignment->pickup->address,
                                'status_date'   => $created
                            ]);
                            return $createConsignment;
                        }
                    }
                }
            }
            else
            {
                ShippingConsignmentLog::create([
                    'order_id'      => $package->order->order_order_id,
                    'order_method'  => 1,
                    'type'          => config('expresspartners.uber.local_id'),
                    'response'      => json_encode($token)
                ]);
            }
            return $outs;
        }
        catch (\Exception $e)
        {
            // info("UBER ERROR");info($e);
            return [
                'status'    => 'error',
                'message'   => $e->getMessage()
            ]; 
        }
    }
    public function getAllConsignments()
    {
        try
        {
            $outs = [
                'status'    => 'error',
                'message'   => ""
            ];
            $token = $this->auth->generateUberToken();
            $outs['message'] = "Uber Token not available";
            if(@$token->access_token)
            {
                $allConsignments = $this->operations->getAllConsignments($token->access_token);
                return $allConsignments;
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
    public function checkDeliveryStatus($ship)
    {
        try
        {
            $outs = [
                'status'    => 'error',
                'data'      => [],
                'message'   => ""
            ];
            $token = $this->auth->generateUberToken();
            $outs['message'] = "Uber Token not available";
            $outs['data'] = $token;
            if(@$token->access_token)
            {
                $getDeliveryDetails = $this->operations->getDeliveryDetails($token->access_token, $ship);
                $outs['data'] = $getDeliveryDetails;
                $outs['message'] = "Delivery details not found";
                if($getDeliveryDetails['status'] == 'success')
                {
                    $getDeliveryData = $getDeliveryDetails['data'];
                    return $getDeliveryData;
                    $setTracking = $this->updateTrackingDetails($ship, $getDeliveryData);
                    $outs['data'] = $setTracking;
                }
                ShippingConsignmentLog::create([
                    'order_id'      => $ship->order_id,
                    'order_method'  => 1,
                    'type'          => config('expresspartners.uber.local_id'),
                    'response'      => json_encode($getDeliveryDetails)
                ]);
            }else
            {
                ShippingConsignmentLog::create([
                    'order_id'      => $package->order->order_order_id,
                    'order_method'  => 1,
                    'type'          => config('expresspartners.uber.local_id'),
                    'response'      => json_encode($token)
                ]);
            }
            return $outs;
        }
        catch (\Exception $e)
        {
            return [
                'status'    => 'error',
                'message'   => $e
            ]; 
        }
    }
    private function updateTrackingDetails($ship, $deliveryData)
    {
        $orderID = $ship->order_id;
        $trackingID = $ship->tracking_id;
        $getTrackingData = ConsignmentTracking::where([
            ['tracking_id', $trackingID],
            ['shipping_type', 'uber']
        ])->latest()->first();
        if($getTrackingData)
        {
            if($getTrackingData->status_value != $deliveryData->status)
            {
                $utc = $deliveryData->updated;
                $date = new DateTime($utc, new DateTimeZone('UTC'));
                $statDate = $date->format('Y-m-d H:i:s');
                ConsignmentTracking::create([
                    'shipping_type' => 'uber',
                    'tracking_id'   => $deliveryData->id,
                    'status_id'     => 0,
                    'status_value'  => $deliveryData->status,
                    'location'      => $deliveryData->tracking_url,
                    'status_date'   => $statDate
                ]);
            }
            $consignmentStatus = 0;
            switch($deliveryData->status)
            {
                case 'pending':
                    $consignmentStatus = 1;
                    break;
                case 'pickup':
                    $consignmentStatus = 1;
                    $this->orderPickup($ship, $deliveryData);
                    break;
                case 'pickup_complete':
                    $consignmentStatus = 2;
                    $this->orderPickupComplete($ship, $deliveryData);
                    break;
                case 'dropoff':
                    $consignmentStatus = 2;
                    break;
                case 'delivered':
                    $consignmentStatus = 3;
                    $this->updateDeliveryStatus($ship, $deliveryData);
                    break;
                case 'canceled':
                    $consignmentStatus = 4;
                    $this->cancelDelivery($ship, $deliveryData);
                    break;
                case 'returned':
                    $consignmentStatus = 6;
                    break;
            }
            ShippingConsignment::where([
                ['shipping_type', 'uber'],
                ['shipping_id', $deliveryData->id]
            ])->update([
                'consignment_status' => $consignmentStatus
            ]);
            return $getTrackingData;
        }
        return false;
    }
    private function orderPickup($ship, $deliveryData)
    {
        $request = [
            "orderID"       => $ship->order_id,
            "deliveryID"    => $deliveryData->id,
            "partner"       => "uber"
        ];
        (new PartnerOrderUpdateRepository)->orderPickup($request);
    }
    private function orderPickupComplete($ship, $deliveryData)
    {
        $request = [
            "orderID"       => $ship->order_id,
            "deliveryID"    => $deliveryData->id,
            "partner"       => "uber"
        ];
        (new PartnerOrderUpdateRepository)->orderPickupComplete($request);
    }
    private function updateDeliveryStatus($ship, $deliveryData)
    {
        $utc = $deliveryData->updated;
        $date = new DateTime($utc, new DateTimeZone('UTC'));
        $statDate = $date->format('Y-m-d H:i:s');

        $request = [
            "orderDate"     => $statDate,
            "orderID"       => $ship->order_id,
            "deliveryID"    => $deliveryData->id,
            "partner"       => "uber"
        ];
        (new PartnerOrderUpdateRepository)->orderDelivered($request);
    }
    private function cancelDelivery($ship, $deliveryData)
    {
        $utc = $deliveryData->updated;
        $date = new DateTime($utc, new DateTimeZone('UTC'));
        $statDate = $date->format('Y-m-d H:i:s');
        $orderID = $ship->order_id;
        $order = Order::where('order_order_id', $ship->order_id)->first();
        if($order)
        {
            $orderHistory = OrderHistory::create([
                'order_id'      => $order->order_id,
                'order_action'  => "Order Cancelled via Uber {$deliveryData->id} on {$statDate}",
                'order_status'  => $order->status_id
            ]);
            TransferOrder::where('fstr_id', $order->order_id)->update([
                'fsto_hasShipmentCreated'   => 3
            ]);
        }
    }
    public function cancelConsignment($ship)
    {
        try
        {
            $outs = [
                'status'    => 'error',
                'data'      => [],
                'message'   => ""
            ];
            $token = $this->auth->generateUberToken();
            $outs['message'] = "Uber Token not available";
            $outs['data'] = $token;
            if(@$token->access_token)
            {
                $cancelConsignment = $this->operations->cancelConsignment($token->access_token, $ship);
                $outs['data'] = $cancelConsignment;
            }
            return $outs;
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
                ['shipping_id', $data->delivery_id],
                ['order_method', 1],
                ['consignment_status', '<', 3]
            ])->first();
            $reqData = (object) $data->data;

            (new ShippingLogRepository)->createLog([
                'order_id'      => $reqData->external_id,
                'order_method'  => 1,
                'type'          => config('expresspartners.uber.local_id'),
                'APIName'       => "webhook",
                "APIURL"        => "",
                "APIHeaders"    => "",
                'request'       => json_encode($data->all()),
                'response'      => ""
            ]);
            $setTracking = $this->updateTrackingDetails($ship, $reqData);
            return $data->delivery_id;
        }
        catch (\Exception $e)
        {
            // info("WEBHOOK ERROR");info($e);
        }
    }
}