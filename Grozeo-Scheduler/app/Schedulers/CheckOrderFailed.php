<?php
namespace App\Schedulers;

use App\Models\{
    Order,
    Customer,
    ProcessLock,
    OrderHistory,
    OrderAddress,
    WalletTransaction,
    Supports\OutboundJobs,
    Supports\SupportUserEvents
};
use App\Sms\SmsSender;
use App\Modules\BlockedProducts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Status\CustomerOrderStatus;
use App\Http\Services\B2CtoSalesOrder;
use App\Http\Repositories\Payment\PaymentRepository;

class CheckOrderFailed
{
    public function __invoke()
    {
        try
        {
            $timer = config('app.order_failed_timer') ?? 300;
            $orders = Order::select('order_id', 'order_group_id', 'order_payment_initiate_time', 'order_order_id', 'order_customer_id', 'storegroup_id', 'order_branch_id', 'total', 'order_wallet_amount', 'order_payment_gateway')
            ->where([
                ['status_id', 21],
                [DB::raw('TIMESTAMPDIFF(SECOND, order_payment_initiate_time, NOW())'), '>', $timer]
            ])
            ->whereIn('payment_mode', [2, 5])
            ->get();
            if($orders)
            {
                foreach ($orders as $order)
                {
                    $hasRestaurant = array_column($order->orderItems->toArray(), 'is_restaurant');
                    if(in_array(1, $hasRestaurant))
                    {
                        $this->markOrderFailed($order);
                    }
                    else
                    {
                        $pgClass = config("paymentgateway.{$order->order_payment_gateway}.class");
                        $pgObj = new $pgClass();

                        $pgResponse = $pgObj->checkScheduledPaymentStatus($order->order_group_id, "payment");
                        if($pgResponse)
                        {
                            $this->paymentFinalize($order, $pgResponse);
                        }
                        else
                        {
                            $this->markOrderFailed($order);
                        }
                    }
                }
            }
            ProcessLock::updateColData("BizAPI_CheckOrderFailed", 1);
        }
        catch (\Exception $e)
        {
            info("CheckOrderFailed SCHEDULER => {$e->getMessage()}");
            info($e);
            ProcessLock::updateColData("BizAPI_CheckOrderFailed", 0);
        }
    }

    private function markOrderFailed($order)
    {
        $this->updateOrderStatus($order->order_id, CustomerOrderStatus::PAYMENT_FAILED);
        $this->updateOrderHistory($order->order_id, CustomerOrderStatus::PAYMENT_FAILED, "Payment marked as failed");
        $this->checkUpdateJobs($order);
        $alreadyNotified = OutboundJobs::where([
            ['calleeId', $order->order_customer_id],
            ['calleeType', 2],
            ['eventId', 5],
            ['orderRefrenceId', $order->order_order_id]
        ])->exists();
        $customerMobile = OrderAddress::where('customer_order_id', $order->order_id)->value('order_contact_no');
        if(@$customerMobile && !$alreadyNotified)
        {
            app(SmsSender::class)->fetchContentSendSms([], $customerMobile, 5);
        }
    }

    private function paymentFinalize($order, $response)
    {
        $custID = $order->order_customer_id;
        $branchType = $order->order_branch_type_id;
        $storegroupID = $order->storegroup_id;
        $ordStatus = CustomerOrderStatus::SUCCESS;
        $paymentMode = $order->payment_mode;

        BlockedProducts::markedForDelivery($order->order_id, $custID);
        $this->updateOrderDetails($order, $response);

        $customerMobile = Customer::where('cust_id', $order->order_customer_id)->value('cust_mobile');
        if($customerMobile)
        {
            $templateData = [
                'amount'     => $order->total
            ];
            app(SmsSender::class)->fetchContentSendSms($templateData, $customerMobile, 4);
        }
    }
    private function updateOrderStatus($order_id, $status)
    {
        $updateOrder = Order::where('order_id', $order_id)->update([
            "status_id"     => $status,
            "updated_at"    => now()
        ]);
    }
    private function updateOrderHistory($order_id, $status, $msg = "")
    {
        $historyUpdate = OrderHistory::create([
            "order_id"      => $order_id,
            "order_action"  => $msg,
            "order_status"  => $status,
            "created_at"    => now(),
            "updated_at"    => now()
        ]);
    }
    private function updateOrderDetails($order, $response)
    {
        $orderUpdate = Order::where('order_id', $order->order_id)->update([
            "status_id"                         => CustomerOrderStatus::SUCCESS,
            "order_payment_response_received"   => 1,
            "order_payment_status"              => "Success",
            "order_payment_gateway_refid"       => $response,
            "order_payment_gateway_refid_crc32" => crc32($response),
            "order_customer_cancel_till"        => PaymentRepository::getAfterBookingDelayTime(time(), 1),
            "order_delivery_start_at"           => PaymentRepository::getAfterBookingDelayTime(time(), 0),
            "order_payment_initiate_time"       => date('Y-m-d H:i:s')
        ]);
        try {
            B2CtoSalesOrder::salesOrders($order->order_id);
        } catch (\Exception $e) {
            Log::error("CheckOrderFailed: B2CtoSalesOrder failed for order {$order->order_id}: " . $e->getMessage(), ['exception' => $e]);
        }
        $this->updateOrderHistory($order->order_id, CustomerOrderStatus::SUCCESS, "");
    }
    private function checkUpdateJobs($order)
    {
        $isJob = OutboundJobs::where([
            ['calleeId', $order->order_customer_id],
            ['calleeType', 2],
            ['eventId', 13],
            ['orderRefrenceId', $order->order_order_id]
        ])->count();
        $custDetails = Customer::where('cust_id', $order->order_customer_id)->first();
        if($isJob == 0)
        {
            $supportEvent = SupportUserEvents::where('id', 13)->first();
            
            if($custDetails)
            {
                $addOutboundJob = OutboundJobs::create([
                    "eventId"           => $supportEvent->id,
                    "jobTitle"          => "{$supportEvent->eventName} - {$custDetails->cust_customer_name}",
                    "calleeId"          => $order->order_customer_id,
                    "calleeName"        => $custDetails->cust_customer_name,
                    "calleeMobile"      => $custDetails->cust_mobile,
                    "calleeType"        => 1,
                    "orderRefrenceId"   => $order->order_order_id,
                    "eventRank"         => $supportEvent->rank,
                    "status"            => 1
                ]);
            }
        }
    }
}