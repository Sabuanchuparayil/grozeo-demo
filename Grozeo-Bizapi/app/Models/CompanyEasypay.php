<?php

namespace App\Models;

use App\Models\CompanyEasypay;
use Illuminate\Database\Eloquent\Model;

class CompanyEasypay extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_company_easypay';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function getCompanyPaydetails($companyid)
    {
        // auth_user()->deli_branch_company_id
        $attribs = CompanyEasypay::where('comp_id', $companyid)
            ->select(
            'cid', 'typ','cny','ver','re1','checksumkey','encryptionkey','paymenturl','tokenurl','enquiryurl')
            ->first();
        $attributes=array();        
        if(isset($attribs) && ($attribs->cid != "") ){
           $attributes['cid'] = $attribs['cid'];
           $attributes['typ'] = $attribs['typ'];
           $attributes['ver'] = $attribs['ver'];
           $attributes['re1'] = $attribs['re1'];
           $attributes['cny'] = $attribs['cny'];
           $attributes['checksumkey'] = $attribs['checksumkey'];
           $attributes['encryptionkey'] = $attribs['encryptionkey'];
           $attributes['paymenturl'] = $attribs['paymenturl'];
           $attributes['tokenurl'] = $attribs['tokenurl'];
           $attributes['enquiryurl'] = $attribs['enquiryurl'];
        }else{
            $attributes['cid'] = config('paymentgateway.easypay.cid');
            $attributes['typ'] = config('paymentgateway.easypay.typ');
            $attributes['ver'] = config('paymentgateway.easypay.ver');
            $attributes['re1'] = config('paymentgateway.easypay.re1');
            $attributes['cny'] = config('paymentgateway.easypay.cny');
            $attributes['checksumkey'] = config('paymentgateway.easypay.checksumkey');
            $attributes['encryptionkey'] = config('paymentgateway.easypay.encryptionkey');
            $attributes['paymenturl'] =config('paymentgateway.easypay.paymenturl');
            $attributes['tokenurl'] = config('paymentgateway.easypay.tokenurl');
            $attributes['enquiryurl'] = config('paymentgateway.easypay.enquiryurl');
        }    

           return $attributes;
    }
   
}
