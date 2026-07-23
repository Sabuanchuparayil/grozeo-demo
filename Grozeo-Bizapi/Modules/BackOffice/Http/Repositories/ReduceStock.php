<?php

namespace BackOffice\Http\Repositories;

use App\Models\Cart;
use App\Models\BlockedItems;
use Illuminate\Support\Facades\DB;
use BackOffice\Models\BranchInventory;

class ReduceStock
{

    public static function minusStock($order_id,$fstoId)
    {
        return (new static)->reduceStockNo($order_id,$fstoId);
    }

    public static function orderCancelled($order_id)
    {
        return (new static)->removeBlockedItem($order_id);
    }

    public static function unblockItems($order_id)
    {
        return (new static)->removeBlockedItem($order_id);
    }

    public static function ResetChildItemsStock($parentProductId, $branchId){
        return (new static)->updateChildStock($parentProductId, $branchId,0);
    }
    /**
     * Reduce stock count when transaction is completed.
     *
     * @param string $pay
     * @return json
     */
    private function reduceStockNo($order_id,$fstoId)
    {

        $blockedItem = $this->getStockBlocked($order_id)
            ->toArray();
        
        $this->updateBranchStock($blockedItem, $order_id,$fstoId);

        return $order_id;
    }
    /**
     * Get product based on order id .
     *
     * @param string $orderId
     * @return \Illuminate\Support\Collection
     */
    private function getStockBlocked($order_id)
    {
        return BlockedItems::where('order_id', $order_id)
            ->where("markedfordelivery", 1)
            ->select('item_id', 'branch_id', 'count')
            ->get();
    }

    private function updateBranchStock(array $blockedItem, $order_id,$fstoId)
    {
        $result_count = [];
        $products = array_column($blockedItem, 'item_id');
        $branch = $blockedItem[0]['branch_id'] ?? '';

        $branchInventoy = BranchInventory::whereIn('stit_id', $products)
                    ->where('branch_id', $branch)
                    ->select('stit_id', 'item_count', 'isOnDemand')
                    ->get()->toArray();
        $branch_count = array_column($branchInventoy, 'item_count', 'stit_id');
        $blocked_count = array_column($blockedItem, 'count', 'item_id');
        $onDemand = array_column($branchInventoy, 'isOnDemand', 'stit_id');

            $parentProductIds = array();
        foreach($blocked_count as $key => $item)
        {
            $branch_val = $branch_count[$key] ?? 0;
            $blocked_val = $blocked_count[$key] ?? 0;
            
            $actual_count = (@$onDemand[$key] == 1) ? 1000 : (int) $branch_val - (int) $blocked_val;
                 $stit_tem = DB::table('finascop_stock_itemmaster')->select('stit_ParentItemId', 'stit_ConvertCalcRate')->where('stit_id', $key)->where('stit_ParentItemId', '>', DB::raw("0"))->first();
                 if(isset($stit_tem) && $stit_tem->stit_ParentItemId > 0){ // Update parent stock
                     if($fstoId > 0){
                        // If the convert rate is different on packing for the item on the particular order packing then take the new value. Otherwise the orignal conversion rate.
                        $convertCalcRate = DB::table('finascop_stock_transfer_order_details')->select('fsto_stockValue')->where('fsto_ItemId', $key)->where('fsto_id', $fstoId)->first();
		                $stit_ConvertCalcRate = (isset($convertCalcRate) && isset($convertCalcRate->fsto_stockValue) ? $convertCalcRate->fsto_stockValue : 0);
		                if($stit_ConvertCalcRate == 0){					
                            $stit_ConvertCalcRate = (isset($stit_tem->stit_ConvertCalcRate) ? $stit_tem->stit_ConvertCalcRate : 0);
                        }
                     }else{
                        $stit_ConvertCalcRate = (isset($stit_tem->stit_ConvertCalcRate) ? $stit_tem->stit_ConvertCalcRate : 0);
                     }

                    $convertCalcRate = (isset($stit_ConvertCalcRate) ? $stit_ConvertCalcRate : 0) * ((int) $blocked_val);

                    $brParentInventory = BranchInventory::where('stit_id', $stit_tem->stit_ParentItemId)->where('branch_id', $branch)->first();
                    if(isset($brParentInventory)){
                        $pstock = ($brParentInventory->item_count - $convertCalcRate);
                        $pstock = ($pstock < 0 ? 0 : $pstock);
                        if($pstock != $brParentInventory->item_count)
                            $brParentInventory->update(['item_count' => $pstock]);

                        // Push to array, in order to call the update child stock function, after removing blocked item.
                        array_push($parentProductIds, $stit_tem->stit_ParentItemId);
                    }
                 }
                  
            $result_count[] = [
                            'count' => $actual_count,
                            'product_id' => $key
                            ];
        }

            $products_id = array_column($result_count, "product_id");
            BranchInventory::whereIn('stit_id', $products_id)
                            ->where('branch_id', $branch)
                            ->lockForUpdate()
                            ->get();

            foreach($result_count as $item)
            {
                    BranchInventory::where('stit_id', $item['product_id'])->where('branch_id', $branch)
                        ->update(['item_count' => $item['count']]);
                }
            $this->removeBlockedItem($order_id,$fstoId);
            foreach($parentProductIds as $parentid){
                $this->updateChildStock($parentid, $branch,$fstoId);
            }
    }


