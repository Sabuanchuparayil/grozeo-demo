<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Categorys;
use App\Models\MedicineMaster;
use App\Models\StockItemMaster;
use App\Models\StockUniqueItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\Item\Price;
use App\Http\Repositories\Item\Stock;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\ErrorWithData;
use App\Http\Responses\ErrorResponse;
use App\Http\Repositories\Item\CheapPrice;
use App\Http\Requests\Product\ProductCategory;

use App\Http\Repositories\Item\ItemMasterCollection;
use Illuminate\Support\Collection;

class ProductController extends Controller
{

    private $stockItem;
    private $itemMaster;
    private $medinicemaster;
    protected $subcategory;
    protected $sub;
    protected $product;
    protected $uniqueItem;
    protected $_itemMasterCollection;

    public function __construct(StockUniqueItem $stockItem, StockUniqueItem $uniqueItem, Category $subcategory, Categorys $sub, StockItemMaster $product, StockItemMaster $itemMaster, MedicineMaster $medinicemaster, ItemMasterCollection $itemMasterCollection)
    {

        $this->stockItem = $stockItem;
        $this->itemMaster = $itemMaster;
        $this->medinicemaster = $medinicemaster;
        $this->subcategory = $subcategory;
        $this->sub = $sub;
        $this->product = $product;
        $this->uniqueItem = $uniqueItem;
        $this->_itemMasterCollection = $itemMasterCollection;
    }

    public function productDetails(Request $request)
    {
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $itemGroup = $request->get('item_group');
        $selectedItem = $request->get('item');
        $possible_keys = $request->get('possible_keys');
        $branch_id = $request->get('branch_id');
        $branchtypeid = $request->get('branch_type_id')??1;
        $stit_id = $selectedItem;
        if(isset($request->stit_ID))
        {
            $stit_id=$request->stit_ID;
        }
        $collection = collect();
        $collectnPush = ['stit_id' => $stit_id];
        if(isset($branch_id) && $branch_id > 0)
        {
            $collectnPush['br_ID'] = $branch_id;
        }
        $collection->push($collectnPush);
        $item = $this->_itemMasterCollection->getProducts($collection, 0, null, 1)->get()->first();
        
        if($item)
        {
            if($item["isMedicine"] == 0)
            {
                $item["item_name"] = (str_contains(strtolower($item["item_name"]), strtolower($item["brand_name"]))) ? $item["item_name"] : $item["brand_name"]." ".$item["item_name"];
            }
            if($item['stit_custInitiate'] == 1)
            {
                $item['return_details'] = 'Return possible only while accepting delivery';
            }
            if($item['stit_itemReturnTime'] > 0)
            {
                $item['return_details'] = 'Return possible within '.$item['stit_itemReturnTime'].' Days';
            }
            if(($item['stit_custInitiate'] == 0) && ($item['stit_itemReturnTime'] <= 0))
            {
                $item['return_details'] = 'Not Returnable Product';
            }
            return new SuccessWithData($item);
        }
        else
        {
            return new ErrorWithData('Error', 'Product not found in this store.');
        }
    }

