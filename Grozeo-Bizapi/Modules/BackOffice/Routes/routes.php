<?php

use Illuminate\Support\Facades\Log;
use BackOffice\Tasks\BoyActivitySummary;
use BackOffice\Tasks\PacksureForceLogOut;
use BackOffice\Http\Controllers\OtpController;
use BackOffice\Http\Controllers\AuthController;
use BackOffice\Http\Controllers\Boy\LogoutController;
use BackOffice\Http\Controllers\OrderAcceptedController;
use BackOffice\Http\Controllers\Boy\BoyLocationController;
use BackOffice\Http\Controllers\Boy\RevokeOrderController;
use BackOffice\Http\Controllers\BranchInventoryController;
use BackOffice\Http\Controllers\OtpVerificationController;
use BackOffice\Http\Controllers\Boy\BoyOrderAssignController;
use BackOffice\Http\Controllers\Order\OrderProceedController;
use BackOffice\Http\Controllers\Boy\BoyOrderPendingController;
use BackOffice\Http\Controllers\Boy\BoyOrderIncompleteController;
use BackOffice\Http\Controllers\Boy\BoyOrderSummaryController;
use BackOffice\Http\Controllers\Boy\BoyDashboardController;
use BackOffice\Http\Controllers\Boy\BoyOrderCancelController;
use BackOffice\Http\Controllers\Boy\BoyOrdeRerackController;
use BackOffice\Http\Controllers\CompletedOrderListingController;
use BackOffice\Http\Controllers\Order\GenerateCpdOrderController;
use BackOffice\Http\Controllers\Order\SingleItemProceedController;
use BackOffice\Http\Controllers\Boy\BoyOrderSummaryDetailsController;
use BackOffice\Http\Controllers\Order\OrderProceedNoBarcodeController;
use BackOffice\Http\Controllers\Transfer\RevokeTransferOrderController;
use BackOffice\Http\Controllers\Transfer\TransferOrderProceedController;
use BackOffice\Http\Controllers\Transfer\TransferOrderAcceptedController;
use BackOffice\Http\Controllers\Transfer\TransferOrderReplenishController;
use BackOffice\Http\Controllers\Transfer\BoyTransferOrderAssignController;
use BackOffice\Http\Controllers\Order\SingleItemNoBarcodeProceedController;
use BackOffice\Http\Controllers\Transfer\GenerateTransferRequestController;
use BackOffice\Http\Controllers\Transfer\SingleTransferItemProceedController;
use BackOffice\Http\Controllers\Transfer\Generate3TierTransferRequestController;
use BackOffice\Http\Controllers\Transfer\TransferOrderNoBarcodeProceedController;
use BackOffice\Http\Controllers\Transfer\SingleTransferItemNoBarcodeProceedController;
use BackOffice\Http\Controllers\Boy\BoyOrderDeliveredController;
use BackOffice\Http\Controllers\Agent\AgentMasterDataController;
use BackOffice\Http\Controllers\Boy\BoyBranchInventoryController;
use BackOffice\Http\Controllers\Boy\BoyBranchController;
use BackOffice\Http\Controllers\ReduceStockController;

use BackOffice\Http\Controllers\BlockedIpController;
use BackOffice\Http\Controllers\SupportTicketController;
use BackOffice\Http\Controllers\LeadboardController;
use BackOffice\Http\Controllers\StockInventoryLogsController;
use BackOffice\Http\Controllers\FinanceAutpostingController;
use BackOffice\Http\Controllers\CRMController;
use BackOffice\Http\Controllers\Transfer\TransferOrderDetailsController;


// Relation Officer
use BackOffice\Http\Controllers\RelationOfficer\ROUserController;
use BackOffice\Http\Controllers\RelationOfficer\RODashboardController;
use BackOffice\Http\Controllers\RelationOfficer\ROLoginController;
use BackOffice\Http\Controllers\RelationOfficer\ROContactsController;
use BackOffice\Http\Controllers\RelationOfficer\ROLeadController;
use BackOffice\Http\Controllers\RelationOfficer\ROProspectController;
use BackOffice\Http\Controllers\RelationOfficer\ROMerchantController;
use BackOffice\Http\Controllers\RelationOfficer\ROSurveyController;
use BackOffice\Http\Controllers\RelationOfficer\ROSupportTicketController;

use BackOffice\Http\Controllers\SchedulerController;

use BackOffice\Http\Repositories\RelationOfficer\AreaFinderCheck;

use BackOffice\Http\Controllers\WalletApiController;

