<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyInstamojo extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_company_instamojo';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function getCompanyPaydetails($companyid)
    {
        // auth_user()->deli_branch_company_id
        $attribs = CompanyInstamojo::where('comp_id', $companyid)
            ->select(
            'api_key', 
            'auth_token',
            'url')
            ->first();
        $attributes=array();        
        if(isset($attribs) && ($attribs->api_key != "") ){
           $attributes['api_key'] = $attribs['api_key'];
           $attributes['auth_token'] = $attribs['auth_token'];
           $attributes['url'] = $attribs['url'];
        }else{
            $attributes['api_key'] = config('paymentgateway.instamojo.api_key');
            $attributes['auth_token'] =  config('paymentgateway.instamojo.auth_token');
            $attributes['url'] =  config('paymentgateway.instamojo.url');
        }    

           return $attributes;
    }
   
}
