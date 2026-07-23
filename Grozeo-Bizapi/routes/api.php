<?php

use App\Models\Branch;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Request;
use App\Location\RetailerLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Nearestlocation;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\PincodeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\RetailerController;
use App\Http\Controllers\MedicinesController;
use App\Http\Controllers\SavedItemController;
use App\Http\Controllers\DeleteCartController;
use App\Http\Controllers\HomeScreenController;
use App\Http\Controllers\BusinessTypeController;

use App\Http\Controllers\BrandScreenController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\InnerSubCategoryScreen;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\VerificationController;
use BackOffice\Actions\Inventory\QugeoProcessor;
use App\Http\Controllers\FilterProductController;
use App\Http\Controllers\OrderCompleteController;
use App\Http\Controllers\OrderInvoiceController;
use App\Http\Controllers\OrderReturnController;
use App\Http\Controllers\ShopbyConcernController;
use App\Http\Controllers\CategoryScreenController;
use App\Http\Controllers\ProductDetailsController;
use App\Http\Controllers\MedicinesDetailsContriller;
use App\Http\Controllers\SubCategoryScreenController;
use App\Http\Controllers\MymedicineReminderController;
use App\Http\Controllers\MymedicineController;
use App\Http\Controllers\UploadPrescriptionController;
use App\Http\Controllers\CategoryProductListController;
use App\Http\Controllers\MoveSavedItemToCartController;
use App\Http\Controllers\WalletCouponController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\PaymentResultController;
use App\Http\Controllers\PostingController;
use App\Http\Controllers\Shipments\ShippingConsignment;
use App\Http\Controllers\SocialLogins\SocialLoginController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\PackingController;
use App\Http\Controllers\AttributeController;

