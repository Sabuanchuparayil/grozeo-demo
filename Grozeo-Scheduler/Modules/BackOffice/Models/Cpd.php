<?php

namespace BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class Cpd extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_cpd';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'cpd_id';

    public function branches()
    {
        return $this->hasMany(Branch::class, 'br_cpd');
    }

}
