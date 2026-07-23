<?php

namespace BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class GodownBoy extends Model
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
    protected $table = 'retaline_godown_boy';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    public function withOutTimestamp()
    {
        
    }

}
