<?php

namespace App\Models;

use App\Models\CompanyEasebuzz;
use Illuminate\Database\Eloquent\Model;

class CompanyEasebuzz extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_company_easebuzz';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function getCompanyPaydetails($companyid, $storegroupid)
    {
        $defaultstoregroup = 0;
        $attribs = CompanyEasebuzz::where('storegroup_id', $storegroupid)->orWhere([
                        ['comp_id', $companyid],
                        ['storegroup_id', $defaultstoregroup]
                    ])
            ->select('comp_id', 'merchant_id', 'salt_key', 'request_url', 'response_url', 'company_name')
            ->orderBy('storegroup_id', 'desc')->limit(1)->first();
        $attributes=array();
        if(isset($attribs) && ($attribs->comp_id != "") && $attribs->comp_id > 0 )
        {
                
            $attributes['merchantID'] = $attribs->merchant_id;
            $attributes['successUrl'] = url($attribs->response_url);
            $attributes['failureUrl'] = url($attribs->response_url);
            $attributes['paymentURL'] = $attribs->request_url;
            $attributes['saltKey'] = $attribs->salt_key; 
            $attributes['company_name'] = $attribs->company_name;
        }
        else
        {
            $paymentType = config("ccavenue.type");
            $attributes['merchantID'] = config("ccavenue.merchantId");
            $attributes['successUrl'] = url(config("ccavenue.responseUrl"));
            $attributes['failureUrl'] = url(config("ccavenue.responseUrl"));
            $attributes['paymentURL'] = config("ccavenue.{$paymentType}.url");
            $attributes['saltKey'] = config("ccavenue.{$paymentType}.saltKey");
            $attributes['company_name'] = "";
        } 
        return $attributes;
    }
   
}
