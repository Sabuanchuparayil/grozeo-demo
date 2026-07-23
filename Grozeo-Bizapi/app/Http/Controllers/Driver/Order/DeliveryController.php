<?php
namespace App\Http\Controllers\Driver\Order;

use Illuminate\Http\Request;
use App\Http\Requests\Driver\{
    DeliveryStartRequest,
    DeliveryFailedRequest,
    DeliveryCompleteRequest,
    DeliveryLocationUpdateRequest
};
use App\Http\Controllers\Controller;
use App\Http\Responses\ErrorResponse;
use App\Http\Repositories\Driver\DeliveryRepository;


class DeliveryController extends Controller
{
    protected $deliRepo;

    public function __construct(DeliveryRepository $deliRepo)
    {
        $this->deliRepo = $deliRepo;
    }
    
    /**
     * Delivery Status Updates
     * recieves mainly order_id, location and status
     * returns order_status array
     */
    public function updateDelivery(Request $request, $status)
    {
        switch ($status)
        {
            case 'start':
                return $this->startDelivery(app(DeliveryStartRequest::class));
            case 'failed':
                return $this->failedDelivery(app(DeliveryFailedRequest::class));
            case 'complete':
                return $this->completeDelivery(app(DeliveryCompleteRequest::class));
            default:
                return new ErrorResponse("Operation failed");
        }
    }

    
    /**
     * Update Delivery location API
     * recieves order_id, location[lat, long]
     * returns Success/Failure message
     */
    public function updateDeliveryLocation(DeliveryLocationUpdateRequest $request)
    {
        return $this->deliRepo->updateDeliveryLocation($request);
    }
    
    private function startDelivery(DeliveryStartRequest $request)
    {
        return $this->deliRepo->startDelivery($request);
    }
    private function failedDelivery(DeliveryFailedRequest $request)
    {
        return $this->deliRepo->failedDelivery($request);
    }
    private function completeDelivery(DeliveryCompleteRequest $request)
    {
        return $this->deliRepo->completeDelivery($request);
    }
}
