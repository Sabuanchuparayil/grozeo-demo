<?php

namespace App\Http\Repositories\Item;

use Illuminate\Support\Facades\DB;
use BackOffice\Models\BranchInventory;

class CheapPrice
{
    public static function getDefault(array $groups, array $stock, array $price)
    {
        return (new static)->defaultItems($groups, $stock, $price);
    }
    public function defaultItems($groups, $stock, $price)
    {
       foreach($groups as $key => $group_item)
        {
            $groups[$key]['products'] = array_intersect_key($stock, array_flip($group_item['products']));
             $products = &$groups[$key]['products'];
             $out_of_stock = $products;
             foreach($products as $key_prod => $product)
             {
                if(empty($product))
                {
                    unset($products[$key_prod]);
                }
             }
             $products = (count($products) > 0) ? $products : $out_of_stock;
             $mrp = array_intersect_key($price['mrp'], $products);
             $groups[$key]['products'] = $mrp;
             $groups[$key]['minimum_prod'] = $mrp ? array_search(min($mrp), $products) : 0;
        }
       return array_column($groups, 'minimum_prod');
       
    }

}