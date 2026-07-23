<?php

namespace App\Http\Controllers\Driver;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\FailedStatusRequest;
use App\Http\Requests\Driver\PolledNotificationRequest;
use App\Http\Requests\Driver\SnapRoadRequest;
use Illuminate\Http\Request;
use App\Http\Repositories\Driver\CommonRepository;

class CommonController extends Controller
{
    protected $commonRepo;

    public function __construct(CommonRepository $commonRepo)
    {
        $this->commonRepo = $commonRepo;
    }
    
    /**
     * Fetch Failed Status List
     *
     */
    public function getFailedStatuses(FailedStatusRequest $request)
    {
        
        return $this->commonRepo->failedStatuses($request);
        
    }
     /**
     * Fetch polled notifications - ie, The FCM notifications that were sent to drivers via the pull pending API but were not received due to any issues will be available in this API.
     *
     */
    public function getNotifications(PolledNotificationRequest $request)
    {
        return $this->commonRepo->getNotifications($request);
    }
    /**
     * Get presigned url
     *
     */
    public function s3Details()
    {
        return $this->commonRepo->s3Details();
        
    }

    public function logout()
    {
        return $this->commonRepo->logout();
        
    }
    public function getSnapRoad(SnapRoadRequest $request)
    {
        return $this->commonRepo->getSnapRoad($request);
    }

    /**
     * Get single order details
     * @return array
    */
    public function getOrderDetails($orderID)
    {
        return $this->commonRepo->getOrderDetails($orderID);
    }

    /**
     * Get driver details
     * @return array
    */
    public function getDriverDetails()
    {
        return $this->commonRepo->getDriverDetails();
    }
}
