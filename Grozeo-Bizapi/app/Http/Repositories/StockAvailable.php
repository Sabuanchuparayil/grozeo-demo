<?php

namespace App\Http\Repositories;

use App\Models\BlockedItems;
use BackOffice\Models\BranchInventory;


class StockAvailable
{

    /**
     * Static function for calling StockAvailability.!
     *
     * @param [integer] $stit_id
     * @param [integer] $branch_id
     * @return int
     */
    public static function checkStock($stit_id, $branch_id)
    {
        return (new static)->getStockAvailable($stit_id, $branch_id);
    }
     /**
     * check stock available based on product id and branch id
     *
     * @param string $stit_id
     * @param string $branch_id
     * @return int
     */
    public function getStockAvailable($stit_id = '',$branch_id = '')
    {
       $stock = BranchInventory::where('stit_id',$stit_id)
                        ->where('branch_id',$branch_id)
                        ->select('item_count')
                        ->first();
        $stock = $stock->item_count ?? 0;
        return $stock ? $this->blockedItemAvailable($stit_id, $branch_id, $stock) : $stock;
    }
    /**
     * Compare Stock and blocked Item count
     *
     * @param [integer] $stit_id
     * @param [integer] $branch_id
     * @param [integer] $stock_count
     * @return integer
     */
    private function blockedItemAvailable($stit_id, $branch_id, $stock_count)
    {
        //$customer_id = auth_user()->cust_id ?? 0;  //where('customer_id', $customer_id)
        $blocked_count = BlockedItems::where('item_id', $stit_id)
                        ->where('branch_id', $branch_id)
                        ->select('count')
                        ->first();
        $blocked = $blocked_count['count'] ?? 0;
        $count = $stock_count - $blocked;
        return ($count > 0) ? $count : 0;
    }
    
}