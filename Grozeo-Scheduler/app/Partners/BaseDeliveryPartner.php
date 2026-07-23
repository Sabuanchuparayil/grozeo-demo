<?php
namespace App\Partners;

use Exception;
use App\Models\{
    Order,
    QugeoOrder,
    TransferOrder,
    QugeoOrderCourier
};
use App\Helpers\EmailHelper;
use App\Models\CourierDelivery\{
    ShippingConsignment,
    ConsignmentTracking,
    CancelConsignment
};
use App\Http\Repositories\PartnerOrderUpdateRepository;
use App\Events\DelayedOrderActions as DelayedOrderEvent;

abstract class BaseDeliveryPartner
{
    protected function successResponse($request, $type = 0)
    {
        if ($request['status'] == 'success')
        {
            switch ($type)
            {
                case 1: // check partner
                    // code...
                    break;
                case 2: // create consignment
                    return $this->orderSuccessUpdate($request['data']);
                    break;
                case 3: // cancel consignment
                    return $this->orderCancelUpdate($request['data']);
                    break;
                case 4: // consignment tracking
                    return $this->orderTrackingUpdate($request['data']);
                    break;
                case 5: // webhook
                    // code...
                    break;
                
                default:
                    // code...
                    break;
            }
        }
        return false;
    }

    protected function errorResponse($request)
    {
        return $request;
        if ($request['status'] == 'failed')
        {
            return $this->orderFailureUpdate($request);
        }
        return false;
    }

    private function orderSuccessUpdate($request)
    {
        try
        {
            $orderOrderID = $request[0]['order_id'];
            $order = Order::where('order_order_id', $orderOrderID)->first();
            $custName = $custEmail = "";
            $qugeoOrder = QugeoOrder::select('quor_id', 'quor_TransferOrder_id')->where([
                ['quor_RefNo', $orderOrderID],
                ['quor_TransferOrder_Type', 1]
            ])->first();
            foreach ($request as $req)
            {
                ShippingConsignment::create([
                    "order_id"              => $req["order_id"],
                    "order_method"          => $req["order_method"],
                    "shipping_type"         => $req["shipping_type"],
                    "shipping_partner"      => $req["shipping_partner"],
                    "shipping_charge"       => $req["shipping_charge"],
                    "consignment_status"    => $req["status"],
                    "consignment_request"   => json_encode($req["request"]),
                    "consignment_response"  => json_encode($req["response"]),
                    "shipping_id"           => $req["shipping_id"],
                    "tracking_id"           => $req["tracking_id"],
                    "shipment_label"        => $req["shipping_label"],
                    "tracking_link"         => $req["tracking_link"],
                    "pickupdate"            => $req["pickup_time"],
                ]);
                $trackUpdate = QugeoOrderCourier::create([
                    'qoc_courier'       => 1,
                    'qoc_qcn'           => $req['tracking_id'],
                    'qoc_date'          => $req['pickup_time'],
                    'quor_id'           => @$qugeoOrder->quor_id,
                    'qoc_trackingUrl'   => $req['tracking_link']
                ]);
                $custName = $req["customerName"];
                $custEmail = $req["customerEmail"];

            }
            $updateOrder = Order::where('order_order_id', $orderOrderID)->update([
                'order_trackID'     => $request[0]['tracking_id'],
                'order_trackURL'    => $request[0]['tracking_link']
            ]);
            $sendEmail = (new EmailHelper)->sendEmail('ShipmentConfirmation', [
                'Customersname'     => @$custName,
                'email'             => @$custEmail,
                'order_order_id'    => @$orderOrderID
            ]);
            $transferOrderUpdate = TransferOrder::where('fsto_id', $qugeoOrder->quor_TransferOrder_id)->update([
                'fsto_hasShipmentCreated'   => 1
            ]);
            return true;
        }
        catch (Exception $e)
        {
            info("Shipment update failed for Order ID: {$orderOrderID} — {$e}");
            return false;
        }
    }
    private function orderFailureUpdate($request)
    {
        return false;
    }
    private function orderCancelUpdate($request)
    {
        try
        {
            $shipping = ShippingConsignment::where('order_id', $request['orderID'])->get();
            $cancellationData = [];
            foreach($shipping as $ship)
            {
                $cancellationData[] = [
                    "order_id"      => $ship->order_id,
                    "order_method"  => $ship->order_method,
                    "shipping_id"   => $ship->shipping_id,
                    "cancel_reason" => "Cancelled by {$ship->shipping_type}"
                ];
            }
            $cancels = CancelConsignment::insert($cancellationData);
            $shipUpdate = ShippingConsignment::where('order_id', $request['orderID'])->update([
                'consignment_status'    => 4
            ]);
            (new PartnerOrderUpdateRepository)->orderCancel(['orderID' => $request['orderID']]);
        }
        catch (Exception $e)
        {
            info("Order cancellation update failed for Order ID: {$request['orderID']} — {$e}");
            return false;
        }
    }
    private function orderTrackingUpdate($request)
    {
        foreach ($request as $req)
        {
            $status = $req['consignment_status'];
            $shipmentData = ShippingConsignment::where([
                ['order_id', $req['orderID']],
                ['shipping_id', $req['trackingID']]
            ])->first();
            if($shipmentData->consignment_status > $status) { continue; }
            switch ($status)
            {
                case 1:
                    // code...
                    break;
                case 2:
                    $this->orderPickupComplete($req, $shipmentData->shipping_type);
                    break;
                case 3:
                    $this->updateDeliveryStatus($req, $shipmentData->shipping_type);
                    break;
                case 4:
                    $this->orderCancelUpdate($req);
                    break;
                
                default:
                    // code...
                    break;
            }
            ConsignmentTracking::create([
                'shipping_type' => $shipmentData->shipping_type,
                'tracking_id'   => $req["trackingID"],
                'status_id'     => $status,
                'status_value'  => $req["status_value"],
                'location'      => ($req["location"] != "") ? $req["location"] : $shipmentData->tracking_link,
                'status_date'   => $req["status_time"]
            ]);
            ShippingConsignment::where('id', $shipmentData->id)->update([
                'consignment_status' => $status
            ]);
        }
    }

    protected function getShipmentDetails($orderID, $type = "")
    {
        return ShippingConsignment::where([
            ['order_id', $orderID],
            ['shipping_type', $type]
        ])->get();
    }

    private function orderPickupComplete($details, $type)
    {
        $request = [
            "orderID"       => $details["orderID"],
            "deliveryID"    => $details["trackingID"],
            "partner"       => $type
        ];
        (new PartnerOrderUpdateRepository)->orderPickupComplete($request);
    }
    private function updateDeliveryStatus($details, $type)
    {
        $request = [
            "orderDate"     => $details["status_time"],
            "orderID"       => $details["orderID"],
            "deliveryID"    => $details["trackingID"],
            "partner"       => $type
        ];
        (new PartnerOrderUpdateRepository)->orderDelivered($request);
    }

    abstract public function checkDeliveryPartner($params);
    abstract public function bookShipment($params);
    abstract public function cancelShipment($params);
    abstract public function trackShipment($params);
    abstract public function webhook($request);
}
