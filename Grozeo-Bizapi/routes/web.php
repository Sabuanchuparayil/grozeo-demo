<?php

use App\Http\Controllers\PaymentResultController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
/* Log::debug(Request::getRequestUri());
Log::debug(Request::all()); */
Route::get('/', function () {
    return view('homepage');
});
Route::match(array('GET', 'POST'), "payment/result/redirect/{paymentgateway}", array(
    'uses' => 'PaymentResultController@store',
    'as' => 'payment.result'
));
//Route::get('payment/result/redirect/{paymentgateway}', [PaymentResultController::class, 'store'])
//    ->name('payment.result');
Route::get('payment/result/webredirect', [PaymentResultController::class, 'webstore'])
    ->name('payment.webresult');

Route::post('payment/result/webhook/{paymentgateway}', [PaymentResultController::class, 'store'])
    ->name('payment.webhook');

/*Route::get('payment/result/redirect', [PaymentResultController::class, 'store'])
    ->name('payment.result');

Route::post('payment/result/webhook', [PaymentResultController::class, 'store'])
    ->name('payment.webhook');*/

Route::get('payment/result/processing', [PaymentResultController::class, 'paymentProcessingRedirect']);

Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');


Route::get('/payment/response/error', function () {
    return view('payments.payment-gateway-error');
});