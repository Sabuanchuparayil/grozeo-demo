<?php

namespace BackOffice\Http\Controllers\Boy;


use App\Http\Responses\{
    ErrorResponse,
    SuccessResponse,
    SuccessWithData
};
use BackOffice\Status\{
    B2bOrderStatus,
    CpdOrderStatus,
    CustomerOrderStatus,
    TransferOrderStatus
};
use BackOffice\Models\{
    B2bOrder,
    BoyOrder,
    CpdOrder,
    GodownBoy,
    TransferOrder,
    BoyOrderRequest
};
use App\Models\Order;
use App\Events\OrderHistory;
use Illuminate\Support\Facades\DB;
use BackOffice\Http\Requests\OrderAssignRequest;


class BoyOrderAssignController
{
    
    protected $order, $boyOrderRequest;
    
    protected const REQUEST_SENT = 1;

    public function __construct(CpdOrder $order, BoyOrderRequest $boyOrderRequest)
    {
        $this->order = $order;
        $this->boyOrderRequest = $boyOrderRequest;
    }

    public function __invoke(OrderAssignRequest $request)
    {
        try
        {
            $boyID = auth_user()->id;
            $transferOrder = TransferOrder::join('retaline_godown_boy', 'branch_id', 'fsto_source')
            ->where([
                ['fsto_id', $request->order_pk_id],
                ['retaline_godown_boy.id', $boyID]
            ])
            ->whereIn('fsto_status', [TransferOrderStatus::ASSIGNED_GODOWN_BOY, TransferOrderStatus::PICKER_APPROVED, TransferOrderStatus::INCOMPLETE_ORDER])
            ->first();
            if(@$transferOrder == NULL)
            {
                return new ErrorResponse("Operation failed");
            }
            $orderRequest = null;
            $branchId =  $transferOrder->fsto_source;
            if((@$transferOrder->fsto_assigned_boy > 0) && (@$transferOrder->fsto_status == TransferOrderStatus::PICKER_APPROVED))
            {
                if(@$transferOrder->fsto_assigned_boy != $boyID)
                {
                    $boy = GodownBoy::find($transferOrder->fsto_assigned_boy);
                    $name = @$boy->name." ".@$boy->lname;
                    return new ErrorResponse("Order already been assigned to {$name}");
                }
                $boyOrder = BoyOrder::where([
                    "order_id"      => $transferOrder->fsto_uid,
                    "boy_id"        => $boyID,
                    "branch_id"     => $transferOrder->fsto_source,
                    "order_pk_id"   => $request->order_pk_id
                ])->first();
                return new SuccessWithData(['boy_order_id'  => @$boyOrder->id]);
            }

            $orderRequest = $this->boyOrderRequest->create([
                'boy_id'        => $boyID,
                'branch_id'     => $transferOrder->fsto_source,
                'order_id'      => $transferOrder->fsto_uid,
                'status'        => static::REQUEST_SENT,
                'order_pk_id'   => $request->order_pk_id
            ]);

            $boyOrder = BoyOrder::create([
                'boy_id'        => $boyID,       
                'order_id'      => $transferOrder->fsto_uid,
                'accepted_time' => now(),
                'branch_id'     => $transferOrder->fsto_source,
                'bgor_id'       => @$orderRequest->id,     
                'order_pk_id'   => $request->order_pk_id,
                'status'        => static::REQUEST_SENT, 
            ]);
            auth_user()->update(['has_open_orders' => 1]);
            $this->updateOrder($request, $boyID, $transferOrder->fstr_id);
            return new SuccessWithData(['boy_order_id'  => @$boyOrder->id]);
        }
        catch (\Exception $e)
        {
            // info("BoyOrderAssignController ERROR---");info($e);
            return new ErrorResponse("Operation failed"); 
        }
    }

    /**
     * Update the order status.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function updateOrder($request,$order_polled_boy, $orderID)
    {
		if($request->is_cpd == 1)
        {
            $model = new CpdOrder;
            $orderField = 'order_no';
            $statusField = 'order_status';
            $status = CpdOrderStatus::GODOWN_BOY_POLLED;
            $polledboyField = 'order_polled_boy';
        }
        else
        {
            if($request->is_b2border == 1)
            {
                $model = new B2bOrder;
                $orderField = 'bbso_SONumber';
                $statusField = 'status_id';
                $status = B2bOrderStatus::GODOWN_BOY_POLLED;
                $polledboyField = 'bbso_polled_boy';
            }
            else
            {
                TransferOrder::where('fsto_id', $request->order_pk_id)->update([
                    'fsto_status'       => TransferOrderStatus::PICKER_APPROVED,
                    'fsto_assigned_boy' => $order_polled_boy
                ]);
                Order::where('order_id', $orderID)->update([
                    'status_id'             => CustomerOrderStatus::ASSIGNED_GODOWN_BOY,
                    'order_assigned_boy'    => $order_polled_boy
                ]);
                event(new OrderHistory($orderID, CustomerOrderStatus::ASSIGNED_GODOWN_BOY));
            }
        }
        if(@$model)
        {
            $model->where($orderField, $request->order_pk_id)->update([
                $statusField    => $status,
                $polledboyField => $order_polled_boy
            ]);
        }
    }
}
