<?php

namespace App\Models\SupportTickets;

use Illuminate\Database\Eloquent\Model;

class SupportTicketStages extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'support_ticket_stages';
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
}
