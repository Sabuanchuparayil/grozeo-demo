<?php

namespace App\Models;

use App\Models\CompanyRazorpay;
use Illuminate\Database\Eloquent\Model;

class CompanyRazorpay extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_company_razorpay';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function getCompanyPaydetails($companyid, $storegroupid, $mode = '1')
    {
        $defaultstoregroup = 0;
        // auth_user()->deli_branch_company_id
        $attribs = CompanyRazorpay::where([
            ['storegroup_id', $storegroupid],
            ['mode', (string)$mode]
        ])->orWhere([
            ['comp_id', '=', $companyid],
            ['storegroup_id', '=', $defaultstoregroup]
        ])
        ->select(
        'comp_id', 'key_id', 'key_secret','cny','url', 'company_name')
        ->orderBy('storegroup_id', 'desc')->limit(1)
        ->first();
        $attributes=array();
        if(isset($attribs) && ($attribs->comp_id != "") && $attribs->comp_id > 0 ){
           $attributes['key_id'] = $attribs['key_id'];
           $attributes['key_secret'] = $attribs['key_secret'];
           $attributes['cny'] = $attribs['cny'];
           $attributes['url'] = $attribs['url'];
           $attributes['company_name'] = $attribs['company_name'];
        }else{
           $attributes['key_id'] = config('key_id');
           $attributes['key_secret'] = config('key_secret');
           $attributes['cny'] = config('cny');
           $attributes['url'] = config('url');

        }    

           return $attributes;
    }
   
}
