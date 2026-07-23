<?php

namespace App\Schedulers;

use Carbon\Carbon;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;
use App\Status\DelayedOrderActions;
use App\Models\CourierDelivery\ShippingConsignment;

class PartnerDeliveryCompletedCheck
{
	public function __construct()
	{
		$this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
	}
    public function __invoke()
    {
        $items = $this->getDelayTableData();
        foreach ($items as $item)
        {
            $orderOrderID = $item['orderOrderID']['N'];
            $orderID = $item['orderID']['N'];
            $partner = $item['modeMethod']['S'];
            $orderMethod = $item['orderMethod']['N'];

            $ship = ShippingConsignment::where([
                ['order_id', $orderOrderID],
                ['shipping_type', $partner],
                ['order_method', $orderMethod],
                ['consignment_status', '<', 3]
            ])->first();
            if($orderMethod == 1 && $ship)
            {
                $shipping = config("expresspartners.{$partner}.sClass");
                $shipper = new $shipping();
                $outs = $shipper->checkDeliveryStatus($ship, 1);
            }
            if($orderMethod == 3 && $ship)
            {
                $shipping = config("courierpartners.{$partner}.sClass");
                $shipper = new $shipping();
                $outs = $shipper->completeTrackingStatus($ship);
            }
            if(@$outs['data'] == 'delivered')
            {
                $updateDelay = $this->updateDelay($item);
            }
            else
            {
                $skipDate = Carbon::now()->addMinutes(15)->toDateTimeString();
                $updateDelay = $this->updateDelay($item, $skipDate);
            }
        }
    }

    private function getDelayTableData()
    {
        $timeFrom = Carbon::now()->subMinutes(15)->toDateTimeString();
        $timeTo = Carbon::now()->addMinutes(15)->toDateTimeString();
        $type = 4;
        $mode = 2;
        $params = [
            'TableName'                 => config('aws.prefix').'delayed_orders',
            'FilterExpression'          => '#skipDateAttr BETWEEN :skipDateFrom and :skipDateTo AND #typeAttr = :typeValue AND #modeAttr = :modeValue',
            'ExpressionAttributeNames'  => [
                '#typeAttr'     => 'type',
                '#modeAttr'     => 'mode',
                '#skipDateAttr' => 'skipDate',
            ],
            'ExpressionAttributeValues' => [
                ':skipDateFrom' => ['S' => $timeFrom],
                ':skipDateTo'   => ['S' => $timeTo],
                ':typeValue'    => ['N' => (string)$type],
                ':modeValue'    => ['N' => (string)$mode]
            ],
        ];
        $items = [];
        do
        {
            $result = $this->dynamoClient->scan($params);
            $items = array_merge($items, $result['Items']);
            $params['ExclusiveStartKey'] = $result['LastEvaluatedKey'];
        } while (!empty($params['ExclusiveStartKey']));

        array_reverse($items);

        return $items;
    }
    private function updateDelay($item, $skipDate = "")
    {
        $attributes = ['#statusAttr' => 'status'];
        $values = [':statusVal' => ['N' => (string)DelayedOrderActions::DELIVERY_COMPLETED]];
        $expression = 'SET #statusAttr = :statusVal';
        if($skipDate != "")
        {
            $attributes['#skipDateAttr'] = 'skipDate';
            $values = [
                ':statusVal'    => ['N' => '0'],
                ':skipDateVal'  => ['S' => $skipDate]
            ];
            $expression .= ', #skipDateAttr = :skipDateVal';
        }
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
}