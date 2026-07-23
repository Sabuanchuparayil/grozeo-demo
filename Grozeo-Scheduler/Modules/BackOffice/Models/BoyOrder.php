<?php

namespace BackOffice\Models;

use BackOffice\Status\BoyOrderStatus;
use Illuminate\Database\Eloquent\Model;

class BoyOrder extends Model
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
    protected $table = 'retaline_godown_boy_orders';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    public static function getStatusDescription($id)
    {
        switch ($id) {
            case BoyOrderStatus::ACCEPTED :
              return "ACCEPTED";
              break;
            case BoyOrderStatus::SCANNING_STARTED:
                return "SCANNING_STARTED";
              break;
            case BoyOrderStatus::INCOMPLETE_ORDER:
                return "INCOMPLETE_ORDER";
              break;
            case BoyOrderStatus::COMPLETED:
                return "COMPLETED";
              break;              
            case BoyOrderStatus::REVOKED:
                return "REVOKED";
                break;
            default:
              "";
          }
    }
}
