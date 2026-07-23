<?php

namespace BackOffice\Models\SupportTicket;

use Illuminate\Database\Eloquent\Model;

class SupportTicketType extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['typeId'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'support_type';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['createdOn', 'updatedOn'];
    const CREATED_AT = 'createdOn';
    const UPDATED_AT = 'updatedOn';
}