//Log::debug(request()->fullUrl() . " -- " . request()->getQueryString());
//Log::debug("Header" . "--" . print_r(request()->headers->all(),true));
Route::group(['prefix' => 'api/back-office'], function () {
    // Route::post('auth/login', [AuthController::class, 'login']);
    Route::group(['prefix' => 'auth', 'middleware' => 'back-office-jwt.auth'], function () {
        Route::post('logout', LogoutController::class);       
    });
    
    //ForceLogut - From Web UI
    Route::post('/forcelogout/{mobno}', PacksureForceLogOut::class);

    //order - OUTDATED
    /*Route::group(['prefix' => 'orders', 'middleware' => 'back-office-jwt.auth'], function () {
        Route::get('/', CompletedOrderListingController::class);
        Route::post('/{orderId}/proceed', OrderProceedController::class);
        Route::post('/{orderId}/proceednobarcode', OrderProceedNoBarcodeController::class);
        Route::post('/{orderId}/accept', OrderAcceptedController::class);
    });*/

    //Boy's MyOrders
    Route::group(['prefix' => 'boy', 'middleware' => 'back-office-jwt.auth'], function () {        
        Route::get('/dashboard', BoyDashboardController::class);
        Route::post('/order/cancel', BoyOrderCancelController::class);
        Route::post('/order/rerack', BoyOrdeRerackController::class);
        Route::post('/orderssummary', BoyOrderSummaryController::class);
        Route::post('/orderdetails', BoyOrderSummaryDetailsController::class);
        Route::post('/pendingorders', BoyOrderPendingController::class);
        Route::get('/pendingorders/count', [BoyOrderPendingController::class,'getOrderCount']);
        Route::get('/pendingorders/polledorder', [BoyOrderPendingController::class,'getPolledOrder']);
        Route::post('/deliveredorders', BoyOrderDeliveredController::class);
        Route::post('/verifycollectotp',[ BoyOrderDeliveredController::class, 'verifyotp']);
        Route::post('/incompleteorders', BoyOrderIncompleteController::class);
        Route::get('/incompleteorders/count', [BoyOrderIncompleteController::class,'getOrderCount']);
        Route::post('/branchstatus', [BoyBranchInventoryController::class,'getBranchStatus']);
        Route::post('/setbranchstatus', [BoyBranchInventoryController::class,'updateBranchesStatus']);
        Route::post('/getbranchinventory', [BoyBranchInventoryController::class,'getBranchInventory']);
        Route::post('/setbranchinventory', [BoyBranchInventoryController::class,'updateBrancheItems']);

        // APIs for Godown boy with branch controls
        Route::group(['prefix' => 'branch'], function () {
            Route::post('/orders', [BoyBranchController::class,'getBranchOrders']);
        });
    });
    
    //order - OUTDATED
    /*Route::group(['prefix' => 'items', 'middleware' => 'back-office-jwt.auth'], function () {
        Route::post('/proceed', SingleItemProceedController::class);
        Route::post('/proceednobarcode', SingleItemNoBarcodeProceedController::class);
    });*/
    
    Route::post('otp', OtpController::class);
    Route::post('otp/verify', OtpVerificationController::class);
    Route::post('BoyActivitySummary', BoyActivitySummary::class);  
    
    
    Route::group(['prefix' => 'agent'], function () {
        Route::get('/brand', [AgentMasterDataController::class,'getBrands']);
        Route::get('/store', [AgentMasterDataController::class,'getBranches']);
        Route::post('/storeproducts', [AgentMasterDataController::class,'getBranchProducts']);
        Route::get('/storegroups', [AgentMasterDataController::class,'getBranchGroup']);
        Route::get('/businesstype', [AgentMasterDataController::class,'getBusinesstype']);
        Route::get('/states', [AgentMasterDataController::class,'getStates']);
        Route::get('/districts/{stateid}', [AgentMasterDataController::class,'getDistricts']);  
        Route::post('/addstore', [AgentMasterDataController::class,'addBranches']);        
        Route::post('/updatestore', [AgentMasterDataController::class,'updateBranches']); 
        Route::post('/addstoregroup', [AgentMasterDataController::class,'addbranchgroup']);        
        Route::post('/updatestoregroup', [AgentMasterDataController::class,'updatebranchgroup']);     
        Route::post('/updatestorestatus', [AgentMasterDataController::class,'updateBranchesStatus']); 
        Route::post('/setstoreasdefault', [AgentMasterDataController::class,'setstoreasdefault']);              
       // Route::get('/brand', 'HomeScreenController@getBrands');
    });

    //Order
    //Route::post('cpd-order', GenerateCpdOrderController::class); //Invoke CPD order build - OUTDATED   
    //Route::post('order-assign', BoyOrderAssignController::class); //assigning CPD orders & b2c orders to godown boy - OUTDATED
    //Route::post('order-revoke', RevokeOrderController::class); //Revoke CPD orders and B2C orders - OUTDATED

    //Transfer
    Route::post('transfer-request', GenerateTransferRequestController::class); //Invoke CPD order build - NEW   
    Route::post('transfer-request-3tier', Generate3TierTransferRequestController::class); //Invoke CPD order build
    Route::post('transfer-order-assign', BoyTransferOrderAssignController::class); //assigning TRANSFER ORDERS  to godown boy    
    Route::post('transfer-order-revoke', RevokeTransferOrderController::class); //Revoke Transfers orders  from godown boy
    //Order Picker REvoke
    Route::group([ 'middleware' => 'back-office-jwt.auth'], function () {
        Route::post('transfer-order-picker-revoke', RevokeTransferOrderController::class); //Requesting  TRANSFER ORDERS by godown boy
    });
    //Order Picker Request
    Route::group([ 'middleware' => 'back-office-jwt.auth'], function () {
        Route::post('transfer-order-picker-request', BoyTransferOrderAssignController::class); //Requesting  TRANSFER ORDERS by godown boy
    });
    //Transfer
    Route::group(['prefix' => 'transfers/items', 'middleware' => 'back-office-jwt.auth'], function () {
        Route::post('/proceed', SingleTransferItemProceedController::class);
        Route::post('/proceednobarcode', SingleTransferItemNoBarcodeProceedController::class);
    });

    //Transfer Manual Packing
    Route::group(['prefix' => 'manualtransfers'], function () {
        Route::post('/{orderId}/proceed', TransferOrderProceedController::class);
        Route::post('/{orderId}/proceednobarcode', TransferOrderNoBarcodeProceedController::class);
        Route::post('/{orderId}/replenish', TransferOrderReplenishController::class);        
        Route::post('/generateshipment', [TransferOrderNoBarcodeProceedController::class,'generateshipment']); 
    });

    //Transfer
    Route::group(['prefix' => 'transfers', 'middleware' => 'back-office-jwt.auth'], function () {
        Route::get('/', CompletedOrderListingController::class);
        Route::post('/accept', TransferOrderAcceptedController::class);
        Route::post('/{orderId}/proceed', TransferOrderProceedController::class);
        Route::post('/{orderId}/proceednobarcode', TransferOrderNoBarcodeProceedController::class);
        //Route::post('/{orderId}/accept', TransferOrderAcceptedController::class);
        Route::post('/{orderId}/replenish', TransferOrderReplenishController::class);
        Route::post('/updateorderpackages', [TransferOrderNoBarcodeProceedController::class,'updatePackages']); 
    });



    Route::group(['prefix' => 'transfersjwt', 'middleware' => 'back-office-jwt.auth'], function () {
        Route::get('/', CompletedOrderListingController::class);
       
    });



    Route::group(['prefix' => 'boy', 'middleware' => 'back-office-jwt.auth'], function () {
        Route::post('location', BoyLocationController::class);
        Route::post('order-assign', BoyOrderAssignController::class);
        Route::post('order/details', TransferOrderDetailsController::class);
    });

    Route::group(['prefix' => 'branch_inventory', 'middleware' => ['back-office-branch.auth','branchThrottle:1,9']], function () {
        Route::get('/', [BranchInventoryController::class, 'get']);
        Route::post('/',[BranchInventoryController::class, 'saveInventory']);
    });
    
     //Sub product stock update
    Route::group(['prefix' => 'admin'], function () {
        Route::post('/updatestock', ReduceStockController::class);
    });

    //ip restrictions yellow.ai
    Route::group(['prefix' => 'externals', 'middleware' => 'blockIP'], function () {
        Route::post('/get-orders',[BlockedIpController::class, 'getOrdersByPhone']);
        Route::post('/order', [BlockedIpController::class, 'getSingleOrder']);
        Route::post('/crm/save', [BlockedIpController::class, 'saveCRMHistory']);
    });
    Route::group(['prefix' => 'externals'], function () {
        Route::group(['prefix' => 'leadboard'], function () {
            Route::post('/nearest-area', [LeadboardController::class, 'getBusinessAssociateByLatLong']);
            Route::post('/create', [LeadboardController::class, 'createNewLead']);
            
        });
    });
    Route::post('/inventory-log/save', [StockInventoryLogsController::class, 'saveStockInventoryLogs']);
    Route::post('/nearest-baarea', [AreaFinderCheck::class, 'callareaCheckLatLong']);

    Route::group(['prefix' => 'wallet'], function () {
        Route::post('/create',[WalletApiController::class, 'createWalletEntry']);
    });

    Route::post('/autoposting/update', [FinanceAutpostingController::class, 'updateAutopostings']);
});


