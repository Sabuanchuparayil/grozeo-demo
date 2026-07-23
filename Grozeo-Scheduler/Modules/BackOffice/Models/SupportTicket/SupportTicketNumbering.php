<?php

namespace BackOffice\Models\SupportTicket;

use Illuminate\Database\Eloquent\Model;

class SupportTicketNumbering extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'support_ticketnumbering';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['createdDate', 'updatedDate'];
    const CREATED_AT = 'createdDate';
    const UPDATED_AT = 'updatedDate';
}
