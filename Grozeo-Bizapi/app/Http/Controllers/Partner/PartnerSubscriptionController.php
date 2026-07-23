<?php
namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Responses\{
    ErrorWithData,
    ErrorResponse,
    SuccessResponse,
    SuccessWithData
};
use App\Models\Branch;
use App\Http\Requests\Partner\PartnerSubscriptionRequest;

class PartnerSubscriptionController extends Controller
{
    public function __construct()
    {
    }

    public function createSubscription(PartnerSubscriptionRequest $request, $type)
    {
        try
        {
            $storegroupID = getHeaderStoreGroup();
            $pgClass = config("paymentgateway.{$type}.class");
            $pgObj = new $pgClass();

            $branchData = Branch::where('br_storeGroup', $storegroupID)->with('storegroup')->first();

            $response = $pgObj->partnerSubscription($request, $branchData);
            if(@$response['status'] == 'success')
            {
                return new SuccessWithData([
                    'subscription'  => $response['subscription'],
                    'customer'      => $response['customer']
                ]);
            }
            return new ErrorWithData($response, 400);
        }
        catch (\Exception $e)
        {
            // info('PartnerSubscriptionController createSubscription() ERROR => ');info($e);
            return new ErrorResponse("Operation Failed");
        }
    }
}