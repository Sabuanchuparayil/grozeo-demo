<?php

namespace App\Schedulers;

use App\Models\{
    Order,
    Branch,
    Customer,
    OrderItem,
    ContractPOProducts,
    Vendor,
    UploadPrescription,
    ProcessLock
};
use App\Events\OrderHistory;
use App\Status\CustomerOrderStatus;
use App\Http\Services\B2CToTransferOrder;
use App\Http\Repositories\Payment\PaymentRepository;
use Illuminate\Support\Facades\DB;

class OrderStatusUpdate {

    public function __invoke() {
        try
        {
            $this->successOrders = $this->getSuccessOrders();
            $this->updateSuccessOrders();
            ProcessLock::updateColData("BizAPI_OrderStatusUpdate", 0);
        }
        catch (\Exception $e)
        {
            info("OrderStatusUpdate SCHEDULER => {$e->getMessage()}");
            ProcessLock::updateColData("BizAPI_OrderStatusUpdate", 0);
        }
    }

    protected function getSuccessOrders() {
        $data = Order::where('status_id', CustomerOrderStatus::SUCCESS)
                ->where('order_customer_cancel_till', '<=', now()->format('Y-m-d H:i:s'))
                ->get();
        return $data;
    }

    protected function updateSuccessOrders() {
        foreach ($this->successOrders as $order) {

            $order_status = null;
            $UploadPrescription = UploadPrescription::where('order_id', $order->order_id)->count();
            if ($UploadPrescription > 0) {
                $order_status = CustomerOrderStatus::ON_HOLD;
            }

            $hasRCItem = DB::table('retaline_customer_order_items')
                    ->where('customer_order_id', $order->order_id)
                    ->where('order_branch_id', '!=', $order->order_branch_id)
                    ->count();

            if ($hasRCItem > 0)
                $order_status = CustomerOrderStatus::CPR_ON_HOLD;


            $asctedbrach_cpr = 0;
            if ($order->order_branch_type_id == 2) {
                $orderdets = OrderItem::where('customer_order_id', $order->order_id)
                        ->select('item_product_id', 'item_order_qty', 'item_retail_price', 'item_sales_price', 'item_order_qty', 'item_cgst', 'item_sgst', 'item_amount', 'item_price', 'item_kfc')
                        ->get();

                foreach ($orderdets as $orderdet) {
                    $itemcprdets = ContractPOProducts::where('fcpod_itemid', $orderdet->item_product_id)
                            ->select('fcpod_vendorid', 'fcpo_validDate')
                            ->first();
                    $count = Vendor::where('stpa_id', $itemcprdets->fcpod_vendorid)
                            ->where('deliverMode_cpr', 2)
                            ->count();
                    if ($count > 0) {
                        $asctedbrach_cpr++;
                    }
                }
            }
            $br_details = Branch::where('br_ID', $order->order_branch_id)->first();
            $this->updatePlacedOrderStatus($order->order_group_id, $order->order_id, $order_status);

            if ($order_status !== null) {
                Order::where('order_id', $order->order_id)->update(['status_id' => $order_status]);
                event(new OrderHistory($order->order_id, $order_status));
            }

            $hasTransferOrder = DB::table('finascop_stock_transfer_order')
                ->where('fstr_id', $order->order_id)
                ->exists();

            if (($UploadPrescription == 0) && ($asctedbrach_cpr == 0) && ($br_details->br_type == 0) && !$hasTransferOrder) {
               // if ($podToOnline != 1) {
                    try {
                        B2CToTransferOrder::transferOrders($order->order_id);
                    } catch (\Exception $e) {

                    }
                    try {
                      //OrderFinascop::OrderVoucher($order->order_id);
                        //StoreFinascop::store(config('paymentgateway.default'), $customer_id, $order->order_id);
                    } catch (\Exception $e) {

                    }
               // }
            }


        }
    }

