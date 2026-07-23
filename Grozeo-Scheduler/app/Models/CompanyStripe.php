<?php

namespace App\Models;

use App\Models\CompanyStripe;
use Illuminate\Database\Eloquent\Model;

class CompanyStripe extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_company_stripe';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function getCompanyPaydetails($companyid)
    {
        // auth_user()->deli_branch_company_id
        $attribs = CompanyStripe::where('comp_id', $companyid)
            ->select(
            'comp_id', 'phishable_key', 'secret_key','currency','webhook_key')
            ->first();
        $attributes=array();
        if(isset($attribs) && ($attribs->comp_id != "") && $attribs->comp_id > 0 )
        {
            $attributes['phishable_key'] = $attribs['phishable_key'];
            $attributes['secret_key'] = $attribs['secret_key'];
            $attributes['currency'] = $attribs['currency'];
            $attributes['webhook_key'] = $attribs['webhook_key'];
            $attributes['key_id'] = $attribs['phishable_key'];
        }
        else
        {
            $attributes['phishable_key'] = config('phishable_key');
            $attributes['secret_key'] = config('secret_key');
            $attributes['currency'] = config('currency');
            $attributes['webhook_key'] = config('webhook_key');
            $attributes['key_id'] = config('phishable_key');

        }
        return $attributes;
    }
   
}
