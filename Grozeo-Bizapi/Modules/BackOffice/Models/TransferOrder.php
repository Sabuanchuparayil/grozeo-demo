<?php

namespace BackOffice\Models;

use App\Models\{
    Order,
    OrderStatus,
    TransferOrderNumbering
};
use BackOffice\Status\{
    B2bOrderStatus,
    CustomerOrderStatus,
    TransferOrderStatus,
    TransferRequestStatus
};
use BackOffice\Models\{
    BoyOrder,
    B2bOrder,
    GodownBoy,
    BoyOrderRequest,
    TransferOrderPackDetails,
    TransferOrderStatus as TOSModel
};
use Illuminate\Database\Eloquent\Model;

class TransferOrder extends Model
{
    protected const TRANSFER_REQUEST = 0;

    protected const CUSTOMER_ORDER = 1;

    protected const B2B_ORDER = 2;

    protected const STOCK_RETURN = 3;

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
    protected $table = 'finascop_stock_transfer_order';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'fsto_id';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'fsto_createdOn';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'fsto_updateon';


    public static function  nextTransferOrderNo($brid)
    {
        $branches = Branch::select('branch_shortname')
        ->where('br_id', $brid)
        ->first();
        $numbering = TransferOrderNumbering::create();
        $lastNo = $numbering ? $numbering->id : 1;
        return $branches->branch_shortname.'-TOR-'.now()->format('ym').'-'.str_pad($lastNo, 3, '0', STR_PAD_LEFT);
    }
    private static function reverseOrderStatusMapping($ordertype,$status)
    {
        if($ordertype === static::TRANSFER_REQUEST){
            switch ($status) {
                case TransferOrderStatus::GODOWN_BOY_POLLED :
                  return TransferRequestStatus::ORDER_PICKER_POLLED ;
                  break;
                case TransferOrderStatus::POLL_NO_RESPONSE:
                    return TransferRequestStatus::POLL_NO_RESPONSE ;
                  break;
                case TransferOrderStatus::ASSIGNED_GODOWN_BOY:
                    return TransferRequestStatus::ASSIGNED_ORDER_PICKER ;
                  break;
                case TransferOrderStatus::TO_MANUALLY_ASSIGN:
                    return TransferRequestStatus::MANUALLY_ASSIGNED_ORDER_PICKER ;
                    break;
                case TransferOrderStatus::POLL_REJECTED:
                    return TransferRequestStatus::POLL_REJECTED ;
                    break;
                case TransferOrderStatus::COMPLETED:
                    return TransferRequestStatus::TRANSFER_ORDER_COMPLETED ;
                    break;    
                default:
                  return -1;
              }
        }else{
            if($ordertype === static::CUSTOMER_ORDER){
                switch ($status) {
                    case TransferOrderStatus::GODOWN_BOY_POLLED :
                      return CustomerOrderStatus::GODOWN_BOY_POLLED ;
                      break;
                    case TransferOrderStatus::POLL_NO_RESPONSE:
                        return CustomerOrderStatus::GODOWN_BOY_POLLED ;
                      break;
                    case TransferOrderStatus::ASSIGNED_GODOWN_BOY:
                        return CustomerOrderStatus::ASSIGNED_GODOWN_BOY ;
                      break;
                    case TransferOrderStatus::TO_MANUALLY_ASSIGN:
                        return CustomerOrderStatus::MANUAL_ASSIGNMENT ;
                        break;
                    case TransferOrderStatus::POLL_REJECTED:
                        return CustomerOrderStatus::GODOWN_BOY_POLLED ;
                        break;
                    case TransferOrderStatus::COMPLETED:
                        return CustomerOrderStatus::READY_FOR_DELIVERY ;
                        break;                          
                    default:
                      return -1;
                  }
            }elseif($ordertype === static::B2B_ORDER){
                switch ($status) {
                    case TransferOrderStatus::GODOWN_BOY_POLLED :
                      return B2bOrderStatus::ORDER_PICKER_POLLED ;
                      break;
                    case TransferOrderStatus::POLL_NO_RESPONSE:
                        return B2bOrderStatus::POLL_NO_RESPONSE ;
                      break;
                    case TransferOrderStatus::ASSIGNED_GODOWN_BOY:
                        return B2bOrderStatus::ASSIGNED_ORDER_PICKER ;
                      break;
                    case TransferOrderStatus::TO_MANUALLY_ASSIGN:
                        return B2bOrderStatus::MANUALLY_ASSIGNED_ORDER_PICKER ;
                        break;
                    case TransferOrderStatus::POLL_REJECTED:
                        return B2bOrderStatus::POLL_REJECTED ;
                        break;
                    case TransferOrderStatus::COMPLETED:
                        return B2bOrderStatus::READY_FOR_DELIVERY ;
                        break;                            
                    default:
                      return -1;
                  }

            }elseif($ordertype === static::STOCK_RETURN){
                switch ($status) {
                    case TransferOrderStatus::GODOWN_BOY_POLLED :
                      return 1 ;
                      break;
                    case TransferOrderStatus::POLL_NO_RESPONSE:
                        return 1 ;
                      break;
                    case TransferOrderStatus::ASSIGNED_GODOWN_BOY:
                        return 1 ;
                      break;
                    case TransferOrderStatus::TO_MANUALLY_ASSIGN:
                        return 1 ;
                        break;
                    case TransferOrderStatus::POLL_REJECTED:
                        return 1 ;
                        break;
                    case TransferOrderStatus::COMPLETED:
                        return 1 ;
                        break;                            
                    default:
                      return -1;
                  }

            }

        }
    }
    public static function reverseStatusUpdate($transferOrderId,$newStatus)
    {
        $transferorders = TransferOrder::select('fstr_id','fsto_ordertype')
        ->where('fsto_id', $transferOrderId)
        ->first();
      
        if($transferorders->fsto_ordertype === static::TRANSFER_REQUEST){
            $order = new TransferRequest;           
            $orderField = 'fstr_id';
            $statusField = 'fstr_status';
            $status= TransferOrder::reverseOrderStatusMapping(static::TRANSFER_REQUEST,$newStatus);
       }else{
           if($transferorders->fsto_ordertype === static::CUSTOMER_ORDER){
            $order =new Order;
            $orderField = 'order_id';
            $statusField = 'status_id';
            $status= TransferOrder::reverseOrderStatusMapping(static::CUSTOMER_ORDER,$newStatus);
           }elseif($transferorders->fsto_ordertype === static::B2B_ORDER){
            $order =new B2bOrder;
            $orderField = 'bbso_id';
            $statusField = 'status_id';
            $status= TransferOrder::reverseOrderStatusMapping(static::B2B_ORDER,$newStatus);
           }elseif($transferorders->fsto_ordertype === static::STOCK_RETURN){
            $order =new ReturnPacking;
            $orderField = 'frrp_id';
            $statusField = 'frrp_status';
            $status= TransferOrder::reverseOrderStatusMapping(static::STOCK_RETURN,$newStatus);
           }       
       } 

       $order->where($orderField, $transferorders->fstr_id)->update([$statusField => $status]);
    }


    public function transferorderDetails()
    {
        return $this->hasMany(TransferOrderDetails::class, 'fsto_id');
    }
    public function packedtransferorderDetails()
    {
        return $this->transferorderDetails()->where('fsto_pkdQty','>',0);
    }
    public function fstosStatus()
    {
        return $this->belongsTo(TOSModel::class, 'fsto_status', 'fstos_id');
    }
    public function boy()
    {
        return $this->belongsTo(GodownBoy::class, 'fsto_assigned_boy', 'id');
    }
    public function boyOrder()
    {
        return $this->belongsTo(BoyOrder::class, 'fsto_id', 'order_pk_id');
    }
    public function boyOrderRequest()
    {
        return $this->belongsTo(BoyOrderRequest::class, 'fsto_id', 'order_pk_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class, 'fstr_id', 'order_id');
    }
    public function packDetails()
    {
        return $this->hasMany(TransferOrderPackDetails::class, 'rtopd_fstoId', 'fsto_id');
    }
}
