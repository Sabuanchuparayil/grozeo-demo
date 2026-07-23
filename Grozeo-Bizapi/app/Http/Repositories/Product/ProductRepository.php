<?php

namespace App\Http\Repositories\Product;

use App\Events\Searched;
use App\Models\StockItemMaster;
use App\Models\StockUniqueItem;
use App\Domains\Search\Suggestion;
use App\Http\Repositories\ItemPrice;
use App\Domains\Search\SearchProduct;
use App\Http\Repositories\Item\Price;
use App\Http\Repositories\Item\Stock;
use BackOffice\Models\BranchInventory;
use App\Http\Repositories\StockAvailable;
use App\Http\Repositories\Item\CheapPrice;
use DB;
class ProductRepository
{
    /**
     * Product model object
     *
     * @var \App\Models\Product
     */
    protected $product;
    /**
     * Stock unique item model object.
     *
     * @var [type]
     */
    protected $stockItem;

    /**
     * itemMaster model object.
     *
     * @var [type]
     */
    protected $itemMaster;

    /**
     * Indicates Popular sort case.
     */
    const POPULAR = 1;

    /**
     * Indicates High to Low sort case.
     */
    const PRICE_HIGH_TO_LOW = 2;

    /**
     * Indicates Low to High sort case.
     */
    const PRICE_LOW_TO_HIGH = 3;

    public function __construct( StockUniqueItem $stockItem, StockItemMaster $itemMaster)
    {
        
        $this->stockItem = $stockItem;
        $this->itemMaster = $itemMaster;
    }

    /**
     * Get all subcategories of a particular category
     *
     * @param string $id
     * @return \Illuminate\Support\Collection
     */
    public function get($id)
    {
        return $this->product
            ->select('product_id', 'product_name', 'product_image_url', 'pdt_mrp', 'pro_nutri_info')
            ->where('product_category', $id)
            ->where('status', 'active')
            ->paginate(10);
    }

    /**
     * Fetch single product details
     *
     * @param integer $id
     * @return \App\Models\Product
     */
    public function getById($id)
    {
        return $this->product
            ->with('subcategory:category_id,category_name')
            ->with(['images' => function ($query) {
                $query->where('image_type', '!=', 1);
            }])
            ->with('savedItem')
            ->where('product_id', $id)
            ->firstOrFail();
    }

    /**
     * Search product
     *
     * @param string $keyWord
     * @return array
     */
    public function search($keyWord)
    {
        return SearchProduct::with($keyWord)->paginate(10)->get();
    }

    /**
     * Search products with filters.
     *
     * @param \Illuminate\Http\Request $filters
     * @return array
     */
    public function filter($filters)
    {
        $shouldFilters = $filters->only(['category_name', 'brand_name',]);
        $mustFilters = $filters->only(['category_id', 'brand_id',]);
        $search = SearchProduct::query();

        $this->applyShouldFilter($search, $shouldFilters);
        $this->applyMustFilter($search, $mustFilters);

        if ($filters->has('product_name')) {
            $search->should('product_name', $filters->product_name);
            event(new Searched($filters->product_name));
        }

        if ($filters->has(['price_min', 'price_max'])) {
            $search->range('pdt_mrp', $filters->price_min, $filters->price_max);
        }

        if ($filters->has('sort')) {
            $this->applySort($search, $filters->sort);
        }

        $search->aggregate('max_price', 'max', 'pdt_mrp')
            ->aggregate('brand_names', 'terms', 'brand_name.keyword')
            ->aggregate('category_names', 'terms', 'category_name.keyword');

        $results = $search->paginate(10)->get();
        $results['data'] = $this->resolveImageUrl($results['data']);
        return $results;
    }

    /**
     * Apply sort filter to ES Query
     *
     * @param \App\Domains\Search\SearchProduct $search
     * @param integer $sortCase
     * @return void
     */
    protected function applySort(SearchProduct $search, int $sortCase)
    {
        switch ($sortCase) {
            case self::POPULAR:
                $search->sort('popular', 'desc');
                break;
            case self::PRICE_HIGH_TO_LOW:
                $search->sort('pdt_mrp', 'desc');
                break;
            case self::PRICE_LOW_TO_HIGH:
                $search->sort('pdt_mrp', 'asc');
                break;
            default:
                break;
        }
    }

    /**
     * Apply Should filter to ES Query.
     *
     * @param \App\Domains\Search\SearchProduct $search
     * @param array $filters
     * @return void
     */
    protected function applyShouldFilter($search, $filters)
    {
        foreach ($filters as $attribute => $filterValue) {
            $values = explode(',', $filterValue);
            $search->mustShould($attribute, $values);
        }
    }

    /**
     * Apply Must filter to ES Query.
     *
     * @param \App\Domains\Search\SearchProduct $search
     * @param array $filters
     * @return void
     */
    protected function applyMustFilter($search, $filters)
    {
        foreach ($filters as $attribute => $value) {
            $search->must($attribute, $value);
        }
    }

