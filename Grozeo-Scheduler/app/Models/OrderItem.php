<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Item;
use App\Models\Order;
use App\Models\BlockedItems;
use App\Models\StockItemImage;
use App\Models\BranchInventory;
use App\Models\StockUniqueItem;
use App\Models\OrderItemBarcodes;

class OrderItem extends Model
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retaline_customer_order_items';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'item_id';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'item_order_id', 'order_order_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_product_id', 'stit_ID');
    }

    public function image()
    {
        return $this->belongsTo(StockItemImage::class, 'item_product_id', 'product_id');
    }

    public function price()
    {
        return $this->belongsTo(BranchInventory::class, 'item_product_id', 'stit_ID');
    }

    public function blockedItems1()
    {
        return $this->hasOne(BlockedItems::class, 'order_item_id');
    }


    public function orderUniqueItem()
    {
        return $this->belongsTo(StockUniqueItem::class, 'item_group_id', 'fsi_uid');
    }
    public function barcodes()
    {
        return $this->hasMany(OrderItemBarcodes::class, 'item_id');
    }

}
