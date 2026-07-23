<?php

namespace BackOffice\Models\SupportTicket;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['ticketId'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'support_ticket';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['createdOn', 'updatedOn'];
    const CREATED_AT = 'createdOn';
    const UPDATED_AT = 'updatedOn';

    public function supportType()
    {
        return $this->belongsTo(SupportTicketType::class, 'ticketSupTypeId', 'typeId');
    }
    public function logs()
    {
        return $this->hasMany(SupportTicketLog::class, 'ticketId', 'ticketId');
    }
}
