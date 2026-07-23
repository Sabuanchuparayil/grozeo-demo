<?php
namespace App\Http\Repositories;

use Exception;
use Illuminate\Http\Request;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;
use Aws\DynamoDb\Exception\DynamoDbException;

class PostingRepository
{
	public function __construct()
	{
		$this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
	}
	public function finascopPosting(Request $request)
	{
		$order_id = $request->order_id;
		$finascopEventRefId = $request->finascopEventRefId;
		$storeGroupId  = ((@$request->storegroup_id) ? $request->storegroup_id : -1);
		try
		{
			$uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
			$result = $this->dynamoClient->putItem([
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
			$resultMeta = $result->get('ResponseMetadata');
			return true;
		}
		catch (DynamoDbException $e)
		{
            info("dynamodb exception Error, PostingRepository finascopPosting, on dynamodb put item: ");
			info($e);
            return false;
        }
		catch (Exception $e)
		{
            info("Error, PostingRepository finascopPosting, on dynamodb put item: ");info($e);
            return false;
        }
	}
}