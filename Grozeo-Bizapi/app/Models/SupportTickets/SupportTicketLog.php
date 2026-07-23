<?php

namespace App\Models\SupportTickets;

use Illuminate\Database\Eloquent\Model;
use App\Models\SupportTickets\{
    SupportUnit,
    SupportTicketStatus,
    SupportTicketStages
};

class SupportTicketLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'support_ticket_log';
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
    protected $dates = ['createdOn', 'updatedOn'];
    const CREATED_AT = 'createdOn';
    const UPDATED_AT = 'updatedOn';

    public function supportUnit()
    {
        return $this->belongsTo(SupportUnit::class, 'ticketSupportUnit', 'id');
    }
    public function supportStatus()
    {
        return $this->belongsTo(SupportTicketStatus::class, 'ticketStatus', 'id');
    }
    public function supportStage()
    {
        return $this->belongsTo(SupportTicketStages::class, 'ticketStage', 'id');
    }
}
