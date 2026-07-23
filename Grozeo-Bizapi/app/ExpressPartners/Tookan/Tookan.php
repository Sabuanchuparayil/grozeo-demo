<?php

namespace App\ExpressPartners\Tookan;

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
use App\ExpressPartners\Tookan\{
    TookanApiOperations,
    TookanConsignmentRequest
};
use App\Helpers\HttpCurlCalls;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\PostingRepository;
use App\Http\Repositories\ShippingLogRepository;


class Tookan
{
    protected $apiKey, $auth, $operations, $request;

    function __construct()
    {
        $this->apiKey = config("expresspartners.tookan.apiKey");
        $this->operations = new TookanApiOperations;
        $this->request = new TookanConsignmentRequest;
    }

    public function checkIfDeliveryAgentAvailable($orderDetails)
    {
        try
        {
            $agentListReq = [
                'order_id'  => $orderDetails['order_id'],
                "latitude"  => $orderDetails['from_latitude'],
                "longitude" => $orderDetails['from_longitude']
            ];
            $checkifAgentAvailable = $this->operations->checkifAgentAvailable($agentListReq);
            return $checkifAgentAvailable;
        }
        catch (\Exception $e)
        {
            // info("Tookan checkIfDeliveryAvailable Error");info($e);
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
            $getReqData = $this->request->getConsignmentData($package->fsto_id);
            if(@$getReqData['status'] == 'success')
            {
                $deliveryReq = [
                    "from_latitude"     => @$getReqData['data']['from_details']['latitude'],
                    "from_longitude"    => @$getReqData['data']['from_details']['longitude'],
                    "to_latitude"       => @$getReqData['data']['to_details']['latitude'],
                    "to_longitude"      => @$getReqData['data']['to_details']['longitude'],
                    'order_id'          => @$getReqData['data']['order_id']
                ];
                $ifDeliveryAvailable = $this->checkIfDeliveryAvailable($deliveryReq);
                if($ifDeliveryAvailable)
                {
                    $getReqData['data']['teamID'] = $ifDeliveryAvailable;
                    $createConsignment = $this->operations->createNewConsignment($getReqData['data']);
                    if($createConsignment['status'] == 'success')
                    {
                        $consignment = $createConsignment['data'];
                        $consignmentReq = $createConsignment['request'];
                        $shipCreate = [
                            'order_id'              => @$consignment->data->order_id,
                            'order_method'          => 1,
                            'shipping_type'         => 'tookan',
                            'shipping_id'           => @$consignment->data->job_id,
                            'tracking_id'           => @$consignment->data->job_id,
                            'shipment_label'        => "",
                            'shipping_partner'      => NULL,
                            'shipping_charge'       => @$consignment->tookanFare,
                            'pickupdate'            => @$consignmentReq['job_pickup_datetime'],
                            'consignment_status'    => 1,
                            'consignment_request'   => json_encode($consignmentReq),
                            'consignment_response'  => json_encode($consignment)
                        ];
                        $createCon = ShippingConsignment::create($shipCreate);
                        $track1 = ConsignmentTracking::create([
                            'shipping_type' => 'tookan',
                            'tracking_id'   => $consignment->data->job_id,
                            'status_id'     => 0,
                            'status_value'  => 'Pickup',
                            'location'      => $consignment->data->pickup_tracking_link,
                            'status_date'   => now()
                        ]);
                        $track2 = ConsignmentTracking::create([
                            'shipping_type' => 'tookan',
                            'tracking_id'   => $consignment->data->job_id,
                            'status_id'     => 0,
                            'status_value'  => 'Delivery',
                            'location'      => $consignment->data->delivery_tracing_link,
                            'status_date'   => now()
                        ]);
                        $updateOrder = Order::where('order_order_id', $consignment->data->order_id)->update([
							'order_trackURL'	=> $consignment->data->delivery_tracing_link
						]);
                        $transferOrderUpdate = TransferOrder::where('fsto_id', $package->fsto_id)->update([
                            'fsto_hasShipmentCreated'	=> 1
                        ]);
                        $sendEmail = (new EmailHelper)->sendEmail('ShipmentConfirmation', [
							'Customersname'		=> @$consignment->data->customer_name,
							'email'     		=> @$getReqData['data']['to_details']['email'],
							'order_order_id'	=> @$consignment->data->order_id
						]);
                        return $createConsignment;
                    }
                }
                return $ifDeliveryAvailable;
            }
            else
            {
                (new ShippingLogRepository)->createLog([
                    'order_id'      => $package->order->order_order_id,
                    'order_method'  => 1,
                    'type'          => config('expresspartners.tookan.local_id'),
                    'request'       => json_encode($package),
                    'response'      => json_encode($getReqData)
                ]);
            }
            return $getReqData;
        }
        catch (\Exception $e)
        {
            // info("Tookan createNewConsignment Exception");info($e);
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
        }
        catch (\Exception $e)
        {
            return [
                'status'    => 'error',
                'message'   => $e
            ]; 
        }
    }
    public function cancelConsignment($ship)
    {
        try
        {
        }
        catch (\Exception $e)
        {
            return [
                'status'    => 'error',
                'message'   => $e->getMessage()
            ]; 
        }
    }


    private function checkIfDeliveryAvailable($orderDetails)
    {
        try
        {
            $agentListReq = [
                'order_id'  => $orderDetails['order_id'],
                "latitude"  => $orderDetails['from_latitude'],
                "longitude" => $orderDetails['from_longitude']
            ];
            $checkifAgentAvailable = $this->operations->checkifAgentAvailable($agentListReq);
            if($checkifAgentAvailable)
            {
                return $this->checkIfPointsAvailable($orderDetails);
            }
            else
            {
                return $this->checkIfPointsAvailable($orderDetails);
            }
            return false;
        }
        catch (\Exception $e)
        {
            // info("Tookan checkIfDeliveryAvailable Error");info($e);
            return false;
        }
    }

    private function checkIfPointsAvailable($orderDetails)
    {
        $pointReq = [
            'order_id'  => $orderDetails['order_id'],
            "points"    => [
                [
                    "latitude"  => $orderDetails['from_latitude'],
                    "longitude" => $orderDetails['from_longitude']
                ]
            ]
        ];
        $checkifPointAvailable = $this->operations->getRegionFromPoints($pointReq);
        return $checkifPointAvailable;
    }
}