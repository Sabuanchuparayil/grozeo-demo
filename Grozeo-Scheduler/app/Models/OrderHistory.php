<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CustomerOrderStatus;

class OrderHistory extends Model
{
    /**
     * The table Associated with the Model.
     *
     * @var string
     */
    protected $table = 'retaline_customer_order_history';

   /**
     * The attribute that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function getOrderStatus()
    {
      return $this->belongsTo(CustomerOrderStatus::class, 'order_status', 'status_id');
    }

}
