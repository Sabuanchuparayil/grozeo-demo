<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\OrderAddress;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Drivers\QugeoOrder;
use BackOffice\Models\TransferOrder;
use BackOffice\Models\SalesOrder;
use App\Models\CourierDelivery\ShippingConsignment;
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
        return $this->hasMany('App\Models\OrderItem', 'customer_order_id', 'order_id');
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
        return $this->hasMany('App\Models\OrderItem', 'customer_order_id');
    }

    public function orderStatus()
    {
        return $this->belongsTo('App\Models\CustomerOrderStatus', 'status_id', 'status_id');
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('d-m-Y H:i:s');
    }
    public function orderHistory()
    {
        return $this->hasMany('App\Models\OrderHistory', 'order_id', 'order_id');
    }
    public function branchDetails()
    {
        return $this->belongsTo(Branch::class, 'order_branch_id', 'br_ID');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'order_customer_id', 'cust_id');
    }
    public function salesOrder()
    {
        return $this->hasOne(SalesOrder::class, 'customer_order_id', 'order_id');
    }
    public function shipment()
    {
        return $this->belongsTo(ShippingConsignment::class, 'order_order_id', 'order_id');
    }
    public function deliveryRule()
    {
        return $this->belongsTo(DeliveryRules::class, 'delivery_rule_id', 'rdr_id');
    }
    public function drive()
    {
        return $this->belongsTo(QugeoOrder::class, 'order_order_id', 'quor_RefNo');
    }
    public function packing()
    {
        return $this->belongsTo(TransferOrder::class, 'order_id', 'fstr_id');
    }
}