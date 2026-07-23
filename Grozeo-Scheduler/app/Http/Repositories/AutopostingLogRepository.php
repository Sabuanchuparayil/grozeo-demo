<?php
namespace App\Http\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Aws\DynamoDb\DynamoDbClient;

class AutopostingLogRepository
{
	public function __construct() {}

	public function createLog($data)
	{
		$entityID = $data['entity_id'];
		$type = $data['type'];
		$info = @$data['info'] ?? "";

		$dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
		
		$uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
		$dynamoClient->putItem([
		'TableName' => config('aws.prefix').'application_log',
			'Item'      => [
				'uuid'				=> ['S' => (string)$uuid],
				'tstamp'			=> ['S' => (string)date('Y-m-d H:i:s')],
				'entity_id'			=> ['S' => (string)$entityID],
				'type'				=> ['S' => (string)$type],
				'detailed_info'		=> ['S' => $info]
			]
		]);
	}
}