    public function getSimilarLikeProducts(Request $request, $type='similar')
    {
        try
        {
            $selectedItem = $request->get('item');
            $branch_id = $request->get('branch_id');
            $subCatId = $request->get('sub_category_id');

            if((!isset($selectedItem) || $selectedItem < 1) && (isset($request->stit_ID) && $request->stit_ID > 0))
            {
                $selectedItem = $request->stit_ID;
            }
            $collection = collect();
            $collection->push(['stit_id' => $selectedItem]);
            if(!isset($subCatId) || $subCatId < 1)
            {
                $item = $this->_itemMasterCollection->getProducts($collection, 0, null, 1)->get()->first();
                $subCatId = @$item['sub_category_id'];
            }
            $data = [];
            $productList = [];
            if(isset($subCatId))
            {
                $products = $this->_itemMasterCollection->getProducts(null, 0, null, 1)->where('sub_category_id', $subCatId)->where('stit_id' , '<>', $selectedItem)->get();
                if(isset($products))
                {
                    $productList = $products->toArray();
                }
                if(count($productList) < 20)
                {
                    $categoryId = $this->subcategory->select('main_category')->where('sub_category_id', $subCatId)->value('main_category');
                    $subcategoryIds = $this->subcategory->select('sub_category_id')->where('main_category', $categoryId)->where('sub_category_id', '<>', $subCatId)->get()->pluck('sub_category_id');
                    $products = $this->_itemMasterCollection->getProducts(null, 0, null, 1)->whereIn('sub_category_id', $subcategoryIds)->get();
                    if(isset($products) && count($products) > 0)
                    {
                        $productList = array_merge($productList, $products->toArray());
                    }
                    if (count($productList) < 20)
                    {
                        $parentCategoryId = $this->sub->select('parent_category')->where('category_id', $categoryId)->value('parent_category');
                        $categoryIds = $this->sub->select('category_id')->where('parent_category', $parentCategoryId)->where('category_id', '<>', $categoryId)->get()->pluck('category_id')->toArray();
                        $subcategoryIds = $this->subcategory->select('sub_category_id')->whereIn('main_category', $categoryIds)->get()->pluck('sub_category_id')->toArray();

                        $products = $this->_itemMasterCollection->getProducts(null, 0, null, 1)->whereIn('sub_category_id', $subcategoryIds)->get();
                        if(isset($products) && count($products) > 0)
                        {
                            $productList = array_merge($productList, $products->toArray());
                        }
                    }
                }

                if(isset($productList) && count($productList) > 0)
                {
                    $similarproducts = array_chunk(array_filter($productList), 10);
                    if($type == 'similar')
                    {
                        $data = (@$similarproducts[0]) ? $similarproducts[0] : [];
                    }
                    if($type == 'like')
                    {
                        $data = (@$similarproducts[1]) ? $similarproducts[1] : [];
                    }
                }
            }
            return new SuccessWithData($data);
        }
        catch (\Exception $e)
        {
            return new ErrorResponse('Operation failed');
        }
    }

    public function getGroupedProducts(Request $request)
    {
        try
        {
            $groupID = $request->get('group_id');
            $selectedItem = $request->get('stit_ID');
            $collection = collect();
            $collection->push(['variantGroupId' => $groupID]);

            if($groupID > 0)
            {
                $product = $this->_itemMasterCollection->getProducts($collection, 0, null, 1)->get();
                return new SuccessWithData($product);
            }
            return new ErrorResponse('Unable to get products');
        }
        catch (\Exception $e)
        {
            // info("ProductController getGroupedProducts ERROR => ".$e->getMessage());
            return new ErrorResponse('Unable to get products');
        }
    }

    public function getOtherProducts(Request $request)
    {
        try
        {
            $validatedData = $request->validate([
                'product_id' => 'required|integer|exists:finascop_stock_itemmaster,stit_ID'
            ]);
            $collection = collect();
            $collection->push(['stit_id' => $request->product_id]);

            $item = $this->_itemMasterCollection->getProducts($collection, 0, null, 1, -1, -1, 0)->first();
            if($item)
            {
                if($item['branch_type_id'] == 0)
                {
                    return new ErrorResponse('Product not found in other stores.');
                }

                if($item["isMedicine"] == 0)
                {
                    $item["item_name"] = (str_contains(strtolower($item["item_name"]), strtolower($item["brand_name"]))) ? $item["item_name"] : $item["brand_name"]." ".$item["item_name"];
                }
                if($item['stit_custInitiate'] == 1)
                {
                    $item['return_details'] = 'Return possible only while accepting delivery';
                }
                if($item['stit_itemReturnTime'] > 0)
                {
                    $item['return_details'] = 'Return possible within '.$item['stit_itemReturnTime'].' Days';
                }
                if(($item['stit_custInitiate'] == 0) && ($item['stit_itemReturnTime'] <= 0))
                {
                    $item['return_details'] = 'Not Returnable Product';
                }
                return new SuccessWithData($item);
            }
            else
            {
                return new ErrorResponse('Product not found in other stores.');
            }
        }
        catch (\Exception $e)
        {
            // info("ProductController getOtherProducts ERROR");info($e);
            return new ErrorResponse('Unable to get products');
        }
    }


    private function checkField($item, $branch_id,$stit_id=0, $branchtypeid)
    {
        //return $item ? $this->addFields($item->toArray(), $branch_id) : [];
        $data = $item ? $item->toArray() : [];
        if ($data)
            $data['item_master'] = $this->productFields($data['item_master'], $branch_id, $data['fsi_uid'],$stit_id, $branchtypeid);
        // dd($data['item_master']);
        return $data;
    }