    private function updatePlacedOrderStatus($order_group_id, $order_id, $order_status = null) {

        $orderDet = Order::where('order_group_id', $order_group_id)->where('order_id', $order_id)
                ->select('order_id', 'order_branch_id', 'order_branch_type_id', 'status_id', 'order_cutoff_time', 'order_slot_id', 'order_slot_date')
                ->first();

        $br_schedulePackiing = Branch::where('br_ID', $orderDet->order_branch_id)
                ->first();
        if ($br_schedulePackiing->br_type == 1) {
            if ($orderDet->order_slot_id > 0) {
                $slot = DB::select('SELECT rbds_time_from, rbds_time_to FROM retaline_branch_delivery_slot WHERE rbds_id= ' . $orderDet->order_slot_id);
                $default_br_schedulePackiing = DB::select("SELECT cfg_Value, cfg_Type FROM sys_configuration WHERE cfg_Name= 'DEFAULT_SCHEDULE_PACKING' ");
                $date = date('Y-m-d', strtotime($orderDet->order_slot_date));
                if ($br_schedulePackiing->br_schedulePackiing > 0) {
                    $time = date('H:i:s', strtotime('-' . $br_schedulePackiing->br_schedulePackiing . ' hours', strtotime($slot[0]->rbds_time_from)));
                } else {
                    $time = date('H:i:s', strtotime('-' . $default_br_schedulePackiing[0]->cfg_Value . ' hours', strtotime($slot[0]->rbds_time_from)));
                }

                $order_delivered_date = $date . ' ' . $time;
                $data['order_delivery_start_at'] = $order_delivered_date;
            } else {
                $data['order_delivery_start_at'] = PaymentRepository::getAfterBookingDelayTime(time(), 0);
            }
            $data['status_id'] = CustomerOrderStatus::DARK_ON_HOLD;
            $ordr = Order::where('order_id', $orderDet->order_id);
            $ordr->update($data);
            event(new OrderHistory($orderDet->order_id, CustomerOrderStatus::DARK_ON_HOLD));
        } else if ($orderDet->order_branch_type_id == 2) {
            $asctedbrach_cpr = 0;
            $orderItems = OrderItem::where('customer_order_id', $orderDet->order_id)
                    ->select('item_product_id', 'item_order_qty', 'item_retail_price', 'item_sales_price', 'item_order_qty', 'item_cgst', 'item_sgst', 'item_amount', 'item_price', 'item_kfc')
                    ->get();

            foreach ($orderItems as $orderIte) {
                $itemcprdets = ContractPOProducts::where('fcpod_itemid', $orderIte->item_product_id)
                        ->select('fcpod_vendorid', 'fcpo_validDate')
                        ->first();
                $count = Vendor::where('stpa_id', $itemcprdets->fcpod_vendorid)
                        ->where('deliverMode_cpr', 2)
                        ->count();
                if ($count > 0) {
                    $asctedbrach_cpr++;
                }
            }
            if ($asctedbrach_cpr > 0) {
                $data = [
                    'status_id' => CustomerOrderStatus::CPR_ON_HOLD,
                    'order_cutoff_time' => PaymentRepository::getAfterBookingDelayTime(time(), 2)
                ];
                $ordr = Order::where('order_id', $orderDet->order_id);
                $ordr->update($data);
                event(new OrderHistory($orderDet->order_id, CustomerOrderStatus::CPR_ON_HOLD));
            }
        } else {
            if ($orderDet->order_slot_id > 0) {
                $slot = DB::select('SELECT rbds_time_from, rbds_time_to FROM retaline_branch_delivery_slot WHERE rbds_id= ' . $orderDet->order_slot_id);
                if($slot)
                {
                    $default_br_schedulePackiing = DB::select("SELECT cfg_Value, cfg_Type FROM sys_configuration WHERE cfg_Name= 'DEFAULT_SCHEDULE_PACKING' ");
                    $date = date('Y-m-d', strtotime($orderDet->order_slot_date));
                    if ($br_schedulePackiing->br_schedulePackiing > 0) {
                        $time = date('H:i:s', strtotime('-' . $br_schedulePackiing->br_schedulePackiing . ' hours', strtotime($slot[0]->rbds_time_from)));
                    } else {
                        $time = date('H:i:s', strtotime('-' . $default_br_schedulePackiing[0]->cfg_Value . ' hours', strtotime($slot[0]->rbds_time_from)));
                    }

                    $order_delivered_date = $date . ' ' . $time;
                    $data = [
                        'status_id' => CustomerOrderStatus::PACK_ON_HOLD,
                        'order_delivery_start_at' => $order_delivered_date
                    ];
                    //order_delivered_date
                    $ordr = Order::where('order_id', $orderDet->order_id);
                    $ordr->update($data);
                    event(new OrderHistory($orderDet->order_id, CustomerOrderStatus::PACK_ON_HOLD));
                }
            }
        }

        return true;
    }

}
