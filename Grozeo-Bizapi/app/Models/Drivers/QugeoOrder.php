<?php
namespace App\Models\Drivers;

use App\Models\{
    Order,
    Branch
};
use Illuminate\Database\Eloquent\Model;
use App\Models\Drivers\QugeoDeliveryStatus;

class QugeoOrder extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qugeo_order';
    protected $primaryKey = 'quor_id';
    public $timestamps = false;
    protected $fillable=['quor_Status','quor_QugeoPickupDDBOrderId','quor_QugeoPickupDDBDriverId','quor_SchedulePickupTime','quor_PickupDriverId','quor_PickedupTime','quor_signature','quor_image'];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'quor_Pickupbr_id', 'br_ID');
    }
    public function finascopBranch()
    {
        return $this->belongsTo(Branch::class, 'quor_Pickupbr_id', 'br_ID');
    }
    public function finascopDeliveryBranch()
    {
        return $this->belongsTo(Branch::class, 'quor_Deliverybr_id', 'br_ID');
    }
    public function deliveryStatus()
    {
        return $this->belongsTo(QugeoDeliveryStatus::class, 'quor_Status', 'dls_ID');
    }
    public function details()
    {
        return $this->hasOne(QugeoOrderDetail::class, 'quor_id')
        ->select(
            'quod_id as detid',
            'quor_id',
            'quor_RefNo as RefNo',
            'quor_IsBarcode as Barcode'
        );
    }
    public function order()
    {
        return $this->belongsTo(Order::class, 'quor_RefNo', 'order_order_id');
    }
}