Route::group(['prefix' => 'api/relation-office'], function () {

    Route::post('otp', [ROLoginController::class, 'createOtp']);
    Route::post('otp/verify', [ROLoginController::class, 'otpVerification']);
    Route::group(['prefix' => 'auth', 'middleware' => 'relation-office.auth'], function () {
        Route::post('logout', [ROLoginController::class, 'logout']);       
    });

    // ba -> business associate
    Route::group(['prefix' => 'ba', 'middleware' => 'relation-office.auth'], function () {

        // DASHBOARD
        Route::get('dashboard/count', [RODashboardController::class, 'getCounts']);
        
        // PROFILE
        Route::get('user/profile', [ROUserController::class, 'profile']);

        // CONTACTS
        Route::get('contact-types', [ROContactsController::class, 'getContactTypes']);
        Route::get('retail-categories/{type?}', [ROContactsController::class, 'getRetailCategories']);
        Route::get('crm-status', [ROContactsController::class, 'getCRMStatus']);
        Route::get('contacts', [ROContactsController::class, 'getAllContacts']);
        Route::post('contacts/create', [ROContactsController::class, 'addNewContact']);
        Route::get('contacts/{contactID}', [ROContactsController::class, 'viewSingleContact']);
        Route::post('contacts/{contactID}/update', [ROContactsController::class, 'updateSelectedContact']);
        Route::post('contacts/{contactID}/status', [ROContactsController::class, 'updateContactStatus']);
        Route::post('contacts/filter', [ROContactsController::class, 'getFilteredContacts']);
        Route::post('contacts/search', [ROContactsController::class, 'getSearchedContacts']);
        Route::get('contacts/upload/link', [ROContactsController::class, 'createImageUploadLink']);

        // LEADS
        Route::get('leads', [ROLeadController::class, 'getAllLeads']);
        Route::get('lead/{leadID}', [ROLeadController::class, 'viewSingleLead']);
        Route::post('lead/{leadID}/update', [ROLeadController::class, 'updateLead']);
        Route::get('lead/{leadID}/convert', [ROLeadController::class, 'leadToProspect']);
        Route::post('leads/filter', [ROLeadController::class, 'getFilteredLeads']);
        Route::post('leads/search', [ROLeadController::class, 'getSearchedLeads']);

        // PROSPECTS
        Route::get('prospects', [ROProspectController::class, 'getAllProspects']);
        Route::get('prospect/{prID}', [ROProspectController::class, 'viewSingleProspect']);
        Route::post('prospects/filter', [ROProspectController::class, 'getFilteredProspects']);
        Route::post('prospects/search', [ROProspectController::class, 'getSearchedProspects']);
        Route::get('prospect/{prID}/invitation', [ROProspectController::class, 'sendProspectInvitation']);
        Route::post('prospect/{prID}/invitation/approve', [ROProspectController::class, 'approveProspectInvitation']);

        // MERCHANTS
        Route::get('merchants', [ROMerchantController::class, 'getAllMerchants']);
        Route::get('merchant/{prID}', [ROMerchantController::class, 'viewSingleMerchant']);
        Route::post('merchants/filter', [ROMerchantController::class, 'getFilteredMerchants']);
        Route::post('merchants/search', [ROMerchantController::class, 'getSearchedMerchants']);
        
        // SURVEY
        Route::group(['prefix'   => 'survey'], function(){
            Route::get('questionnaire/{type?}', [ROSurveyController::class, 'questionAnswers']);
            Route::post('submit', [ROSurveyController::class, 'submitAnswers']);
            Route::get('details/{userID}/{type?}', [ROSurveyController::class, 'surveyDetails']);
        });

        Route::group(['prefix'   => 'support-ticket'], function(){
            Route::get('units/{type?}', [ROSupportTicketController::class, 'supportUnitList']);
            Route::get('list/{type?}', [ROSupportTicketController::class, 'supportTicketListing']);
            Route::get('/upload/link/{extension?}', [ROSupportTicketController::class, 'createPresignedURL']);
            Route::post('submit', [ROSupportTicketController::class, 'submitSupportTicket']);
        });
    });
});

//support ticket
Route::group(['prefix' => 'api/support-ticket'], function () {
    Route::post('/create',[SupportTicketController::class, 'createNewSupportTicket']);
    Route::get('/upload/link', [SupportTicketController::class, 'createPresignedURL']);
    Route::post('/log/add',[SupportTicketController::class, 'addSupportTicketLog']);
});

Route::group(['prefix'   => 'api/crm'], function(){
    Route::post('enquiry', [CRMController::class, 'crmEnquiries']);
});

Route::group(['prefix'   => 'api/schedulers'], function(){
    Route::get('test', [SchedulerController::class, 'testScheduler']);
});