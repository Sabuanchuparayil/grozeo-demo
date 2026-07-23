<?php
namespace App\Http\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Aws\DynamoDb\DynamoDbClient;

class ShippingLogRepository
{
	public function __construct() {}

	public function createLog($data)
	{
		$orderID = $data['order_id'];
		$type = $data['type'];
		$orderMethod = (@$data['order_method'] ? $data['order_method'] : 3);
		$APIName = (@$data['APIName'] ?? "");
		$APIURL = (@$data['APIURL'] ?? "");
		$APIHeaders = (@$data['APIHeaders'] ?? "");
		$request = $data['request'];
		$response = $data['response'];

		$dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
		
		$uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
		$dynamoClient->putItem([
		'TableName' => config('aws.prefix').'shipping_consignment_log',
			'Item'      => [
				'uuid'			=> ['S' => (string)$uuid],
				'tstamp'		=> ['S' => (string)date('Y-m-d H:i:s')],
				'orderID'		=> ['S' => (string)$orderID],
				'type'			=> ['S' => (string)$type],
				'APIName'		=> ['S' => (string)$APIName],
				'APIURL'		=> ['S' => (string)$APIURL],
				'APIHeaders'	=> ['S' => (string)$APIHeaders],
				'orderMethod'	=> ['S' => (string)$orderMethod],
				'request'		=> ['S' => $request],
				'response'		=> ['S' => $response]
			]
		]);
	}
}