use App\Http\Controllers\Partner\PartnerSubscriptionController;
/*

|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['prefix' => 'back-office'], function () {
Route::get('cpd-order',[TestController::class, 'index']);
});
Route::get('test', [TestController::class, 'index']);



Route::post('login', [AuthController::class, 'login']);
Route::group(['middleware' => 'jwt.verify'], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});



Route::group(['prefix' => 'signup'], function () {
    Route::get('socials/login/redirect/{type}', [SocialLoginController::class, 'redirectToLogin']);
    Route::get('socials/login/request/{type}', [SocialLoginController::class, 'socialLoginRequest']);
    Route::post('socials/{type}', [SocialLoginController::class, 'socialLogins']);
    Route::post('verify/password', [VerificationController::class, 'verifyPassword']);
    Route::post('verify/{type?}', [VerificationController::class, 'verify']);
    Route::post('pincode', [PincodeController::class, 'get']);
    Route::post('customer', [RegistrationController::class, 'store']);
    Route::post('{type}', [VerificationController::class, 'store']);
});

Route::group(['prefix' => 'impersonate', 'middleware' => 'jwt.verify'], function () {
    Route::post('/token', [VerificationController::class, 'impuser']);
});

//Route::group(['prefix' => 'kalyera'], function () {
//    Route::post('/', [VerificationController::class, 'incomingcall']);
//});


Route::group(['prefix' => 'home'], function () {
    Route::get('category', [CategoryController::class, 'get']);
    Route::get('/main/{id}/{order_method}/{btype?}/{retailtype?}', [HomeScreenController::class, 'get']);
    Route::get('/getfield', [HomeScreenController::class, 'getfield']);
    Route::get('categorymenu', [CategoryController::class, 'getCategoryMenuList']);
    Route::get('filteredcategorymenu', [CategoryController::class, 'getCategoryMenuListNew']);

    Route::get('virtualsubcats/{vcid}', [CategoryController::class, 'GetVirtualSubcategories']);
    Route::get('/retailertypes/{businessTypeId}', [BusinessTypeController::class, 'GetRetailTypes']);
    Route::get('/businesstypes/{retailCatId}', [BusinessTypeController::class, 'getBusinesstypeByRetailCategory']);
    Route::get('/offers/{business_type?}/{retailtype?}/{sort?}/{filter_type?}/{filter_value?}', 'HomeScreenController@getOffers');
    Route::get('/products/{type?}/{business_type?}/{retailtype?}/{size?}', 'HomeScreenController@getProductsPaged');
    Route::get('/advertisement/sidebanner/{btype?}/{retailtype?}', 'HomeScreenController@getSideBannerAdvertisement');
});


// attribute api
Route::group(['prefix' => 'attributes'], function () {
    // {type}   => product/category
    // {id}     => product id/category id
    Route::get('/{type}/{id}', [AttributeController::class, 'getAttributes']);
});

Route::group(['prefix' => 'categoryscreen'], function () {
    Route::post('/', [CategoryScreenController::class, 'getdata']);
});
Route::group(['prefix' => 'subcategoryscreen'], function () {
    Route::post('/', [SubCategoryScreenController::class, 'getdata']);
});

//State list
Route::group(['prefix' => 'state'], function () {
    Route::get('list/{country?}', [StateController::class, 'getStates']);
});
//District list
Route::group(['prefix' => 'district'], function () {
    Route::get('list/{state?}', [DistrictController::class, 'getDistricts']);
});


Route::group(['prefix' => 'healthconcernscreen'], function () {

    Route::post('/', [ShopbyConcernController::class, 'getdata']);
});



Route::group(['prefix' => 'innersubcategoryscreen'], function () {
    Route::post('/', [InnerSubCategoryScreen::class, 'getdata']);
});

Route::group(['prefix' => 'brandscreen'], function () {
    Route::post('/', [BrandScreenController::class, 'getdata']);
});

Route::group(['prefix' => 'feedback', 'middleware' => 'jwt.verify'], function () {
    Route::post('/', [FeedbackController::class, 'store']);
});


Route::group(['prefix' => 'products'], function () {
    Route::post('item', [ProductController::class, 'productDetails']);
    Route::post('item/grouped', [ProductController::class, 'getGroupedProducts']);
    Route::post('item/other', [ProductController::class, 'getOtherProducts']);
    Route::post('item/{type}', [ProductController::class, 'getSimilarLikeProducts']);
});

Route::group(['prefix' => 'subcategory'], function () {
    //Route::get('/{id}/products', 'ProductController@get');
    Route::post('/products', [ProductController::class, 'getItem']);
});


Route::group(['prefix' => 'category'], function () {
    Route::get('/{id}/subcategory', [SubcategoryController::class, 'get']);
    Route::get('relatedcategories/{cid}/{clevel}', [CategoryController::class, 'GetRelatedCategoryList']);
    Route::get('virtual/items/{typeID}/{vcID}', [CategoryController::class, 'getVirtualCategoryItems']);
});



Route::group(['prefix' => 'wishlist', 'middleware' => 'jwt.verify'], function () {
    Route::post('/', [SavedItemController::class, 'create']);
    Route::get('/{order_method}', [SavedItemController::class, 'get']);
    Route::delete('/{groupId}/{productId}/{order_method}', [SavedItemController::class, 'delete']);
});

Route::group(['prefix' => 'wishlist-to-cart', 'middleware' => 'jwt.verify'], function () {
    Route::post('/{group_id}/{productId}/{order_method}', [MoveSavedItemToCartController::class, 'index']);
});


Route::group(['prefix' => 'customer', 'middleware' => 'jwt.verify'], function () {
    Route::put('/', [CustomerController::class, 'edit']);
    Route::get('/', [CustomerController::class, 'get']);
    Route::post('/age/verify', [CustomerController::class, 'ageVerification']);
    Route::post('/address', [CustomerController::class, 'addAddress']);
    Route::get('/address', [CustomerController::class, 'getAddress']);
    Route::get("/wallet/balance", "CustomerController@getWallet");
    Route::get("/wallet/history", "CustomerController@getWalletHistory");
    Route::post("/wallet/history", "CustomerController@getWalletHistoryFiltered");
    Route::get("/deactivate", "CustomerController@deactivateAccount");
    Route::delete('/{id}',[CustomerController::class, 'delete']);
});

Route::group(['prefix' => 'address', 'middleware' => 'jwt.verify'], function () {
    Route::put('/{id}/primary', 'AddressController@setPrimary');
});


Route::group(['prefix' => 'orders', 'middleware' => 'jwt.verify'], function () {
    Route::get('/', 'OrderController@get');
    Route::get('/{id}/status', 'OrderStatusController@get');
    Route::post('/cancel', 'OrderCancelledController@store');
    Route::post('/setslot', 'OrderCompleteController@setslot');
    Route::post('/notes', 'OrderCompleteController@notes');
    Route::post('/reloadorder', 'OrderCompleteController@reloadOrder');
});

//Nearest location fetch for branch
Route::group(['prefix' => 'location'], function () {
    Route::post('/', [BranchController::class, 'getlocation']);
});
Route::get('branches/list', [BranchController::class, 'getBranchesByStoregroup']);

Route::group(['prefix' => 'key'], function () {
    Route::post('search/', [MedicinesDetailsContriller::class, 'medicineSearch']);
});
Route::group(['prefix' => 'search', 'middleware' => 'auth.guest'], function () {
    Route::post('products-search',  [MedicinesDetailsContriller::class, 'newSearch']);
    Route::post('group-search',  [MedicinesDetailsContriller::class, 'searchByGroupId']);
    Route::get('group-products',  [MedicinesDetailsContriller::class, 'getAllGroupItems']);

});
Route::group(['prefix' => 'medicine', 'middleware' => 'jwt.verify'], function () {
    Route::post('details/', [MedicinesDetailsContriller::class, 'medicineDetails']);
    Route::post('alternate/', [MedicinesDetailsContriller::class, 'medicineAlternalte']);
    Route::post('viewall/', [MedicinesDetailsContriller::class, 'viewallalernatemedicineList']);
});
Route::get('initial', 'HomeScreenController@getCredentials'); // TODO: Security — this endpoint issues a dummy JWT without auth; may be intentional for guest browsing but should be reviewed.

Route::group(['prefix' => 'category'], function () {
    Route::post('prodectlist/', [CategoryProductListController::class, 'productlist']);
});

Route::group(['prefix' => 'product'], function () {
    Route::post('viewall/', [ProductDetailsController::class, 'viewall']);
    Route::post('brandlist/', [ProductDetailsController::class, 'allbrands']);
    Route::post('popularproductslist/', [ProductDetailsController::class, 'popularproductslist']);
    Route::post('browsebycategory/', [ProductDetailsController::class, 'browsebyCategory']);
    Route::post('sortFilter/', [FilterProductController::class, 'sortfilter']);
    Route::post('sortFilterSearch/', [FilterProductController::class, 'sortFilterSearch']);
    Route::post('grouppricerange/', [ProductDetailsController::class, 'Grouppricerange']);

});

Route::group(['prefix' => 'document', 'middleware' => 'jwt.verify'], function () {
    Route::post('uploadprescription/', [UploadPrescriptionController::class, 'upload_data']);
    Route::put('uploadprescription', [UploadPrescriptionController::class, 'update_upload_data']);
    Route::get('getprescription/', [UploadPrescriptionController::class, 'getdiscription']);
    Route::delete('prescription/{id}', [UploadPrescriptionController::class, 'delete_prescription']);
    Route::post('uploadimage/', [ProductImageController::class, 'imageUploadPost']);
});


Route::post('nearestRetailer', [RetailerController::class, 'get']);
Route::get('order/order_success/{order_id}', [CartController::class, 'order_success']);
Route::post('neareststores', [RetailerController::class, 'getNearestStores']);
Route::post('nearestbranches', [RetailerController::class, 'getNearestBranches']);

Route::group(['prefix' => 'cart', 'middleware' => 'auth.guest'], function () {
    Route::post('/', [CartController::class, 'store']);
    Route::get('/{order_method}', [CartController::class, 'get']);
    Route::get('/cartdetails/{order_method}', [CartController::class, 'cartdetails']);
    Route::delete('/clear/items/{order_method}', [CartController::class, 'clear']);
    Route::delete('/{id}', [CartController::class, 'delete']);
    Route::put('/', [CartController::class, 'edit']);
    Route::get('/summary/{order_method}', [CartController::class, 'cartSummary']);
});

Route::group(['prefix' => 'cart', 'middleware' => 'jwt.verify'], function () {
    Route::get('/wishlistdetails/{order_method}', [CartController::class, 'wishlistdetails']);
    Route::get('/checkout', [CartController::class, 'checkOut']);
    Route::post('bulkCartRequest', [CartController::class, 'bulkstore']);
    Route::post('checkprocessed',[CartController::class, 'checkprocessed']);
    Route::post('replaceitem',[CartController::class, 'ReplaceItem']);
    Route::post('movewishlist',[CartController::class, 'moveToWishList']);

    Route::get('preview/{order_method}',[CartController::class, 'cartPreview']);

});
Route::group(['prefix' => 'checkout', 'middleware' => 'jwt.verify'], function () {
    Route::post('/delivery/step1', [CartController::class, 'delivery_step1']);
    Route::post('/delivery/step2', [CartController::class, 'delivery_step2']);
    Route::post('/delivery/step3', [CartController::class, 'delivery_step3']);
    Route::post('/pickup/step1', [CartController::class, 'pickup_step1']);
    Route::post("/remove", "CheckoutController@removeNotDeliverableOrders");
    Route::post("/confirm", "CheckoutController@confirmorder");
});


Route::group(['prefix' => 'cartorder', 'middleware' => 'jwt.verify'], function () {
    Route::post('details', [CartController::class, 'cartorder']);
    Route::delete('delete', [DeleteCartController::class, 'delete']);


});


Route::post('coupon-wallet', [WalletCouponController::class, 'get'])->middleware('jwt.verify');
Route::post('coupon-remove', [WalletCouponController::class, 'couponRemove'])->middleware('jwt.verify');

Route::get('payment/redirect/{order_group_id}/{podToOnline?}', [PaymentResultController::class, 'redirectToPayment'])->middleware('auth.guest');


Route::post('regioninfo', [CountryController::class, 'get']);

//----------------------------------
Route::middleware('blockIP')->group(function () {
Route::get('/es/create/index/itemmaster', function () {
    return Artisan::call('es:create-index', [
        'index' => 'itemmaster'
    ]);
});

Route::get('/es/create/index/blockeditems', function () {
    return Artisan::call('es:create-index', [
        'index' => 'blockeditems'
    ]);
});

Route::get('/es/create/index/inventory', function () {
    return Artisan::call('es:create-index', [
        'index' => 'inventory'
    ]);
});

Route::get('/es/create/index/productsearch', function () {
    return Artisan::call('es:create-index', [
        'index' => 'productsearch'
    ]);
});

Route::get('/es/export/itemmaster', function () {
    return \App\Models\ESItemmaster::export();
});

Route::get('/es/export/blockeditems', function () {
    return \App\Models\ESBlockedItems::export();
});

Route::get('/es/export/inventory', function () {
    return \App\Models\ESInventory::export();
});

Route::get('/es/export/productsearch', function () {
    return \App\Models\ESProductsearch::export();
});

Route::get('/es/export-all-tables', function () {
    return Artisan::call('es:export-all-tables');
});
}); // end blockIP group
//----------------------------------

//====== ELASTIC SEARCH (auth.guest — public search) ==========
Route::middleware('auth.guest')->group(function () {
    Route::post('es/key/search', 'ProductSearchController@newESSearch')->name('es.key.search');
    Route::post('es/product/search', 'ProductSearchController@newESSortFiletrSearch')->name('es.product.search');
});
//=====================================

Route::post('s3_credential', [CartController::class, 's3_bucket'])->middleware('jwt.verify');
//Route::post('s3Signedurl',[CartController::class, 'getSignedUrl']);

Route::group(["prefix" => "pharmacy", "middleware" => "jwt.verify"], function () {
        Route::post("/checkout", "CheckoutController@checkout");
        Route::post("/instamojo/verify", "CheckoutController@verifyInstamojo");
        Route::post("/instamojo/status", "CheckoutController@instamojoStatus");
        Route::post('/postpayment', 'CheckoutController@postpayment');

});

Route::group(['prefix' => 'site'], function () {
    Route::post('product/viewall/', [ProductDetailsController::class, 'webviewall']);
    Route::post('product/recentlyviewed', [ProductController::class, 'recentlyviewd']);
});

Route::group(['prefix' => 'order', 'middleware' => 'jwt.verify'], function () {
    Route::get('/list/{order_method}', [OrderHistoryController::class, 'list']);
    Route::get('/summary/{order_id}',[OrderHistoryController::class, 'summary']);
    Route::get('detail/{order_id}',[OrderHistoryController::class, 'orderDetails']);
    Route::get('track/{id}',[OrderHistoryController::class, 'trackUrl']);
    Route::put('rating',[OrderHistoryController::class, 'addRating']);
    Route::get('/groupOrders/{order_group_id}', [OrderHistoryController::class, 'groupOrders']);

});




Route::group(['prefix' => 'orders', 'middleware' => 'jwt.verify'], function () {
    Route::post('generateLink', 'CheckoutController@generatePaymentGatewayLink');
    Route::post('invoice', [OrderInvoiceController::class, 'getInvoiceByOrder']);
    Route::get('{orderId}/complete', [OrderCompleteController::class, 'getdata']);
    Route::get('{orderId}/returnables', [OrderReturnController::class, 'getAllReturnableProducts']);
    Route::post('returnables/return', [OrderReturnController::class, 'returnSelectedProducts']);
});

Route::get('app/version/{version}', 'VersionController@getVersion');
Route::post('feedback', 'AboutController@store')->middleware('jwt.verify');
Route::get('faq', 'AboutController@get');
Route::get('pages', 'AboutController@getPages');

    Route::post('paymenthook/{paymentgateway}', [PaymentResultController::class, 'webhook']);


Route::group(['prefix' => 'medicinereminder', 'middleware' => 'jwt.verify'], function () {

    Route::get('mymedicinereminder/{id}', [MymedicineReminderController::class, 'getallitem']);
    Route::post('searchitem', [MymedicineReminderController::class, 'searchitem']);
    Route::post('addreminder', [MymedicineReminderController::class, 'additem']);
    Route::put('updatereminder', [MymedicineReminderController::class, 'updateitem']);
    Route::put('notificationreminder', [MymedicineReminderController::class, 'notificationInterwellupdate']);

    Route::delete('/{id}', [MymedicineReminderController::class, 'delete']);

});

Route::group(['prefix' => 'finascop', 'middleware' => 'blockIP'], function () {
    Route::post('finascopPostingService', [PostingController::class, 'finascopPosting']);
});

Route::group(['prefix' => 'mymedicine', 'middleware' => 'jwt.verify'], function () {

    Route::get('/', [MymedicineController::class, 'get']);
});

//PACKING
Route::group(['prefix' => 'packing', 'middleware' => 'blockIP'], function () {
    // TRACKING WEBHOOK
    Route::post('{type}/callback', [PackingController::class, 'packingCallback']);
});

// SHIPMENTS
Route::group(['prefix' => 'shipments', 'middleware' => 'blockIP'], function () {
    // TRACKING WEBHOOK
    Route::post('tracking/{type}/{provider}', [ShippingConsignment::class, 'updateTrackingDetails']);
    // SHIPMENT DELIVERED WEBHOOK
    Route::post('{provider}/delivered', [ShippingConsignment::class, 'shipmentDelivered']);
});
Route::group(['prefix' => 'partner', 'middleware' => 'jwt.verify'], function () {
    Route::post('subscriptions/{type}/create', [PartnerSubscriptionController::class, 'createSubscription']);
});