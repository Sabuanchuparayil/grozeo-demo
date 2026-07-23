<?php

namespace BackOffice\Http\Controllers\Boy;

use App\Models\{
    Order,
    Branch,
    OrderHistory,
    OrderRefunds,
    OrderCancelled,
    ESBlockedItems
};
use BackOffice\Models\{
    GodownBoy,
    TransferOrder
};
use BackOffice\Status\{
    TransferOrderStatus,
    CustomerOrderStatus
};
use App\Http\Responses\{
    ErrorResponse,
    SuccessResponse
};
use BackOffice\Http\Requests\{
    WalletRequest,
    BoyOrderCancelRequest
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\PostingRepository;
use BackOffice\Http\Repositories\WalletTransactionRepository;

class BoyOrderCancelController
{
    public function __invoke(BoyOrderCancelRequest $request)
    {
        try
        {
            $boy = auth_user()->id;
            $transferOrder = TransferOrder::join('retaline_godown_boy', 'branch_id', 'fsto_source')
            ->where([
                ['fsto_id', $request->order_id],
                ['retaline_godown_boy.id', $boy]
            ])
            ->whereIn('fsto_status', [
                TransferOrderStatus::CREATED,
                TransferOrderStatus::POLL_REJECTED,
                TransferOrderStatus::PICKER_APPROVED,
                TransferOrderStatus::POLL_NO_RESPONSE,
                TransferOrderStatus::GODOWN_BOY_POLLED,
                TransferOrderStatus::TO_MANUALLY_ASSIGN,
                TransferOrderStatus::ASSIGNED_GODOWN_BOY
            ])->first();
            if($transferOrder)
            {
                $this->cancelTransferOrder($transferOrder->fsto_id);
                $order = Order::where('order_id', $transferOrder->fstr_id)->first();
                if($order)
                {
                    $this->cancelCustomerOrder($order);
                    $this->addOrderHistory($order, $request->reason, $boy);
                    $this->orderRefundUpdate($order, $request->reason);
                    // $this->updateWalletAmount($order, $request->reason);
                    $fcmID = auth_user()->fcm_id;
                    DB::statement("UPDATE retaline_firebase_log SET rfir_StatusId = 2 where rfir_StatusId = 1 AND rfir_token = '{$fcmID}'");

                    $postReq = new Request();
                    $postReq->setMethod('POST');
                    $postReq->request->add([
                        'order_id'              => $order->order_id,
                        'finascopEventRefId'    => config("event_master.cancellation"),
                        'storegroup_id'         => (@$order->storegroup_id ? $order->storegroup_id : 0)
                    ]);
                    (new PostingRepository)->finascopPosting($postReq);

                    return new SuccessResponse("Order Cancelled");
                }
            }
            return new ErrorResponse("Unable to cancel order");
        }
        catch (\Exception $e)
        {
            // info("BoyOrderCancelController--Error");info($e);
            return new ErrorResponse($e->getMessage()); 
        }
    }
    private function cancelCustomerOrder($order)
    {
        $ord = Order::where('order_id', $order->order_id)->update([
            'status_id' => CustomerOrderStatus::CANCELLED
        ]);
        $blocked = ESBlockedItems::where('order_id', $order->order_id)->delete();

    }
    private function cancelTransferOrder($fstoID)
    {
        $transferOrder = TransferOrder::where('fsto_id', $fstoID)->update([
            'fsto_status'   => TransferOrderStatus::CANCELLED,
            'fsto_updateby' => 1
        ]);
    }
    private function addOrderHistory($order, $reason, $boyID)
    {
        $history = OrderHistory::create([
            'order_id'      => $order->order_id,
            'order_action'  => "Order Cancelled by merchant - {$reason}",
            'order_status'  => CustomerOrderStatus::CANCELLED
        ]);
        $cancelled = OrderCancelled::create([
            "customer_id"       => $order->order_customer_id,
            "order_id"          => $order->order_id,
            "reason"            => "Order Cancelled by boy - {$reason}",
            "cancelled_by_type" => 4,
            "cancelled_by_id"   => $boyID
        ]);
    }
    private function orderRefundUpdate($order, $reason)
    {
        if($order->payment_mode == 2 || $order->payment_mode == 5)
        {
            
            $amount = ($order->payment_mode == 2) ? $order->total : $order->order_wallet_amount;
            OrderRefunds::create([
                "order_id"          => $order->order_id,
                "payment_gateway"   => $order->order_payment_gateway,
                "amount"            => $amount
            ]);
            $order->status_id = CustomerOrderStatus::REFUND_INITIATED;
            $order->save();
            OrderHistory::create([
                'order_id'      => $order->order_id,
                'order_action'  => "Refund Initiated by Grozeo",
                'order_status'  => CustomerOrderStatus::REFUND_INITIATED
            ]);
        }
        if($order->payment_mode == 3 || $order->payment_mode == 4 || $order->payment_mode == 5)
        {
            $this->updateWalletAmount($order, $reason);
        }
    }
    private function updateWalletAmount($order, $reason)
    {
        if(!in_array($order->payment_mode, [1, 6, 7]))
        {
            $branch = Branch::where('br_ID', $order->order_branch_id)->first();
            $amount = 0;
            switch ($order->payment_mode)
            {
                case 2:
                    $amount = $order->total;
                    break;
                case 3:
                    $amount = $order->total;
                    break;
                case 4:
                    $amount = $order->order_wallet_amount;
                    break;
                case 5:
                    $amount = $order->order_wallet_amount;
                    break;
            }
            $storeGroupName = @$branch->storegroup->store_group_name ?? "";
            $walletReq = [
                'customer_id'   => $order->order_customer_id,
                'order_id'      => $order->order_id,
                'source_type'   => 1,
                'amount'        => $amount,
                'information'   => "Order {$order->order_order_id} from {$storeGroupName} has been cancelled",
                'barcode'       => 0
            ];
            $response = (new WalletTransactionRepository)->createWalletEntry($walletReq);
        }
    }
}
