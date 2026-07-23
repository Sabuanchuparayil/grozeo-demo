<?php
namespace App\Http\Controllers\Shipments;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shipments\ShipmentDeliveredRequest;
use App\Http\Repositories\Shipments\ShippingConsignmentRepository;

class ShippingConsignment extends Controller
{

    public function __construct(ShippingConsignmentRepository $shipRepo)
    {
        $this->shipRepo = $shipRepo;
    }

    public function updateTrackingDetails(Request $request, $type = 0, $provider = "")
    {
        if($type == 1) // EXPRESS
        {
            $shipping = config("expresspartners.{$provider}.sClass");
            $shipper = new $shipping();
            $data = $shipper->webhook($request);
        }
        if($type == 3) // COURIER
        {}
        return response()->json(['status' => 'ok']);
    }
    public function shipmentDelivered(ShipmentDeliveredRequest $request, $provider)
    {
        return $this->shipRepo->shipmentDeliveredWebhook($request->validated(), $provider);
    }
}