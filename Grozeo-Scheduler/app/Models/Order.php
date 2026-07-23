<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\OrderAddress;
use App\Models\Branch;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\CustomerOrderStatus;
use App\Models\OrderHistory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_customer_order';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'order_id';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    //public $incrementing = false;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'customer_order_id', 'order_id');
    }

    public function status()
    {
        return $this->hasOne(OrderStatus::class, 'customer_order_id');
    }

    public function deliveryAddress()
    {
        return $this->hasOne(OrderAddress::class, 'customer_order_id');
    }

    public function productItem()
    {
        return $this->hasMany(OrderItem::class, 'customer_order_id');
    }

    public function orderStatus()
    {
        return $this->belongsTo(CustomerOrderStatus::class, 'status_id', 'status_id');
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('d-m-Y H:i:s');
    }
    public function orderHistory()
    {
        return $this->hasMany(OrderHistory::class, 'order_id', 'order_id');
    }
    public function branchDetails()
    {
        return $this->belongsTo(Branch::class, 'order_branch_id', 'br_ID');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'order_customer_id', 'cust_id');
    }





}
