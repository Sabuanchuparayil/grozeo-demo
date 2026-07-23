<?php

namespace App\Models\SupportTickets;

use Illuminate\Database\Eloquent\Model;

class SupportUnit extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'support_unit';
/**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_on', 'updated_on'];
    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'updated_on';
}
