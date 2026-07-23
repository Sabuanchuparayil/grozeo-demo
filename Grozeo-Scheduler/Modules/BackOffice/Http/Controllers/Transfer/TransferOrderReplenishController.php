<?php

namespace BackOffice\Http\Controllers\Transfer;

use Carbon\Carbon;
use App\Models\Order;
use App\Events\OrderHistory;
use BackOffice\Models\B2bOrder;
use BackOffice\Models\BoyOrder;
use BackOffice\Models\CpdOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use BackOffice\Models\ReturnPacking;
use BackOffice\Models\TransferOrder;
use App\Http\Responses\ErrorResponse;
use BackOffice\Status\B2bOrderStatus;
use BackOffice\Models\BoyOrderRequest;
use BackOffice\Models\TransferRequest;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use BackOffice\Status\CustomerOrderStatus;
use BackOffice\Status\TransferOrderStatus;
use BackOffice\Models\TransferOrderDetails;
use BackOffice\Status\BoyOrderRequestStatus;
use BackOffice\Http\Requests\OrderAcceptRequest;
use BackOffice\Http\Resources\TransferOrderCollection;
use BackOffice\Models\FirebaseLog;

class TransferOrderReplenishController
{

    protected $boyorder;
    protected $boyorderrequest;
    protected $transferorder;
    protected $transferorderdetails;
    protected $relatedorder;
    
    protected const ACCEPTED = 1;
    protected const REJECTEDS = 3;
    protected const SUCCESS = 200;

    protected const TRANSFER_REQUEST = 0;

    protected const CUSTOMER_ORDER = 1;

    protected const B2B_ORDER = 2;

    protected const STOCK_RETURN = 3;

    public function __construct(TransferOrder $transferorder, TransferOrderDetails $transferorderdetails, BoyOrder $boyorder, BoyOrderRequest $boyorderrequest)
    {
        $this->transferorder = $transferorder;
        $this->transferorderdetails = $transferorderdetails;
        $this->boyorder = $boyorder;
        $this->boyorderrequest = $boyorderrequest;
    }

    public function __invoke(OrderAcceptRequest $request, $orderId)
    {
        
        $orderaction = $request->action;
        $orderReqId = $request->order_request_id;
        $orderpkid = $request->order_pk_id;
        $reposts =false;

        if(intval($orderReqId)==0){
            return new ErrorResponse('Invalid request Id');
        }
        $assignedorderdetails = $this->verifyTransferOrderId($orderpkid);

        if ($orderaction == 0) {
            $this->updateStatusForReplenishedOrderActions( $request->order_pk_id,  $orderReqId,$assignedorderdetails);
            FirebaseLog::where([
                ['rfir_StatusId', 1],
                ['rfir_token', $request->fcm_token]
            ])->update([
                'rfir_StatusId' => 2
            ]);
            return new SuccessResponse('Order Reracked');
        }
        

    }


    public function updateStatusForReplenishedOrderActions($orderId,  $orderReqId, $assignedorderdetails)
    {
        DB::transaction(function () use ($orderId,  $orderReqId,$assignedorderdetails) {
            BoyOrderRequest::where('id', $orderReqId)
                ->update(['status' => 5]);  //replenished

            $transorder =  TransferOrder::where('fsto_id', $orderId)
            ->select('fsto_ordertype','fstr_id')
            ->first() ;
            if(intval($orderReqId) > 0){
            TransferOrder::where('fsto_id', $orderId)                       
                    ->update(['fsto_replenished_boy' => auth_user()->id,'is_replenished' => 1,'replenishedOn' => now()]);
            }else{
                TransferOrder::where('fsto_id', $orderId)                       
                    ->update(['is_replenished' => 1,'replenishedOn' => now()]);
    }
        });
    }


    public function verifyTransferOrderId($orderId)
    {
        return TransferOrder::where([['fsto_id', '=', $orderId]])->first();
    }
   
}
