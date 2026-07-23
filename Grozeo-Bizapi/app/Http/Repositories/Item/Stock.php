<?php

namespace App\Http\Repositories\Item;

use App\Models\BlockedItems;
use BackOffice\Models\BranchInventory;
use Illuminate\Support\Facades\DB;


class Stock
{

    public static function getStock(array $stit_id, int $branch_id, $branchtypeid=1)
    {


        return (new static)->findStock($stit_id, $branch_id, $branchtypeid);
    }

    private function findStock($stit_id, $branch_id, $branchtypeid=1)
    {
    /*
        if($branchtypeid==2){
            $cpoproducts = DB::select('SELECT fcpod_itemid AS stit_id, 100 AS item_count FROM vw_cpo_products where branch_id=' . $branch_id);
            return array_column($cpoproducts, "item_count", "stit_id");
        }
*/
        $stocks = BranchInventory::whereIn('stit_id',$stit_id)
                    ->where('branch_id',$branch_id)
                    ->select('stit_id', 'item_count')
                    ->get();

         $stk = array_column($stocks->toArray(), "item_count", "stit_id");
        return $stk ? $this->blockedItem($stit_id, $branch_id, $stk) : $stk;
    }

    private function blockedItem($stit_id, $branch_id, array $stock)
    {
       $blocked = BlockedItems::whereIn('item_id', $stit_id)
                        ->where('branch_id', $branch_id)
                        ->selectRaw('item_id, SUM(count) as count')
                        ->groupBy('item_id')
                        ->get();

        $blocked_items = array_column($blocked->toArray(), "count", "item_id");
       foreach ($stock as $key => $value)
        {
           if(array_key_exists($key, $blocked_items))
           {
            $stk_no = $stock[$key] - $blocked_items[$key];
            $stock[$key] = ($stk_no < 0) ? 0 : $stk_no;
           }
        }


        return $stock;
    }

}
