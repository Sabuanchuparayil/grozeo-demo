<?php

namespace App\Models\SupportTickets;

use Illuminate\Database\Eloquent\Model;

class SupportType extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'support_type';
/**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'typeId';
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
    protected $dates = ['createdOn', 'updatedOn'];
    const CREATED_AT = 'createdOn';
    const UPDATED_AT = 'updatedOn';
}
