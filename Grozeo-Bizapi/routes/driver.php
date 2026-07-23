<?php
use App\Http\Controllers\Driver\Authentication\DriverDetailsController;
use App\Http\Controllers\Driver\Authentication\DriverAuthController;
use App\Http\Controllers\Driver\Authentication\OtpController;
use App\Http\Controllers\Driver\Vehicles\VehicleController;
use App\Http\Controllers\Driver\Geolocation\GeoLocationController;
use App\Http\Controllers\Driver\Order\PendingOrderController;
use App\Http\Controllers\Driver\Order\OrderListController;
use App\Http\Controllers\Driver\Order\PullPendingOrderController;
use App\Http\Controllers\Driver\Order\DeliveredOrderController;
use App\Http\Controllers\Driver\Order\LiveOrderController;
use App\Http\Controllers\Driver\Order\PolledOrderController;
use App\Http\Controllers\Driver\Order\ConcludeOrderController;
use App\Http\Controllers\Driver\Order\ProceedPolledOrderController;
use App\Http\Controllers\Driver\Order\MileStoneController;
use App\Http\Controllers\Driver\Order\PreConcludeOrderController;
use App\Http\Controllers\Driver\Order\PartnerOrderController;
use App\Http\Controllers\Driver\CommonController;
use App\Http\Controllers\Driver\DashboardController;
use App\Http\Controllers\Driver\Order\DeliveryController;
use App\Http\Controllers\Driver\AutoPollTestController;

// ***** Fetching Driver Details *****
Route::post('userdetails', [DriverDetailsController::class, 'driverDetails']);

// ***** OTP send and verification *****
Route::post('send-otp', [OtpController::class, 'sendOTP']); // old api. to be removed.
Route::post('verify-otp', [OtpController::class, 'verifyOTP']); // old api. to be removed.

// send otp
Route::post('otp/send', [OtpController::class, 'otpSend']);
// verify otp
Route::post('otp/verify', [OtpController::class, 'otpVerify']);

// ***** Authentication *****
Route::post('auth', [DriverAuthController::class, 'driverAuthentication']);

Route::group(['middleware' => 'newdriver.auth'], function () {

    // Driver Details API
    Route::get('details', [CommonController::class, 'getDriverDetails']);

    // Dashboard API
    Route::get('dashboard', [DashboardController::class, 'dashboardDetails']);

    // OrderDetails API
    Route::get('order/details/{orderID}', [CommonController::class, 'getOrderDetails']);


    /**
     * Updated Vehicle APIs
     *      Vehicle List
     *      Add Vehicle
     *      Select Vehicle
    */
    Route::group(['prefix' => 'vehicles'], function () {
        // list vehicles
        Route::get('/list', [VehicleController::class, 'vehicleList']);
        // add vehicle
        Route::post('/add', [VehicleController::class, 'addVehicle']);
        // select vehicle
        Route::get('/{id}/select', [VehicleController::class, 'chooseVehicle']);
    });

    // ***** Vehicle APIs *****
    // Fetch list of vehicle types
    Route::get('get-vehicle-types', [VehicleController::class, 'getVehicleTypes']);
    // Fetch last used three vehicles
    Route::get('last-vehicles', [VehicleController::class, 'getLastVehicles']);// old api. New api is above. To be removed.
    // Select or add a vehicle
    Route::post('select-vehicle', [VehicleController::class, 'selectVehicle']);// old api. New api is above. To be removed.


    // ***** Geolocation  *****
    // Update driver's location
    Route::post('geolocation', [GeoLocationController::class, 'updateLocation']);


    // ***** Order related APIs *****
    // Order listing (type => pending/in-progress)
    Route::get('orders/{type}', [OrderListController::class, 'orders']);
    // Fetch all driver's pending orders
    Route::get('pending-orders', [PendingOrderController::class, 'pendingOrders']);
    // Pull Pending order
    Route::post('pull-pending-order', [PullPendingOrderController::class, 'pullPendingOrder']);
    // Delivered Order
    Route::get('delivered-orders', [DeliveredOrderController::class, 'deliveredOrders']);
    // Live Order
    Route::post('live-orders', [LiveOrderController::class, 'liveOrder']);
    // Polled Order
    Route::post('polled-order', [PolledOrderController::class, 'pollOrder']);
    // Polled Order Deny
    Route::get('polled-order/deny/{orderID}', [PolledOrderController::class, 'denyPolledOrder']);
    // Conclude Order
    Route::post('conclude-order', [ConcludeOrderController::class, 'concludeOrder']);
    // Milestone
    Route::post('milestone', [MileStoneController::class, 'milestone']);
    // Proceed Polled Order
    Route::post('proceed-polled-order', [ProceedPolledOrderController::class, 'proceedOrder']);
    // Preconclude order
    Route::post('pre-conclude-order', [PreConcludeOrderController::class, 'preConcludeOrder']);

    // Delivery API {status => start, failed, complete}
    Route::post('deliverable/{status}', [DeliveryController::class, 'updateDelivery']);
    Route::post('location/update', [DeliveryController::class, 'updateDeliveryLocation']);


    // ***** Common APIs *****
    // Fetch failed status list
    Route::post('failed-statuses', [CommonController::class, 'getFailedStatuses']);
    // Fetch order notifications
    Route::post('polled-notifications', [CommonController::class, 'getNotifications']);
    // Fetch s3 presigned url
    Route::get('s3-details', [CommonController::class, 's3Details']);
    // Logout
    Route::post('logout', [CommonController::class, 'logout']);

});


// ***** From Partner Site *****
// Assign Driver to order
Route::post('scheduleABookingJobs', [PartnerOrderController::class, 'partnerOrder']);
// List Live Vehicles
Route::post('listLiveVehicles', [PartnerOrderController::class, 'listLiveVehicles']);
// Load Vehicle Details
Route::post('loadVehicleDetails', [PartnerOrderController::class, 'loadVehicleDetails']);


// ***** From  BackOffice ******
Route::post('snap-to-road', [CommonController::class, 'getSnapRoad']);

// ***** AutoScheduler Testing
Route::post('auto-poll-test', [AutoPollTestController::class, 'autoPoll']);

