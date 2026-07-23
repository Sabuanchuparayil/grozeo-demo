<?php

namespace BackOffice\Models;


use Illuminate\Database\Eloquent\Model;
use BackOffice\Status\BoyOrderRequestStatus;

class BoyOrderRequest extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_godown_boy_orders_request';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function getStatusDescription($id)
    {
        switch ($id) {
            case BoyOrderRequestStatus::REQUEST_SENT :
              return "REQUEST_SENT";
              break;
            case BoyOrderRequestStatus::ACCEPTED:
                return "ACCEPTED";
              break;
            case BoyOrderRequestStatus::REJECTED:
                return "REJECTED";
              break;
            case BoyOrderRequestStatus::TIMED_OUT:
                return "TIMED OUT";
                break;
            default:
              return "";
          }
    }
    
}
