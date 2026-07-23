<?php

namespace App\Models\SupportTickets;

use App\Models\SupportTickets\{
    SupportUnit,
    SupportType,
    SupportTicketLog,
    SupportTicketStatus,
    SupportTicketStages
};

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'support_ticket';
/**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ticketId';
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
        return $this->belongsTo(SupportUnit::class, 'ticketSuId', 'id');
    }
    public function supportType()
    {
        return $this->belongsTo(SupportType::class, 'ticketSupTypeId', 'typeId');
    }
    public function supportStatus()
    {
        return $this->belongsTo(SupportTicketStatus::class, 'ticketStatus', 'id');
    }
    public function supportStage()
    {
        return $this->belongsTo(SupportTicketStages::class, 'ticketStage', 'id');
    }
    public function logs()
    {
        return $this->hasMany(SupportTicketLog::class, 'ticketId', 'ticketId');
    }
}
