<?php

namespace App\Schedulers;

use Carbon\Carbon;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;
use App\Models\FinanceDeliveryType;
use App\Models\CourierDelivery\ShippingConsignment;

class PartnerDeliveryStartedCheck
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
            $skipDuration = FinanceDeliveryType::where('id', $item['deliveryType']['S'])->value('deliveryCompletionTime');
            $skipDate = now();
            if($skipDuration)
            {
                $skipDate = Carbon::now()->addMinutes($skipDuration)->toDateTimeString();
            }
            if($orderMethod == 1 && $ship)
            {
                $shipping = config("expresspartners.{$partner}.sClass");
                $shipper = new $shipping();
                $outs = $shipper->checkDeliveryStatus($ship, 0);
            }
            if($orderMethod == 3 && $ship)
            {
                $shipping = config("courierpartners.{$partner}.sClass");
                $shipper = new $shipping();
                $outs = $shipper->completeTrackingStatus($ship);
            }
            if(@$outs['data'] == 'pickup')
            {
                $updateDelay = $this->updateDelay($item, 4, $skipDate);
            }
            else
            {
                $skipDate = Carbon::now()->addMinutes(15)->toDateTimeString();
                $updateDelay = $this->updateDelay($item, 3, $skipDate);
            }
        }
    }

    private function getDelayTableData()
    {
        $timeFrom = Carbon::now()->subMinutes(15)->toDateTimeString();
        $timeTo = Carbon::now()->addMinutes(15)->toDateTimeString();
        $type = 3;
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
            ]
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

    private function updateDelay($item, $type, $skipDate)
    {
        $this->dynamoClient->updateItem([
            'TableName'                 => config('aws.prefix').'delayed_orders',
            'Key'                       => [
                'uuid'      => ['S' => $item['uuid']['S']],
                'tstamp'    => ['S' => $item['tstamp']['S']]
            ],
            'ExpressionAttributeNames'  => [
                '#statusAttr'   => 'status',
                '#typeAttr'     => 'type',
                '#skipDateAttr' => 'skipDate',
            ],
            'ExpressionAttributeValues' => [
                ':statusVal'    => ['N' => '0'],
                ':typeVal'      => ['N' => (string)$type],
                ':skipDateVal'  => ['S' => $skipDate]
            ],
            'UpdateExpression'          => 'SET #statusAttr = :statusVal, #skipDateAttr = :skipDateVal, #typeAttr = :typeVal'
        ]);
    }
}