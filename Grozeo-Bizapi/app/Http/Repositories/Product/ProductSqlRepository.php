<?php

namespace App\Http\Repositories\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\StockItemMaster;
use App\Models\StockUniqueItem;

class ProductSqlRepository
{

    protected $itemMaster;

    protected $uniqueItem;

    public function __construct(StockItemMaster $itemMaster = null, StockUniqueItem $uniqueItem = null)
    {
        $this->itemMaster = $itemMaster ?? new StockItemMaster;
        $this->uniqueItem = $uniqueItem ?? new StockUniqueItem;
    }

    public function filter(Request $filter)
    {
        return $this->itemMaster
            ->select('stit_ID', 'stit_SKU', 'pdt_brand', 'stit_fsiuid')
            ->where('stit_sku', 'like', "%{$filter->product_name}%")
            ->with('brand:brand_id,brand_name')
            ->with('thumbImage:product_id,image_thumb_url')
            ->with(['branchStock' => function ($query) use ($filter) {
                $query->select('stit_id', 'branch_id', 'item_count', 'mrp', 'selling_price')
                    ->where('branch_id', $filter->branch_id);
            }])
            ->get();
    }

    public function search(Request $filter)
    {
        $items = $this->uniqueItem
            ->select(
                'fsi_uid', 
                'fsi_uid as item_group_id',
                'fsi_item_name as item_name',
                'fsi_brand_name as brand_name',
                'fsi_category_id as category_id',
                'fsi_categry_name as category_name',
                'fsi_variant as variant'
            )
            ->where('fsi_item_name', 'like', "%{$filter->product_name}%")
            ->orWhere('fsi_brand_name', 'like', "%{$filter->product_name}%")
            ->orWhere('fsi_categry_name', 'like', "%{$filter->product_name}%")
            ->with(['itemMaster' => function ($query) {
                $query->select(
                    'stit_ID', 
                    'stit_fsiuid', 
                    'stit_quantity as quantity',
                    'stit_itemId as itemId',
                    'stit_Description as short_description',
                    'stit_long_description as long_description'
                )
                ->with(['mainImage' => function ($query) {
                    $query->select('id', 'product_id', 'image_url', 'image_thumb_url')
                        ->where('image_type', 1);
                }])
                ->with(['additionalImage' => function ($query) {
                    $query->select('id', 'product_id', 'image_url', 'image_thumb_url')
                        ->where('image_type', 1);
                }]);
            }])
            ->get()->toArray();

        return app(ProductRepository::class)->addFields($items, $filter->branch_id);
    }
    
}
