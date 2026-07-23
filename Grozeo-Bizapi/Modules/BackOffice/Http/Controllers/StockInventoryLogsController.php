<?php

namespace BackOffice\Http\Controllers;

use Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\SuccessWithData;
use Aws\DynamoDb\DynamoDbClient;

class StockInventoryLogsController
{
    public function saveStockInventoryLogs(Request $request)
    {
        $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
        $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
        $result = $dynamoClient->putItem([
            'TableName' => config('aws.prefix').'stock_branch_inventory_log',
            'Item'      => [
                'uuid'                      => ['S' => $uuid],
                'tstamp'                    => ['S' => date('Y-m-d H:i:s')],
                'stit_id'                   => ['S' => ((@$request->stit_id != NULL) ? (string)$request->stit_id : "NULL")],
                'branch_id'                 => ['S' => ((@$request->branch_id != NULL) ? (string)$request->branch_id : "NULL")],
                'old_item_count'            => ['S' => ((@$request->old_item_count != NULL) ? (string)$request->old_item_count : "NULL")],
                'item_count'                => ['S' => ((@$request->item_count != NULL) ? (string)$request->item_count : "NULL")],
                'old_selling_price'         => ['S' => ((@$request->old_selling_price != NULL) ? (string)$request->old_selling_price : "NULL")],
                'selling_price'             => ['S' => ((@$request->selling_price != NULL) ? (string)$request->selling_price : "NULL")],
                'discount_selling_price'    => ['S' => ((@$request->discount_selling_price != NULL) ? (string)$request->discount_selling_price : "NULL")],
                'grozeo_margin'             => ['S' => ((@$request->grozeo_margin != NULL) ? (string)$request->grozeo_margin : "NULL")],
                'fpod_skuPurchaseRange'     => ['S' => ((@$request->fpod_skuPurchaseRange != NULL) ? (string)$request->fpod_skuPurchaseRange : "NULL")],
                'fpod_skuPurchaseQty'       => ['S' => ((@$request->fpod_skuPurchaseQty != NULL) ? (string)$request->fpod_skuPurchaseQty : "NULL")],
                'fpod_skuAvgPurchaseRate'   => ['S' => ((@$request->fpod_skuAvgPurchaseRate != NULL) ? (string)$request->fpod_skuAvgPurchaseRate : "NULL")],
                'fpod_skuLastPurchaseRate'  => ['S' => ((@$request->fpod_skuLastPurchaseRate != NULL) ? (string)$request->fpod_skuLastPurchaseRate : "NULL")],
                'fpod_leastSKUepr'          => ['S' => ((@$request->fpod_leastSKUepr != NULL) ? (string)$request->fpod_leastSKUepr : "NULL")],
                'fpod_effectivemargin'      => ['S' => ((@$request->fpod_effectivemargin != NULL) ? (string)$request->fpod_effectivemargin : "NULL")],
                'updated_on'                => ['S' => ((@$request->updated_on != NULL) ? (string)$request->updated_on : "NULL")],
                'updated_by'                => ['S' => ((@$request->updated_by != NULL) ? (string)$request->updated_by : "NULL")],
                'type'                      => ['S' => ((@$request->type != NULL) ? (string)$request->type : "NULL")],
                'action'                    => ['S' => ((@$request->action != NULL) ? (string)$request->action : "NULL")]
            ]
        ]);

        return response()->json(true);
    }
}