    private function removeBlockedItem($order_id,$fstoId=0)
    {
        $orderParentItems = BlockedItems::join('finascop_stock_itemmaster as fs', 'fs.stit_id', 'finascop_stock_blocked.item_id')
                            ->select('fs.stit_ParentItemId', 'finascop_stock_blocked.branch_id')
                            ->where('finascop_stock_blocked.order_id', $order_id)
                            ->where('finascop_stock_blocked.markedfordelivery', 1)
                            ->where('fs.stit_ParentItemId', '>', DB::raw("0"))->groupBy('stit_ParentItemId')->get();

        $removedItem = BlockedItems::where('order_id', $order_id)
                            ->where('markedfordelivery', 1)
                            ->delete();
                             
        // Trigger update sub products stock
        if(isset($orderParentItems) && count($orderParentItems)> 0){
            foreach($orderParentItems as $orderParentItem)
                $this->updateChildStock($orderParentItem->stit_ParentItemId, $orderParentItem->branch_id,$fstoId);
        }

        return $removedItem;
    }

    // Get parent product along with blocked quantity of all sub products. The blocked quantity will be calculated with stit_ConvertCalcRate for each sub item.
    private function getParentStockItem($parentProductId, $branchId){
	$sql= '(SELECT '.$parentProductId.' AS pid, SUM(bl.count * fs.stit_ConvertCalcRate) AS blockedCount FROM finascop_stock_blocked bl
            INNER JOIN finascop_stock_itemmaster fs ON fs.stit_id=bl.item_id WHERE bl.branch_id = '.$branchId.' and fs.stit_ParentItemId= '.$parentProductId.' ) as blocked';
	    return BranchInventory::leftJoin(DB::raw($sql), function($join)
            {
                $join->on('blocked.pid', '=', 'finascop_stock_branch_inventory.stit_id');
            })
            ->where('stit_id', $parentProductId)->where('finascop_stock_branch_inventory.branch_id', $branchId)
		->select('stit_id', 'item_count', 'blocked.blockedCount', DB::raw('ifnull(blocked.blockedCount, 0) as blockedCount'))->first();
    }

    // Update sub products stock.
    private function updateChildStock($parentProductId, $branchId, $fstoId){
	    $parentProductStock = $this->getParentStockItem($parentProductId, $branchId);
        if(!isset($parentProductStock))
            return;

        $parentActualStock = $parentProductStock->item_count - $parentProductStock->blockedCount;
	    $subProducts = DB::table('finascop_stock_itemmaster')->select('stit_id', 'stit_ConvertCalcRate')->where('stit_ParentItemId', $parentProductId)->get();

	    foreach($subProducts as $subProduct){
                /*if($fstoId > 0){
                    $convertCalcRate = DB::table('finascop_stock_transfer_order_details')->select('fsto_stockValue')->where('fsto_ItemId', $subProduct->stit_id)->where('fsto_id', $fstoId)->first();
		            $stit_ConvertCalcRate = isset($convertCalcRate->fsto_stockValue) ? $convertCalcRate->fsto_stockValue : 0;
		            if($stit_ConvertCalcRate == 0){					
                        $stit_ConvertCalcRate = $subProduct->stit_ConvertCalcRate;
                    }
                }else{
                    $stit_ConvertCalcRate = $subProduct->stit_ConvertCalcRate;
                }*/
                $stit_ConvertCalcRate = $subProduct->stit_ConvertCalcRate;
		    $subProductStock = (1 / $stit_ConvertCalcRate) * $parentActualStock;
            $subProductStock = floor($subProductStock);
            $subProductBlockedCount = DB::table('finascop_stock_blocked')->where('item_id', $subProduct->stit_id)->where('branch_id', $branchId)->sum('count');
            if(!isset($subProductBlockedCount))
                $subProductBlockedCount = 0;
            $subCurrentStock = $subProductStock + $subProductBlockedCount;
		    BranchInventory::where('stit_id', $subProduct->stit_id)->where('branch_id', $branchId)->update(['item_count' => ($subCurrentStock < 0 ? 0: $subCurrentStock)]);
	    }
    }


}
