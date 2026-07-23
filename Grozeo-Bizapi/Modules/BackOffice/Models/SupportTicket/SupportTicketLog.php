<?php

namespace BackOffice\Models\SupportTicket;

use Illuminate\Database\Eloquent\Model;

class SupportTicketLog extends Model
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
    protected $table = 'support_ticket_log';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['createdOn', 'updatedOn'];
    const CREATED_AT = 'createdOn';
    const UPDATED_AT = 'updatedOn';

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticketId', 'ticketId');
    }
}
