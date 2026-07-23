<?php
namespace App\Http\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Aws\DynamoDb\DynamoDbClient;

class PackingLogRepository
{
	public function __construct() {}

	public function createLog($data)
	{
		$orderID = $data['order_id'];
		$type = $data['type'];
		$APIName = (@$data['APIName'] ?? "");
		$APIURL = (@$data['APIURL'] ?? "");
		$APIHeaders = (@$data['APIHeaders'] ?? "");
		$request = $data['request'];
		$response = $data['response'];

		$dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
		
		$uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
		$dynamoClient->putItem([
		'TableName' => config('aws.prefix').'packing_order_log',
			'Item'      => [
				'uuid'			=> ['S' => (string)$uuid],
				'tstamp'		=> ['S' => (string)date('Y-m-d H:i:s')],
				'orderID'		=> ['S' => (string)$orderID],
				'type'			=> ['S' => (string)$type],
				'APIName'		=> ['S' => (string)$APIName],
				'APIURL'		=> ['S' => (string)$APIURL],
				'APIHeaders'	=> ['S' => (string)$APIHeaders],
				'request'		=> ['S' => $request],
				'response'		=> ['S' => $response]
			]
		]);
	}
}
