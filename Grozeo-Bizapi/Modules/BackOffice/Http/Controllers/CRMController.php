<?php

namespace BackOffice\Http\Controllers;

use Illuminate\Support\Facades\DB;
use BackOffice\Http\Requests\CRM\CRMEnquiryRequest;
use BackOffice\Models\CRM\FinascopCRMEnquiry;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;

class CRMController
{
    public function crmEnquiries(CRMEnquiryRequest $request)
    {
        try
        {
            $inserts = FinascopCRMEnquiry::create([
                'crmm_store_name'           => @$request->business_name,
                'crme_name'                 => @$request->name,
                'crme_mobile'               => @$request->phone,
                'crme_email'                => @$request->email,
                'crmm_location'             => @$request->location,
                'crmm_address'              => @$request->address,
                'crmm_business_category'    => (@$request->business_category) ? $request->business_category : 0,
                'crme_description'          => @$request->message,
                'crms_id'                   => @$request->source,
                'crme_type'                 => (@$request->type) ? $request->type : 1
            ]);
            if($inserts)
            {
                if(@$request->redirect_to != "")
                {
                    return \Redirect::to($request->redirect_to);
                }
                else
                {
                    return new SuccessResponse("Enquiry Added"); 
                }
            }
            return new ErrorResponse("Some error occured."); 
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
}