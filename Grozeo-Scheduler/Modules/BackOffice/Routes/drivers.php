<?php

use BackOffice\Http\Controllers\Drivers\DriverAuthController;
use BackOffice\Http\Controllers\Drivers\DriverStatusUpdateController;
use BackOffice\Http\Controllers\Drivers\DriverVehiclesController;
use BackOffice\Http\Controllers\Drivers\DriverController;
use BackOffice\Http\Controllers\Drivers\DriverOrderController;

// Route::group(['prefix'   => 'api/drivers'], function(){
// 	Route::post('login', [DriverAuthController::class, 'driverAuthentication']);
// 	Route::post('login/otp', [DriverAuthController::class, 'driverOtpVerification']);

// 	Route::group(['middleware' => 'drivers.auth'], function () {
// 		//update driver status (online/offline)
// 		Route::post('status/update', [DriverStatusUpdateController::class, 'driverUpdateOnlineStatus']);
// 		//driver list vehicle
// 		Route::get('vehicles', [DriverVehiclesController::class, 'listAllVehicles']);
// 		//driver list vehicle types
// 		Route::get('vehicles/types', [DriverVehiclesController::class, 'listAllVehicleTypes']);
// 		//driver create vehicle
// 		Route::post('vehicles/create', [DriverVehiclesController::class, 'createNewVehicle']);
// 		//driver details
// 		Route::get('driver/details', [DriverController::class, 'driverDetails']);
// 		// Get driver's all pending orders
// 		Route::get('pendingorders', [DriverOrderController::class, 'pendingOrderDetails']);

// 	});
// });