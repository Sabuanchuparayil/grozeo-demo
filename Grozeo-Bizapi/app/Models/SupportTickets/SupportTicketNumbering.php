<?php

namespace App\Models\SupportTickets;

use Illuminate\Database\Eloquent\Model;

class SupportTicketNumbering extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'support_ticketnumbering';
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
    protected $dates = ['createdDate', 'updatedDate'];
    const CREATED_AT = 'createdDate';
    const UPDATED_AT = 'updatedDate';
}