    private function productFields(array $item, $branch_id, $group_id,$stit_id, $branchtypeid)
    {

        $product_id = array_column($item, 'stit_ID');
        $group_product[] = [
            "group" => $group_id,
            "products" => $product_id,
        ];
        //dd($product_id);
        $stock = Stock::getStock($product_id, $branch_id, $branchtypeid);
        $price = Price::findPrice($product_id, $branch_id, $branchtypeid);
        $cheap = CheapPrice::getDefault($group_product, $stock, $price);
        for ($i = 0; $i < count($item); $i++) {

            /*$stitId = $item[$i]['stit_ID'];
            $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
            $mrp = array_key_exists($stitId, $price['mrp']) ? $price['mrp'][$stitId] : 0;
            $selling_price = array_key_exists($stitId, $price['selling_price']) ? $price['selling_price'][$stitId] : 0;*/

             $stitId = $item[$i]['stit_ID'];
                $cos_nos= $item[$i]['cos_nos']??1;
                if($cos_nos <=0)
                    $cos_nos =1;
                $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
                $mrp = array_key_exists($stitId, $price['fpod_leastSKUmrp']) ? $price['fpod_leastSKUmrp'][$stitId] * ($cos_nos<1 ? 1 : $cos_nos) : 0;
              //  $order_method= \Session::get('order_method');
              //  $order_method= \Session::get('order_method');
                $order_method= 1;
            /*
            if($branchtypeid == 3)
                $selling_price = array_key_exists($stitId, $price['fpod_customerRateHmDel']) ? $price['fpod_customerRateHmDel'][$stitId] *$cos_nos: 0;
            else
                $selling_price = array_key_exists($stitId, $price['fpod_customerRateCouDel']) ? $price['fpod_customerRateCouDel'][$stitId] *$cos_nos: 0;
            */

                //if($order_method==1){
                //     $selling_price = array_key_exists($stitId, $price['fpod_customerRateCouDel']) ? $price['fpod_customerRateCouDel'][$stitId] *$cos_nos: 0;
                //}else{
                   $selling_price = array_key_exists($stitId, $price['fpod_customerRateHmDel']) ? $price['fpod_customerRateHmDel'][$stitId] *$cos_nos : 0;  
                //}
                
               
             $percentage=($mrp>0)?((($mrp - $selling_price)*100) /$mrp):0 ;
            $percentage=round($percentage,2);
            if($stit_id>0){
                $default_val = ($stitId==$stit_id)?1:0;
            }else{
                $default_val = in_array($stitId, $cheap) ? 1 : 0;
            }

         

             $mrp=round($mrp,2);

            $selling_price=round($selling_price,2);
            $item[$i]['stock_available'] = $stock_count;
            $item[$i]['mrp'] = $mrp;
            $item[$i]['selling_price'] = $selling_price;
            $item[$i]['godown_itemId'] = $this->getRand();
            $item[$i]['default_value'] = $default_val;
             $item[$i]['percentage'] = $percentage;
        }
        // dd($item);
        return $item;
    }



    public function getItem(ProductCategory $request)
    {
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disk4s.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        //$this->product->getItem($request->validated())
        // if ($request['type'] == 1) {
        $item = $this->stockItem
         //   ->join('finascop_stock_itemmaster as fs', 'fs.stit_fsiuid', 'finascop_stock_uniqueitem.fsi_uid')
        //    ->whereHas('itemMaster')
            ->with(['itemMaster' => function ($query) use ($domain) {
                $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,cos_nos")
                    ->with(['mainImage' => function ($qry) use ($domain) {
                        $qry->where('image_type', 1)
                            ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                    }]);
            }])
            ->selectRaw($this->getItemFields())
            ->where('fsi_category_id', $request['category_id'])
            ->where('fsi_count','>', 0)
           // ->groupBy('fsi_uid')

            ->paginate(10);
        $data = $item->toArray();
        //$data['data'] = $this->addFields($data['data'], $request['branch_id']);
        $data['data'] = $this->addFields($data['data'], $request['branch_id'], $request['branch_type_id']??1);


        return new SuccessWithData(
            $data['data']

        );
    }



    /**
     * Add more fields in getItem function
     *
     * @param array $item
     * @return void
     */

