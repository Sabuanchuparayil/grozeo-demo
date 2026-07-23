<?php

namespace App\Models;

use App\Models\CompanyRevolut;
use Illuminate\Database\Eloquent\Model;

class CompanyRevolut extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_company_revolut';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function getCompanyPaydetails($companyid, $storegroupid)
    {
        $defaultstoregroup = 0;
        $attribs = CompanyRevolut::where('storegroup_id', $storegroupid)->orWhere([
                        ['comp_id', $companyid],
                        ['storegroup_id', $defaultstoregroup]
                    ])
            ->select('comp_id', 'secret_key', 'public_key', 'api_version', 'currency', 'country_code', 'response_url', 'company_name', 'storegroup_id')
            ->orderBy('storegroup_id', 'desc')->limit(1)->first();
        $attributes=array();
        $paymentType = config("revolut.type");
        if(isset($attribs) && ($attribs->comp_id != "") && $attribs->comp_id > 0 )
        {
            $attributes['secretKey'] = $attribs->secret_key;
            $attributes['publicKey'] = $attribs->public_key;
            $attributes['apiVersion'] = $attribs->api_version;
            $attributes['redirectUrl'] = url($attribs->response_url);
            $attributes['cancelUrl'] = url($attribs->response_url);
            $attributes['currency'] = $attribs->currency;
            $attributes['countryCode'] = $attribs->country_code;
        }
        else
        {
            $attributes['secretKey'] = config("revolut.{$paymentType}.secretKey");
            $attributes['publicKey'] = config("revolut.{$paymentType}.publicKey");
            $attributes['apiVersion'] = config("revolut.{$paymentType}.apiVersion");
            $attributes['redirectUrl'] = url(config("revolut.responseUrl"));
            $attributes['cancelUrl'] = url(config("revolut.responseUrl"));
            $attributes['currency'] = config("revolut.currency");
            $attributes['countryCode'] = config("revolut.countryCode");
        }

        $attributes['orderURL'] = config("revolut.{$paymentType}.orderURL");
        $attributes['refundURL'] = config("revolut.{$paymentType}.refundURL");
        $attributes['getOrder'] = config("revolut.{$paymentType}.getOrder");
        $attributes['taxCalc'] = config("revolut.taxCalc") ?? 0.2;
        $attributes['key_id'] = "";
        return $attributes;
    }
   
}
