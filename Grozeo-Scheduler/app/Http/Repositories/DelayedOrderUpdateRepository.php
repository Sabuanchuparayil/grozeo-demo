<?php
namespace App\Http\Repositories;

use App\Models\{
    Order,
    DelayedOrderLog,
    FinanceDeliveryType
};
use Carbon\Carbon;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;

class DelayedOrderUpdateRepository
{
    protected $dynamoClient;
    public function __construct()
    {
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
    }
    public function delayedOrderActions($orderID, $type, $delay = 0)
    {
        switch($type)
        {
            case 2:
                $this->packingNotStarted($orderID);
                break;
            case 3:
                $this->packingNotCompleted($orderID);
                break;
            case 4:
                $this->bookingNotStarted($orderID, $delay);
                break;
            case 5:
                $this->deliveryNotStarted($orderID);
                break;
            case 6:
                $this->deliveryNotCompleted($orderID);
                break;
            case 7:
                $this->settlementDelay($orderID);
                break;
        }
    }
    private function packingNotStarted($orderID)
    {
        $order = Order::where('order_id', $orderID)->first();
        $delayData = $this->getDelayTableData($orderID, 0);
        if($order && empty($delayData))
        {
            $now = Carbon::now();
            $ruleData = @$order->deliveryRule->deliveryType->packingAssignment_maxDelay ?? NULL;
            $skipDate = (@$ruleData) ? $now->addMinutes($ruleData) : $now;
            $skipDate = $skipDate->toDateTimeString();

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
                'storegroupID'  => $order->branchDetails->br_storeGroup
            ];
            $deliMode = $this->checkModeMethod($order);

            $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
            $this->dynamoClient->putItem([
            'TableName' => config('aws.prefix').'delayed_orders',
            'Item'      => [
                'uuid'              => ['S' => $uuid],
                'tstamp'            => ['S' => date('Y-m-d H:i:s')],
                'orderID'           => ['N' => (string)$order->order_id],
                'orderOrderID'      => ['N' => (string)$order->order_order_id],
                'type'              => ['N' => '2'],
                'orderMethod'       => ['N' => (string)$order->order_method],
                'branchID'          => ['N' => (string)$order->order_branch_id],
                'status'            => ['N' => '0'],
                'mode'              => ['S' => (string)@$deliMode['mode']],
                'modeMethod'        => ['S' => @$deliMode['method']],
                'orderDate'         => ['S' => $order->order_confirmed_on],
                'orderTotal'        => ['N' => (string)$order->total],
                'merchantDetails'   => ['S' => json_encode($merchantDetails)],
                'customerDetails'   => ['S' => json_encode($customerDetails)],
                'deliveryType'      => ['S' => (string)@$order->deliveryRule->deliveryType->id],
                'paymentMode'       => ['N' => (string)$order->payment_mode],
                'orderSubtotal'     => ['N' => (string)$order->subtotal],
                'action'            => ['N' => '0'],
                'skipDate'          => ['S' => $skipDate]
            ]
        ]);
            DelayedOrderLog::create([
                'orderId'   => $order->order_id,
                'type'      => 2,
                'uuid'      => $uuid
            ]);
        }
    }
    private function packingNotCompleted($orderID)
    {
        $type = 3;
        $items = $this->getDelayTableData($orderID, $type);
        if($items)
        {
            foreach ($items as $item)
            {
                $skipDuration = FinanceDeliveryType::where('id', $item['deliveryType']['S'])->value('packingCompletion_maxDelay');
                if($skipDuration)
                {
                    $now = Carbon::now();
                    $skipDate = $now->addMinutes($skipDuration);
                    $attributes = [
                        '#statusAttr'   => 'status',
                        '#typeAttr'     => 'type',
                        '#skipDateAttr' => 'skipDate',
                    ];
                    $values = [
                        ':statusVal'    => ['N' => '0'],
                        ':typeVal'      => ['N' => (string)$type],
                        ':skipDateVal'  => ['S' => $skipDate->toDateTimeString()]
                    ];
                    $expression = 'SET #statusAttr = :statusVal, #skipDateAttr = :skipDateVal, #typeAttr = :typeVal';
                    $this->updateDelayTable($item, $attributes, $values, $expression);
                }
            }
        }
    }
    private function bookingNotStarted($orderID, $delay = 0)
    {
        $type = 4;
        $order = Order::where('order_id', $orderID)->first();
        $deliMode = $this->checkModeMethod($order, 2);
        if(@$deliMode['method'] == "")
        {
            $this->deliveryNotStarted($orderID);
            return null;
        }
        $items = $this->getDelayTableData($orderID, $type);
        if($items)
        {
            foreach ($items as $item)
            {
                $now = Carbon::now();
                $skipDate = $now->addMinutes($delay);
                $attributes = [
                    '#statusAttr'   => 'status',
                    '#typeAttr'     => 'type',
                    '#skipDateAttr' => 'skipDate',
                    '#modeAttr'     => 'mode',
                    '#modeMethAttr' => 'modeMethod',
                ];
                $values = [
                    ':statusVal'    => ['N' => '0'],
                    ':typeVal'      => ['N' => (string)$type],
                    ':skipDateVal'  => ['S' => $skipDate->toDateTimeString()],
                    ':modeVal'      => ['S' => (string)@$deliMode['mode']],
                    ':modeMethVal'  => ['S' => @$deliMode['method']]
                ];
                $expression = 'SET #statusAttr = :statusVal, #skipDateAttr = :skipDateVal, #typeAttr = :typeVal, #modeAttr = :modeVal, #modeMethAttr = :modeMethVal';
                $this->updateDelayTable($item, $attributes, $values, $expression);
            }
        }
    }
    private function deliveryNotStarted($orderID)
    {
        $type = 5;
        $order = Order::where('order_id', $orderID)->first();
        $items = $this->getDelayTableData($orderID, $type);
        if($items)
        {
            foreach ($items as $item)
            {
                $skipDuration = FinanceDeliveryType::where('id', $item['deliveryType']['S'])->value('pickupAssignment_maxDelay');
                if($skipDuration)
                {
                    $now = Carbon::now();
                    $skipDate = $now->addMinutes($skipDuration);
                    $deliMode = $this->checkModeMethod($order, 2);
                    $attributes = [
                        '#statusAttr'   => 'status',
                        '#typeAttr'     => 'type',
                        '#skipDateAttr' => 'skipDate',
                        '#modeAttr'     => 'mode',
                        '#modeMethAttr' => 'modeMethod',
                    ];
                    $values = [
                        ':statusVal'    => ['N' => '0'],
                        ':typeVal'      => ['N' => (string)$type],
                        ':skipDateVal'  => ['S' => $skipDate->toDateTimeString()],
                        ':modeVal'      => ['S' => (string)@$deliMode['mode']],
                        ':modeMethVal'  => ['S' => @$deliMode['method']]
                    ];
                    $expression = 'SET #statusAttr = :statusVal, #skipDateAttr = :skipDateVal, #typeAttr = :typeVal, #modeAttr = :modeVal, #modeMethAttr = :modeMethVal';
                    $this->updateDelayTable($item, $attributes, $values, $expression);
                }
            }
        }
    }
    private function deliveryNotCompleted($orderID)
    {
        $type = 6;
        $items = $this->getDelayTableData($orderID, $type);
        if($items)
        {
            foreach ($items as $item)
            {
                $skipDuration = FinanceDeliveryType::where('id', $item['deliveryType']['S'])->value('deliveryCompletionTime');
                if($skipDuration)
                {
                    $now = Carbon::now();
                    $skipDate = $now->addMinutes($skipDuration);
                    $attributes = [
                        '#statusAttr'   => 'status',
                        '#typeAttr'     => 'type',
                        '#skipDateAttr' => 'skipDate',
                    ];
                    $values = [
                        ':statusVal'    => ['N' => '0'],
                        ':typeVal'      => ['N' => (string)$type],
                        ':skipDateVal'  => ['S' => $skipDate->toDateTimeString()]
                    ];
                    $expression = 'SET #statusAttr = :statusVal, #skipDateAttr = :skipDateVal, #typeAttr = :typeVal';
                    $this->updateDelayTable($item, $attributes, $values, $expression);
                }
            }
        }
    }
    private function settlementDelay($orderID)
    {
        $type = 7;
        $settlementDate = Order::where('order_id', $orderID)->value('settlement_date');
        $skipDate = @$settlementDate ? "{$settlementDate} 00:00:00" : now();
        $items = $this->getDelayTableData($orderID, $type);
        if($items)
        {
            foreach ($items as $item)
            {
                $attributes = [
                    '#statusAttr'   => 'status',
                    '#typeAttr'     => 'type',
                    '#skipDateAttr' => 'skipDate',
                ];
                $values = [
                    ':statusVal'    => ['N' => '0'],
                    ':typeVal'      => ['N' => (string)$type],
                    ':skipDateVal'  => ['S' => $skipDate]
                ];
                $expression = 'SET #statusAttr = :statusVal, #skipDateAttr = :skipDateVal, #typeAttr = :typeVal';
                $this->updateDelayTable($item, $attributes, $values, $expression);
            }
        }
    }
    
    private function checkModeMethod($order, $mode = 1)
    {
        $outs = [
            'mode'      => NULL,
            'method'    => NULL
        ];
        $settings = $order->branchDetails->settings->toArray();
        $settingData = array_values(
            array_filter($settings, function($item) use ($mode) {
                return ($item['type'] == $mode && $item['tp_type'] == "status" && $item['tp_value'] == 1);
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
            $partner = ($mode == 1) ? config('packingpartners.default') : (($order->order_method == 1) ? config('expresspartners.default') : config('courierpartners.default'));
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
    private function getDelayTableData($orderID, $type = 0)
    {
        $attributes['#orderAttr'] = 'orderID';
        $values[':orderVal'] = ['N' => (string)$orderID];
        $expression = '#orderAttr = :orderVal';
        if($type > 0)
        {
            $attributes['#typeAttr'] = 'type';
            $values[':typeValue'] = ['N' => (string)$type];
            $expression .= ' AND #typeAttr < :typeValue';
        }
        $params = [
            'TableName'                 => config('aws.prefix').'delayed_orders',
            'FilterExpression'          => $expression,
            'ExpressionAttributeNames'  => $attributes,
            'ExpressionAttributeValues' => $values,
        ];
        $response = $this->dynamoClient->scan($params);
        $items = @$response['Items'];

        return $items;
    }
    private function updateDelayTable($item, $attributes, $values, $expression)
    {
        $this->dynamoClient->updateItem([
            'TableName'                 => config('aws.prefix').'delayed_orders',
            'Key'                       => [
                'uuid'      => ['S' => $item['uuid']['S']],
                'tstamp'    => ['S' => $item['tstamp']['S']]
            ],
            'ExpressionAttributeNames'  => $attributes,
            'ExpressionAttributeValues' => $values,
            'UpdateExpression'          => $expression
        ]);
    }


    public function delayedUpdate($orderID, $type, $status)
    {
        $params = [
            'TableName'                 => config('aws.prefix').'delayed_orders',
            'FilterExpression'          => 'orderID = :orderID AND #typeAttr = :typeValue AND #statusAttr <> :statusVal',
            'ExpressionAttributeNames'  => [
                '#typeAttr'     => 'type',
                '#statusAttr'   => 'status'
            ],
            'ExpressionAttributeValues' => [
                ':orderID'      => ['N' => (string)$orderID],
                ':typeValue'    => ['N' => (string)$type],
                ':statusVal'    => ['N' => (string)$status]
            ],
        ];
        if($orderID > 0)
        {
            $response = $this->dynamoClient->scan($params);

            $items = @$response['Items'];
            if($items)
            {
                foreach ($items as $item)
                {
                    $response = $this->dynamoClient->updateItem([
                        'TableName'                 => config('aws.prefix').'delayed_orders',
                        'Key'                       => [
                            'uuid'      => ['S' => $item['uuid']['S']],
                            'tstamp'    => ['S' => $item['tstamp']['S']]
                        ],
                        'ExpressionAttributeNames' => [
                            '#statusAttr' => 'status',
                        ],
                        'ExpressionAttributeValues' => [
                            ':statusVal' => ['N' => (string)$status]
                        ],
                        'UpdateExpression'          => 'SET #statusAttr = :statusVal'
                    ]);
                }
            }
        }
    }
}