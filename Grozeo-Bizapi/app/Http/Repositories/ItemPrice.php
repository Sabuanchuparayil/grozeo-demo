<?php

namespace App\Http\Repositories;

use BackOffice\Models\BranchInventory;

class ItemPrice
{

   public static function findPrice(int $stit_id, int $branch_id)
   {
        return (new static)->getPrice($stit_id, $branch_id);
   }

   private function getPrice($stit_id, $branch_id)
   {
        return BranchInventory::where('stit_id',$stit_id)
                        ->where('branch_id',$branch_id)
                        ->select('mrp','selling_price')
                        ->first();
        
   }

}