<?php
namespace BackOffice\Models;

use BackOffice\Models\PackageMaster;
use Illuminate\Database\Eloquent\Model;

class TransferOrderPackDetails extends Model
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
    protected $table = 'retaline_transfer_order_pack_details';

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
    protected $primaryKey = 'rtopd_id';

    public function package()
    {
        return $this->belongsTo(PackageMaster::class, 'rtopd_packaging', 'rpckm_id');
    }
}