    private function addFields(array $item, $branch_id, $branchtypeid=1)
    {
        $products = $this->getHomeProducts($item);
        $stock = Stock::getStock($products['product'], $branch_id, $branchtypeid);
        $price = Price::findPrice($products['product'], $branch_id, $branchtypeid);
        $cheap = CheapPrice::getDefault($products['group'], $stock, $price);
        foreach ($item as $key => $itm) {
            $count = count($item[$key]['item_master']);
            for ($i = 0; $i < $count; $i++) {
               /* $stitId = $item[$key]['item_master'][$i]['stit_ID'];
                $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
                $mrp = array_key_exists($stitId, $price['mrp']) ? $price['mrp'][$stitId] : 0;
                $selling_price = array_key_exists($stitId, $price['selling_price']) ? $price['selling_price'][$stitId] : 0;
                */
                 $stitId = $item[$key]['item_master'][$i]['stit_ID'];
                $cos_nos= $item[$key]['item_master'][$i]['cos_nos'];
                
                $cos_nos = ($cos_nos>0)?$cos_nos:1;

                $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
                $mrp = array_key_exists($stitId, $price['fpod_leastSKUmrp']) ? $price['fpod_leastSKUmrp'][$stitId] *$cos_nos : 0;
          //      $order_method= \Session::get('order_method');
                 
              //  $order_method= \Session::get('order_method');
                $order_method= 1;
                /*
                if($order_method==1){
                     $selling_price = array_key_exists($stitId, $price['fpod_customerRateCouDel']) ? $price['fpod_customerRateCouDel'][$stitId] *$cos_nos: 0;
                }else{
                */
                   $selling_price = array_key_exists($stitId, $price['fpod_customerRateHmDel']) ? $price['fpod_customerRateHmDel'][$stitId] *$cos_nos : 0;
                /*
                }
                */                
                $percentage=($mrp>0)?((($mrp - $selling_price)*100) /$mrp):0 ;
               $percentage=round($percentage,2);
                $mrp=round($mrp,2);
                $selling_price=round($selling_price,2);

                
                $default_val = in_array($stitId, $cheap) ? 1 : 0;

                $item[$key]['item_master'][$i]['default_value'] = $default_val;
                $item[$key]['item_master'][$i]['stock_available'] = $stock_count;
                $item[$key]['item_master'][$i]['mrp'] = $mrp;
                $item[$key]['item_master'][$i]['selling_prize'] = $selling_price;
                $item[$key]['item_master'][$i]['selling_price'] = $selling_price;
                $item[$key]['item_master'][$i]['godown_itemId'] = $this->getRand();
                $item[$key]['item_master'][$i]['percentage'] = $percentage;

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
     * Undocumented function
     *
     * @param array $items
     * @return void
     */
    private function getProducts(array $items)
    {
        $product_id = array();
        $group = array();
        foreach ($items as $Itm) {
            $products = $Itm['item_master'];
            $group_id = $Itm['fsi_uid'];
            foreach ($products as $product) {
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
                stit_item_volume + 0E0 as stock_available,
                stit_GST as selling_prize,
                stit_MRP as mrp,
                stit_displaylabel as displaylabel,
                stit_long_description as long_description,
                cos_nos,
                stit_SKU as item_name,
                if(stit_ID = $selectedItem,1,0) as default_value,
                stgp_groupID,variantGroupId";
                //   stit_long_description as long_description,
    }

    /**
     * Return stock unique items fields
     *
     * @return Array
     */
    private function getItemFields()
    {
        /*return
            "fsi_uid,fsi_uid as item_group_id,CONCAT(fsi_brand_name,' ',fsi_item_name) as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,isMedicine,fsi_displaylabel";*/

        return
            "fsi_uid,fsi_uid as item_group_id,fsi_item_name as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,finascop_stock_uniqueitem.isMedicine,fsi_displaylabel";    


    }

    public function recentlyviewd(Request $Request)
    {
        DB::enableQueryLog();
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $validatedData = $request->validate([

            'stit_ID' => 'required|array'

        ]);

        $reqired_id = $request['stit_ID'];
        $recenty = array();

        foreach ($reqired_id  as  $stit_id) {

            $id = $this->product->select('stit_fsiuid')->where('stit_ID',  $stit_id)->first();
            $product = $this->stockItem
              //   ->join('finascop_stock_itemmaster as fs', 'fs.stit_fsiuid', 'finascop_stock_uniqueitem.fsi_uid')
                ->with(['itemMaster' => function ($query) use ($domain, $reqired_id, $stit_id) {
                    $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume + 0E0 as stock_available,
         stit_GST as selling_prize,
         stit_MRP as mrp")->where('stit_ID', $stit_id)
                       ->with(['mainImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 1)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }]);
                }])
                ->selectRaw($this->getItemFields())->where('finascop_stock_uniqueitem.isMedicine', 0)
                //->groupBy('fsi_uid')
                ->where('fsi_uid', $id['stit_fsiuid'])
                ->first();
              // dd($product);

            array_push($recenty, $product);
        }

        return new SuccessWithData(
            $recenty
        );
    }
}