    /**
     * Generate and append correct image url to search results
     *
     * @param array $data
     * @return array
     */
    public function resolveImageUrl($data)
    {
        return array_map(function ($item) {
            if ($url = $item['product']['product_image_url']) {
                $item['product']['product_image_url'] = substr($url, 0, 4) === "http"
                    ? $url
                    : config('app.backoffice_url') . $url;
            }
            return $item;
        }, $data);
    }

    /**
     * Get the suggestions for autocomplete
     *
     * @param string $keyWord
     * @return array
     */
    public function getSuggestions($keyWord)
    {
        return Suggestion::getSuggestions(
            get_es_index(),
            'product',
            'product_name',
            $keyWord
        );
    }

    /**
     * Fetch Popular or Featured product.
     *
     * @param \Illuminate\Http\Request $filter
     * @param string $attribute
     * @return array
     */
    private function fetchPopularOrFeatured($filter, $attribute = 'popular')
    {
        $search = SearchProduct::query();
        $search->must($attribute, 1);

        if ($filter->has('sort')) {
            $this->applySort($search, $filter->sort);
        }

        return $search->paginate(10)->get();
    }

    /**
     * Fetch popular products.
     *
     * @param \Illuminate\Http\Request $filter
     * @return array
     */
    public function fetchPopular($filter)
    {
        return $this->fetchPopularOrFeatured($filter);
    }

