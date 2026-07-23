<?php

namespace App\Http\Repositories\Driver;

use App\Models\Drivers\QugeoOrder;
use App\Models\Drivers\QugeoDriverLog;
use App\Models\Drivers\FirebaseLog;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class LiveOrderRepository
{
    /**
     * Get driver's live order
     */
    public function liveOrder($request)
    {
        try {
            $orderIds = [];
            $deliveryLocs = [];
            $orderLive = [];
    
            $polledOrders = QugeoDriverLog::where('pollid', $request["msgid"])
                ->orderBy('id', 'asc')
                ->get();
    
              
            foreach ($polledOrders as $polledOrder) {

                $delLocations = QugeoOrder::with(['deliveryStatus' => function($query) {
                    $query->select('dls_ID', 'dls_DelStatus');
                }])
                ->whereIn('quor_Status', [22, 23, 25, 27, 9, 31, 32])
                ->where('quor_id', $polledOrder->quorId)
                ->select('quor_id', 'quor_RefNo', 'quor_PickupPincode', 'quor_PickupLat', 'quor_PickupLng', 'quor_PickupAddress', 'quor_PickupLocation', 'quor_DeliveryPincode', 'quor_DeliveryLat', 'quor_DeliveryLng', 'quor_DeliveryLocation', 'quor_DeliveryAddress', 'quor_Pickupbr_id', 'quor_Deliverybr_id', 'quor_QugeoPickupDDBOrderId', 'quor_QugeoDeliveryDDBOrderId', 'quor_Status', 'quor_PickupPhone')
                ->first($polledOrder->quorId);
              
                
                if (isset($delLocations) && $delLocations->quor_id > 0) {
                    $quor_QugeoPickupDDBOrderId = (!empty($delLocations->quor_QugeoDeliveryDDBOrderId) ? $delLocations->quor_QugeoDeliveryDDBOrderId : $delLocations->quor_QugeoPickupDDBOrderId);
                    $quor_DeliveryLocation = $delLocations->quor_DeliveryAddress . ' ' . $delLocations->quor_DeliveryPincode;
    

                    $orderIds[] = [
                        "id" => $quor_QugeoPickupDDBOrderId,
                        "order" => $delLocations->quor_id,
                        "orderNo" => $delLocations->quor_RefNo,
                        "location" => $quor_DeliveryLocation,
                        "latitude" => $delLocations->quor_DeliveryLat,
                        "longitude" => $delLocations->quor_DeliveryLng,
                        "orderStatus" => $delLocations->deliveryStatus->dls_DelStatus,
                        "statusId" => $delLocations->quor_Status,
                    ];
    
                    $deliveryLocs[] = [
                        "latitude" => $delLocations->quor_DeliveryLat,
                        "longitude" => $delLocations->quor_DeliveryLng,
                        "location" => $quor_DeliveryLocation,
                    ];
                    $geocoords = [
                        "pickup" => [
                            "latitude" => $delLocations->quor_PickupLat,
                            "longitude" => $delLocations->quor_PickupLng,
                            "location" => $delLocations->quor_PickupLocation,
                            "address" => $delLocations->quor_PickupAddress,
                            "mobile" => $delLocations->quor_PickupPhone,
                        ],
                        "delivery" => $deliveryLocs,
                    ];
        
                }
            }
    
            if (!empty($deliveryLocs)) {
           
              
                $orderLive['orders'] = $orderIds;
                $orderLive['details'] = $geocoords;
    
                if ($request['hasaccepted'] == 'true') {
                    FirebaseLog::where('rfir_StatusId', 1)
                        ->where('rfir_token', $request['fcm_token'])
                        ->update(['rfir_StatusId' => 2]);
                } else {
                    FirebaseLog::where('rfir_StatusId', 1)
                        ->where('rfir_token', $request['fcm_token'])
                        ->update(['rfir_StatusId' => 3]);
                }
            } else {
                return new SuccessResponse("No Orders");
            }
    
            return new SuccessWithData($orderLive);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
    }
}
