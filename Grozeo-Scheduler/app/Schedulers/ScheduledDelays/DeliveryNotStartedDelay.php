<?php

namespace App\Schedulers\ScheduledDelays;

use DateTime;
use App\Models\{
    Order,
    ProcessLock,
    FinanceDeliveryType
};
use App\Schedulers\ScheduledDelays\AddDelayData;
use Illuminate\Support\Facades\DB;


class DeliveryNotStartedDelay
{
    public function __invoke()
    {
        try
        {
            $type = 3;
            $orderList = Order::select('order_id', 'order_method', 'status_id', 'order_branch_id', 'delivery_rule_id', 'order_confirm_date', 'order_confirmed_on', 'total', 'subtotal', 'order_order_id', 'payment_mode')
            ->leftJoin('delayed_order_log', function($join) use ($type){
                $join->on('delayed_order_log.orderID', 'retaline_customer_order.order_id');
                $join->where('delayed_order_log.type', $type);
            })
            ->with([
                'orderHistory',
                'deliveryAddress:deli_id,customer_order_id,order_customer_name,order_customer_email,order_contact_no,order_house_no,order_house_name,order_address,order_address2,order_land_mark,order_city,order_post,order_state',
                'branchDetails'
            ])
            ->where('delayed_order_log.orderID', NULL)
            ->whereIn('status_id', [9, 55])
            ->orderBy('order_id', 'DESC')
            ->get();
            $details = [];
            foreach ($orderList as $order)
            {
                $getRuleData = DB::table('retaline_delivery_rules')->where('rdr_id', $order->delivery_rule_id)->first();
                if($getRuleData)
                {
                    $delType = FinanceDeliveryType::where('id', $getRuleData->rdr_deliveryMode)->first();
                    if($delType)
                    {
                        $now = new DateTime();
                        $history = $order->OrderHistory->toArray();
                        $orderID = $order->order_id;
                        $status = $order->status_id;
                        $historyCheck = array_values(array_filter($history, function($item) use ($orderID, $status) {
                            return ($item['order_id'] == $orderID && $item['order_status'] == $status);
                        }));
                        if(@$historyCheck[0]['created_at'])
                        {
                            $checkDate = new DateTime($historyCheck[0]['created_at']);
                            $timeDiff =  $now->diff($checkDate);
                            $timeDiff = ($timeDiff->days * 24 * 60) + ($timeDiff->h * 60) + $timeDiff->i;
                            if($timeDiff > $delType->pickupAssignment_maxDelay)
                            {
                                $store_addr = [@$order->branchDetails->br_Address, @$order->branchDetails->br_City, @$order->branchDetails->district->dst_Name, @$order->branchDetails->state->st_name, @$order->branchDetails->br_pincode];
                                $cust_addr = [@$order->deliveryAddress->order_address, @$order->deliveryAddress->order_city, @$order->deliveryAddress->order_land_mark,  @$order->deliveryAddress->order_state, @$order->deliveryAddress->order_pin];
                                $customerDetails = [
                                    'name'      => $order->deliveryAddress->order_customer_name,
                                    'phone'     => $order->deliveryAddress->order_contact_no,
                                    'address'   => implode(', ', array_filter($cust_addr)),
                                ];
                                $merchantDetails = [
                                    'name'          => $order->branchDetails->br_Name,
                                    'phone'         => $order->branchDetails->br_Phone,
                                    'address'       => implode(', ', array_filter($store_addr)),
                                    'storegroupID'  => $order->branchDetails->br_StoreGroup
                                ];
                                $deliMode = $this->checkModeMethod($order);
                                $details[] = [
                                    "orderID"           => $order->order_id,
                                    "orderMethod"       => $order->order_method,
                                    "branchID"          => $order->order_branch_id,
                                    'type'              => $type,
                                    'status'            => '0',
                                    'mode'              => @$deliMode['mode'],
                                    'modeMethod'        => @$deliMode['method'],
                                    'merchantDetails'   => json_encode($merchantDetails),
                                    'customerDetails'   => json_encode($customerDetails),
                                    'orderDate'         => $order->order_confirmed_on,
                                    'orderTotal'        => $order->total,
                                    'deliveryType'      => $delType->id,
                                    'paymentMode'       => $order->payment_mode,
                                    'orderSubtotal'     => $order->subtotal,
                                    'orderOrderID'      => $order->order_order_id
                                ];
                            } 
                        }
                    }
                }
            }
            if(count($details) > 0)
            {
                AddDelayData::addData($details);
            }
            ProcessLock::updateColData("BizAPI_DeliveryNotStartedDelay", 0);
        }
        catch (\Exception $e)
        {
            info("DeliveryNotStartedDelay ERROR => ".$e);
            ProcessLock::updateColData("BizAPI_DeliveryNotStartedDelay", 1);
        }
    }
    
    private function checkModeMethod($order)
    {
        $outs = [
            'mode'      => NULL,
            'method'    => NULL
        ];
        if($order->order_method == 1)
        {
            $settings = $order->branchDetails->settings->toArray();
            $settingData = array_values(
                array_filter($settings, function($item) {
                    return ($item['type'] == 2 && $item['tp_type'] == "status" && $item['tp_value'] == 1);
                })
            );
            $type = @$settingData[0]['tp_name'];
            if($type)
            {
                $outs['mode'] = 2;
                $outs['method'] = $type;
            }
            else
            {
                $partner = config('expresspartners.default');
                if($partner != "")
                {
                    $outs['mode'] = 2;
                    $outs['method'] = $partner;
                }
                else
                {
                    $outs['mode'] = 1;
                    $outs['method'] = "";
                }
            }
        }
        if($order->order_method == 3)
        {
            $partner = config('courierpartners.default');
            if($partner != "")
            {
                $outs['mode'] = 2;
                $outs['method'] = $partner;
            }
            else
            {
                $outs['mode'] = 1;
                $outs['method'] = "";
            }
        }
        return $outs;
    }

}