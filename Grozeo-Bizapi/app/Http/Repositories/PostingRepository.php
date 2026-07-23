<?php
namespace App\Http\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Aws\DynamoDb\DynamoDbClient;

class PostingRepository
{
	public function __construct() {}

	public function finascopPosting(Request $request)
	{
		$order_id = $request->order_id;
		$finascopEventRefId = $request->finascopEventRefId;
		$storeGroupId  = ((@$request->storegroup_id) ? $request->storegroup_id : -1);

		$dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
		
		$uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
		$dynamoClient->putItem([
		'TableName' => config('aws.prefix').'named_events',
			'Item'      => [
				'uuid'                  => ['S' => (string)$uuid],
				'tstamp'                => ['S' => (string)date('Y-m-d H:i:s')],
				'order_id'              => ['S' => (string)$order_id],
				'finascopEventRefId'    => ['S' => (string)$finascopEventRefId],
				'storeGroupId'          => ['S' => (string)$storeGroupId],
				'neStatus'              => ['S' => '0']
			]
		]);
	}
}
