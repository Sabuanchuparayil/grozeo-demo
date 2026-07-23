<?php

namespace App\Http\Repositories\Driver;

use App\Models\Drivers\QugeoOrder;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;

class DeliveredOrderRepository
{
    /**
     * Get driver's delivered orders
     */
    public function deliveredOrders($request)
    {
        try {
            $userId = auth_user();

            $deliveredOrders = QugeoOrder::query()
                ->select([
                    'quor_DeliveryName as name',
                    'quor_DeliveryPhone as phone',
                    'quor_DeliveredTime as Date',
                    'quor_RefNo as OrderNo',
                    'quor_PickupLocation',
                    'quor_DeliveryLocation'
                ])
                ->with(['finascopBranch', 'finascopDeliveryBranch'])
                ->where('quor_Type', 1)
                ->where('quor_DeliveryDriverId', $userId->d_ID)
                ->whereIn('quor_Status', [15, 38])
                ->orderByDesc('quor_DeliveredTime')
                ->skip($request->start ?? 0)
                ->limit($request->limit ?? 10)
                ->get();

            if ($deliveredOrders->isEmpty()) {
                return new SuccessResponse("No Delivered Orders");
            } else {
                return new SuccessWithData($deliveredOrders);
            }
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
    }
}
