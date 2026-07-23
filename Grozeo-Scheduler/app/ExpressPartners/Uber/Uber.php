<?php

namespace App\ExpressPartners\Uber;

use App\Models\{
    Order,
    QugeoOrder,
    OrderHistory,
    TransferOrder,
    CourierDelivery\CancelConsignment,
    CourierDelivery\ShippingConsignment,
    CourierDelivery\ConsignmentTracking
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
use App\Helpers\EmailHelper;
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

    public function createNewConsignment($package)
    {
        try
        {
            $outs = false;
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
                                'tracking_link'         => $consignment->tracking_url,
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
                            
                            $updateOrder = Order::where('order_order_id', $consignment->external_id)->update([
                                'order_trackID'     => $consignment->id,
                                'order_trackURL'    => $consignment->tracking_url
                            ]);
                            $transferOrderUpdate = TransferOrder::where('fsto_id', $package->fsto_id)->update([
                                'fsto_hasShipmentCreated'   => 1
                            ]);
                            $sendEmail = (new EmailHelper)->sendEmail('ShipmentConfirmation', [
                                'Customersname'     => @$getReqData['data']['to_details']['name'],
                                'email'             => @$getReqData['data']['to_details']['email'],
                                'order_order_id'    => @$consignment->external_id
                            ]);
                            $outs = true;
                        }
                    }
                }
            }
            return $outs;
        }
        catch (\Exception $e)
        {
            info("UBER ERROR");info($e);
            return false;
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
    public function checkDeliveryStatus($ship, $type = 1)
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
                    $setTracking = $this->updateTrackingDetails($ship, $getDeliveryData, $type);
                    $outs['data'] = $setTracking;
                }
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
    private function updateTrackingDetails($ship, $deliveryData, $type)
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
            $returns = "";
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
                    $returns = "pickup";
                    $this->orderPickupComplete($ship, $deliveryData);
                    break;
                case 'dropoff':
                    $consignmentStatus = 2;
                    break;
                case 'delivered':
                    if($type == 1)
                    {
                        $consignmentStatus = 3;
                        $returns = "delivered";
                        $this->updateDeliveryStatus($ship, $deliveryData);
                    }
                    else
                    {
                        $consignmentStatus = 2;
                        $returns = "pickup";
                        $this->orderPickupComplete($ship, $deliveryData);
                    }
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
            return $returns;
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
        $request = [
            "orderDate"     => $statDate,
            "orderID"       => $ship->order_id,
            "deliveryID"    => $deliveryData->id,
            "partner"       => "uber"
        ];
        (new PartnerOrderUpdateRepository)->orderCancel($request);
    }
    public function cancelConsignment($order_id, $reason = '')
    {
        try
        {
            $shippingData = ShippingConsignment::select('id', 'order_id', 'shipping_id', 'tracking_id')->where([
                ['order_id', $order_id],
                ['consignment_status', '<', 3]
            ])->get();
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
                $cancelled = 0;
                foreach ($shippingData as $ship)
                {
                    $cancelConsignment = $this->operations->cancelConsignment($token->access_token, $ship);
                    $outs['data'] = $cancelConsignment;
                    if(@$cancelConsignment->id != "")
                    {
                        ShippingConsignment::where([
                            'shipping_type' => 'uber',
                            'id'            => $ship->id
                        ])->update([
                            'consignment_status'    => 4
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
}