    /**
     * Fetch featured products.
     *
     * @param \Illuminate\Http\Request $filter
     * @return array
     */
    public function fetchFeatured($filter)
    {
        return $this->fetchPopularOrFeatured($filter, 'featured');
    }
    /**
     * Get products
     *
     * @param [type] $id
     * @return pagination with Data
     */
    public function getItem($request)
    {
        $item = $this->stockItem
            ->whereHas('itemMaster')
            ->with(['itemMaster' => function ($query) {
                $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,cos_nos")
                    ->with(['mainImage' => function ($qry) {
                        $qry->where('image_type', 1)
                            ->select('id', 'product_id', 'image_url', 'image_thumb_url');
                    }]);
            }])
            ->selectRaw($this->getItemFields())
            ->where('fsi_category_id', $request['category_id'])
            ->paginate(10);
        $data = $item->toArray();
        $data['data'] = $this->addFields($data['data'], $request['branch_id']);
        return $data;
    }
    public function getProductsIds($productIds)
    {
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $item = $this->stockItem->whereHas('itemMaster', function($q) use($productIds){
            $q->whereIn('stit_id', $productIds);
            })
            ->with(['itemMaster' => function ($query)  use($productIds,$domain) {
                $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
            stit_GST as selling_prize,isMedicine,
            stit_MRP as mrp,cos_nos")
                    ->with(['mainImage' =>  function ($qry)use($domain){
                        $qry->where('image_type', 1)
                        ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));
                    }]);
             $query->whereIn('stit_id', $productIds);       

            }])
            ->selectRaw($this->getItemFields())
           
            ->get();
        $data = $item->toArray();
        return $data;
    }


    /**
     * Add more fields in getItem function
     *
     * @param array $item
     * @return void
     */
    public function addFields(array $item, $branch_id)
    {
        $products = $this->getProducts($item);
        $stock = Stock::getStock($products['product'], $branch_id);
        $price = Price::findPrice($products['product'], $branch_id);
        $cheap = CheapPrice::getDefault($products['group'], $stock, $price);
        foreach ($item as $key => $itm) {
            $count = count($item[$key]['item_master']);
            for ($i = 0; $i < $count; $i++) {
               /* $stitId = $item[$key]['item_master'][$i]['stit_ID'];
                $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
                $mrp = array_key_exists($stitId, $price['mrp']) ? $price['mrp'][$stitId] : 0;
                $selling_price = array_key_exists($stitId, $price['selling_price']) ? $price['selling_price'][$stitId] : 0;*/

                $stitId = $item[$key]['item_master'][$i]['stit_ID'];
                $cos_nos= $item[$key]['item_master'][$i]['cos_nos'];
                
                $cos_nos = ($cos_nos>0)?$cos_nos:1;

                $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
                $mrp = array_key_exists($stitId, $price['fpod_leastSKUmrp']) ? $price['fpod_leastSKUmrp'][$stitId] *$cos_nos : 0;
               // $order_method= \Session::get('order_method');
                 
              //  $order_method= \Session::get('order_method');
                $order_method= 1;
/*
                if($order_method==1){
                     $selling_price = array_key_exists($stitId, $price['fpod_customerRateCouDel']) ? $price['fpod_customerRateCouDel'][$stitId] *$cos_nos: 0;
                }else{
*/
                   $selling_price = array_key_exists($stitId, $price['fpod_customerRatePikup']) ? $price['fpod_customerRatePikup'][$stitId] *$cos_nos : 0;
/*
                }
*/
                
                $percentage=($mrp>0)?((($mrp - $selling_price)*100) /$mrp):0 ;
                $percentage=round($percentage,2);
                $default_val = in_array($stitId, $cheap) ? 1 : 0;

                 $mrp=round($mrp,2);
                  $selling_price=round($selling_price,2);

                $item[$key]['item_master'][$i]['default_value'] = $default_val;
                $item[$key]['item_master'][$i]['stock_available'] = $stock_count;
                $item[$key]['item_master'][$i]['mrp'] = $mrp;
                $item[$key]['item_master'][$i]['selling_price'] = $selling_price;
                $item[$key]['item_master'][$i]['percentage'] = $percentage;
                $item[$key]['item_master'][$i]['godown_itemId'] = $this->getRand();
            }
        }
        return $item;
    }
    /**
     * Generate random Value
     *
     * @return randum integer value.
     */
    private function getRand()
    {
        return rand(10, 1000);
    }
    /**
     * Get default value
     *
     * @param [type] $id
     * @return 1 or 0
     */
    private function getDefault($id)
    {
        $def = $this->stockItem->where('fsi_def_itemmaster_id', $id)->first();
        return $def ? 1 : 0;
    }
    /**
     * Return stock unique items fields
     *
     * @return Array
     */
    private function getItemFields()
    {
        return
            "fsi_uid,fsi_uid as item_group_id,case isMedicine
        when 'isMedicine>0' then CONCAT(fsi_brand_name,' ',fsi_item_name,' ',fsi_variant)
        else CONCAT(fsi_item_name,' ',fsi_variant)
    end as  item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,isMedicine";

      /*     return
            "fsi_uid,fsi_uid as item_group_id, CONCAT(fsi_item_name,' ',fsi_variant)
         as  item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,isMedicine";*/
    }
    /**
     * Product Details-
     *
     * @param [int] $itemGroup
     * @param [int] $selectedItem
     * @return array
     */
   
    public function productDetails($itemGroup='', $selectedItem='', $possible_keys='', $branch_id='')
    {
        $item = $this->stockItem->whereHas('itemMaster', function($q) use($itemGroup,$possible_keys){
            $q->where('stit_fsiuid', $itemGroup)->whereIn('stit_id', $possible_keys);
            })
            ->with(['itemMaster' => function ($query) use($selectedItem){
                         $query->with(['mainImage' => function ($qry) {
                                    $qry->where('image_type', 1)
                                    ->select('id', 'product_id', 'image_url', 'image_thumb_url');
                                    }])
                            ->with(['additionalImage' => function ($qry) {
                                $qry->where('image_type', 0)
                                ->select('id', 'product_id', 'image_url', 'image_thumb_url');
                            }])
                            ->selectRaw($this->getProductFields($selectedItem));
            }])
            ->selectRaw($this->getItemFields())
            ->first();
            return $this->checkField($item, $branch_id);    
     
    }

     /**
     * select fields in productDetails function 
     *
     * @param [int] $selectedItem
     * @return String
     */
    private function getProductFields($selectedItem)
    {
        return "stit_ID,
                stit_fsiuid,
                stit_quantity as quantity,
                stit_ID as itemId,
                stit_Description as short_description,
                stit_long_description as long_description,
                if(stit_ID = $selectedItem,1,0) as default_value";
    }

    /**
     * 
     */
    private function productFields(array $item, $branch_id, $group_id)
    {   
       $product_id = array_column($item, 'stit_ID');
       $group_product[] = [
           "group" => $group_id,
           "products" => $product_id,
       ];
        $stock = Stock::getStock($product_id, $branch_id);
        $price = Price::findPrice($product_id, $branch_id);
        $cheap = CheapPrice::getDefault($group_product, $stock, $price);
       for ($i = 0; $i < count($item); $i++) {
                $stitId = $item[$i]['stit_ID'];
                $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
                $mrp = array_key_exists($stitId, $price['mrp']) ? $price['mrp'][$stitId] : 0;
                $selling_price = array_key_exists($stitId, $price['selling_price']) ? $price['selling_price'][$stitId] : 0;
                $default_val = in_array($stitId, $cheap) ? 1 : 0;

                $item[$i]['stock_available'] = $stock_count;
                $item[$i]['mrp'] = $mrp;
                $item[$i]['selling_price'] = $selling_price;
                $item[$i]['godown_itemId'] = $this->getRand();
                $item[$i]['default_value'] = $default_val;
           
        }
     return $item;
    }
    /**
     * 
     */
    private function checkField($item, $branch_id)
    {
        $data = $item ? $item->toArray() : [];
        if($data)
        $data['item_master'] = $this->productFields($data['item_master'], $branch_id, $data['fsi_uid']);
        return $data;
    }

    private function getProducts(array $items)
    {
        foreach($items as $Itm)
        {
            $products = $Itm['item_master'];
            $group_id = $Itm['fsi_uid'];
            foreach($products as $product)
            {
                $product_id[] = $product['stit_ID'];
                $group_product[] = $product['stit_ID'];
            }
            $group[] = [
                "group" => $group_id,
                "products" => $group_product
                ];
            unset($group_product);
        }
        return [
            'product' => $product_id,
            'group' => $group,
        ];
       
    }

    
}
