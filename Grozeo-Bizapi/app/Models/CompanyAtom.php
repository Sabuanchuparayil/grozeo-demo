<?php

namespace App\Models;

use App\Models\CompanyAtom;
use Illuminate\Database\Eloquent\Model;

class CompanyAtom extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_company_atom';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function getCompanyPaydetails($companyid)
    {
        // auth_user()->deli_branch_company_id
        $attribs = CompanyAtom::where('comp_id', $companyid)
            ->select(
            'comp_id', 'login', 'pass','ttype','prodid','txncurr','clientcode','custacc','reqhashkey','resphashkey','aesreqhashkey','aesreqhashkeysalt','paymenturl','aesresphashkey', 'aesresphashkeysalt', 'paymenturl', 'enquiryurl', 'mode')
            ->first();
        $attributes=array();        
        if(isset($attribs) && ($attribs->comp_id != "") ){
           $attributes['login'] = $attribs['login'];
           $attributes['pass'] = $attribs['pass'];
           $attributes['ttype'] = $attribs['ttype'];
           $attributes['prodid'] = $attribs['prodid'];
           $attributes['txncurr'] = $attribs['txncurr'];
           $attributes['clientcode'] = $attribs['clientcode'];
           $attributes['custacc'] = $attribs['custacc'];
           $attributes['reqhashkey'] = $attribs['reqhashkey'];
           $attributes['resphashkey'] = $attribs['resphashkey'];
           $attributes['aesreqhashkey'] = $attribs['aesreqhashkey'];
           $attributes['aesreqhashkeysalt'] = $attribs['aesreqhashkeysalt'];
           $attributes['aesresphashkey'] = $attribs['aesresphashkey'];
           $attributes['aesresphashkeysalt'] = $attribs['aesresphashkeysalt'];
           $attributes['paymenturl'] = $attribs['paymenturl'];
           $attributes['enquiryurl'] = $attribs['enquiryurl'];
           $attributes['mode'] = $attribs['mode'];
        }else{
            $attributes['login'] = config('paymentgateway.Atom.login');
            $attributes['pass'] = config('paymentgateway.Atom.pass');
            $attributes['ttype'] = config('paymentgateway.Atom.ttype');
            $attributes['txncurr'] = config('paymentgateway.Atom.txncurr');
            $attributes['clientcode'] = config('paymentgateway.Atom.clientcode');
            $attributes['custacc'] = config('paymentgateway.Atom.custacc');
            $attributes['reqhashkey'] = config('paymentgateway.Atom.reqhashkey');
            $attributes['resphashkey'] =config('paymentgateway.Atom.resphashkey');
            $attributes['aesreqhashkey'] = config('paymentgateway.Atom.aesreqhashkey');
            $attributes['aesreqhashkeysalt'] =config('paymentgateway.Atom.aesreqhashkeysalt');   
            $attributes['aesresphashkey'] = config('paymentgateway.Atom.aesresphashkey');
            $attributes['aesresphashkeysalt'] =config('paymentgateway.Atom.aesresphashkeysalt');         
            $attributes['tokenurl'] = config('paymentgateway.Atom.tokenurl');
            $attributes['enquiryurl'] = config('paymentgateway.Atom.enquiryurl');
            $attributes['mode'] = config('paymentgateway.Atom.mode');
        }    

           return $attributes;
    }
   
}
