<?php

namespace App\Models;

use App\Models\CompanyCCAvenue;
use Illuminate\Database\Eloquent\Model;

class CompanyCCAvenue extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_company_ccavenue';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function getCompanyPaydetails($companyid, $storegroupid)
    {
        $defaultstoregroup = 0;
        $attribs = CompanyCCAvenue::where('storegroup_id', $storegroupid)->orWhere([
                        ['comp_id', $companyid],
                        ['storegroup_id', $defaultstoregroup]
                    ])
            ->select('comp_id', 'merchant_id', 'access_code', 'working_key', 'cny', 'lang', 'request_url', 'response_url', 'company_name', 'integration')
            ->orderBy('storegroup_id', 'desc')->limit(1)->first();
        $attributes=array();
        if(isset($attribs) && ($attribs->comp_id != "") && $attribs->comp_id > 0 )
        {
                
            $attributes['merchantID'] = $attribs->merchant_id;
            $attributes['redirectUrl'] = url($attribs->response_url);
            $attributes['cancelUrl'] = url($attribs->response_url);
            $attributes['currency'] = $attribs->cny;
            $attributes['language'] = $attribs->lang;
            $attributes['integration'] = $attribs->integration;
            $attributes['paymentURL'] = $attribs->request_url;
            $attributes['accessCode'] = $attribs->access_code;
            $attributes['workingKey'] = $attribs->working_key;
            $attributes['key_id'] = "";
        }
        else
        {
            $paymentType = config("ccavenue.type");
            $attributes['merchantID'] = config("ccavenue.merchantId");
            $attributes['redirectUrl'] = url(config("ccavenue.responseUrl"));
            $attributes['cancelUrl'] = url(config("ccavenue.responseUrl"));
            $attributes['currency'] = config("ccavenue.currency");
            $attributes['language'] = config("ccavenue.language");
            $attributes['integration'] = config("ccavenue.integration");
            $attributes['paymentURL'] = config("ccavenue.{$paymentType}.url");
            $attributes['accessCode'] = config("ccavenue.{$paymentType}.accessCode");
            $attributes['workingKey'] = config("ccavenue.{$paymentType}.workingKey");
            $attributes['key_id'] = "";
        } 
        return $attributes;
    }
   
}
