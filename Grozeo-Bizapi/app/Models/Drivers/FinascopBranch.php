<?php

namespace App\Models\Drivers;

use Illuminate\Database\Eloquent\Model;

class FinascopBranch  extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_branch';
    public function deliveryRule()
    {
        return $this->hasOne(RetalineDeliveryRule::class, 'rdr_id', 'br_rdrIdExpress')->where('rdr_ruleFor', 1);
    }
    
}

