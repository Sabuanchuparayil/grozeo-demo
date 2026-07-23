<?php

namespace BackOffice\Models;

use App\Models\StockItemImage;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetails extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'B2CInvoiceDetails';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';


    
   
}
