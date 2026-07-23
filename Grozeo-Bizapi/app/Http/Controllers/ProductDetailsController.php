<?php

namespace App\Http\Controllers;

use stdClass;
use App\Models\Category;
use App\Models\HomePage;
use App\Models\Categorys;
use App\Models\ProductBrand;
use App\Models\StockItemMaster;
use App\Models\StockUniqueItem;
use App\Models\MstParentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\Item\Price;
use App\Http\Repositories\Item\Stock;
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\Item\CheapPrice;
use App\Http\Controllers\BrandScreenController;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Repositories\Product\ProductBrandRepository;

class ProductDetailsController extends Controller
{

    protected $uniqueItem;
    protected $parentCategory;
    protected $homePage;
    protected $subcategory;
    protected $productbrands;
    protected $productbrand;
    protected $itemmaster;
    protected $product;
    protected $brandscreen;
    protected $sub;
    public function __construct(StockItemMaster $itemmaster, BrandScreenController $brandscreen, ProductBrandRepository $productbrand, Categorys $sub, StockItemMaster $product, ProductBrand $productbrands, StockUniqueItem $uniqueItem, HomePage $homePage, MstParentCategory $parentCategory, Category $subcategory)
    {

        $this->uniqueItem = $uniqueItem;
        $this->parentCategory = $parentCategory;
        $this->homePage = $homePage;
        $this->subcategory = $subcategory;
        $this->productbrands = $productbrands;
        $this->productbrand = $productbrand;
        $this->itemmaster = $itemmaster;
        $this->product = $product;
        $this->braproduct = $brandscreen;
        $this->sub = $sub;
    }

    public function viewall(Request $request)
    {
        DB::enableQueryLog();

        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;


        $validatedData = $request->validate([
            'id' => 'required'


        ]);

        $type = HomePage::select('type', 'screen')
            ->where('id',  $request['id'])
            ->first();

        /**fetured products */

        if ($type['type'] == "Featured Products"  &&  $type['screen'] == "Home") {

            /*$stock_uniq = $this->itemmaster->where('featured', 1)
                ->select('stit_fsiuid')->get()->toArray();

            $id = array_unique(array_column($stock_uniq, 'stit_fsiuid'));*/

            $item = $this->uniqueItem
                      ->join('finascop_stock_itemmaster as fs', 'fs.stit_fsiuid', 'finascop_stock_uniqueitem.fsi_uid')
                      ->with(['itemMaster' => function ($query) use ($domain) {
                    $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
                        stit_GST as selling_prize,isMedicine,
                        stit_MRP as mrp")
                        ->with(['mainImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 1)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                          }]);
                          $query->where('stit_status',1);
                    }])
                    ->selectRaw($this->getItemFields())
                    ->where('finascop_stock_uniqueitem.isMedicine', 0)
                    ->where("featured", 1)
                    ->where("fs.stit_status", 1)
                     ->groupBy('fsi_uid')
                    ->get();
            return new SuccessWithData($item);
        } else if ($type['type'] == "Featured Products"  &&  $type['screen'] == "Category") {

            $validatedData = $request->validate([

                'request_id' => 'required'

            ]);

            $category_id = $request['request_id'];
            $sub_category_id = array();
            $stock_uniq = $this->itemmaster->where('featured', 1)->select('stit_fsiuid')->get()->toArray();
            $id = array_unique(array_column($stock_uniq, 'stit_fsiuid'));
            $category_id = $this->sub->select('category_id')->where('parent_category', $category_id)->where('isMedicine', 0)->get()->toArray();
            $subcategory_id = $this->subcategory->select('sub_category_id')->whereIn('main_category', array_column($category_id, 'category_id'))->where('isMedicine', 0)->get()->toArray();
            //$product=$this->product->whereIn('product_category',array_column($subcategory_id, 'sub_category_id'))->get();
            $sub_ids = array_column($subcategory_id, 'sub_category_id');

            foreach ($sub_ids as $sub_id) {

                $isexit = $this->product->where('featured', 1)->where('product_category', $sub_id)->get();
                if (count($isexit) > 0) {

                    array_push($sub_category_id, $sub_id);
                }
            }

            $product = array();
            $products = $this->uniqueItem
                ->join('finascop_stock_itemmaster as fs', 'fs.stit_fsiuid', 'finascop_stock_uniqueitem.fsi_uid')
                ->with(['itemMaster' => function ($query) use ($sub_category_id, $domain) {
                    $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
                     stit_GST as selling_prize,
                     stit_MRP as mrp")->whereIn('product_category', $sub_category_id)
                        ->with(['mainImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 1)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }]);
                    $query->where('stit_status',1);
                }])
                ->selectRaw($this->getItemFields())->whereIn('fsi_uid', $id)
                ->where('finascop_stock_uniqueitem.isMedicine', 0)
                ->where("fs.stit_status", 1)
                ->groupBy('fsi_uid')
                ->get()->toArray();

            foreach ($products as $prod) {
                if (count($prod['item_master']) > 0) {
                    array_push($product, $prod);
                }
            }
            return new SuccessWithData($product);
        }
        /** popular products*/

        if ($type['type'] == "Popular products"  &&  $type['screen'] == "Home") {

           /* $stock_uniq = $this->itemmaster->where('popular', 1)->select('stit_fsiuid')->get()->toArray();

            $id = array_unique(array_column($stock_uniq, 'stit_fsiuid'));*/

            $item = $this->uniqueItem
                    ->join('finascop_stock_itemmaster as fs', 'fs.stit_fsiuid', 'finascop_stock_uniqueitem.fsi_uid')
           //     ->whereHas('itemMaster')
                ->with(['itemMaster' => function ($query) use ($domain) {
                    $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
            stit_GST as selling_prize,isMedicine,
            stit_MRP as mrp")
                        ->with(['mainImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 1)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }]);
                    $query->where('stit_status',1);
                }])
                ->selectRaw($this->getItemFields())
                ->where("popular", 1)          
                //->whereIn('fsi_uid', $id)
                ->where("fs.stit_status", 1)
                ->where('finascop_stock_uniqueitem.isMedicine', 0)
                 ->groupBy('fsi_uid')
                ->get();
            return new SuccessWithData($item);
        }
        /**brand */

        if ($type['type'] == "Brand"  &&  $type['screen'] == "Home") {

            $brand = $this->productbrands->select(["brand_id","brand_name","img_url"])->where("top_brand",1)->get();
            //$brand = $this->productbrands->get();

            return new SuccessWithData($brand);
        } else if ($type['type'] == "Brand"  &&  $type['screen'] == "Category") {

            $validatedData = $request->validate([

                'request_id' => 'required'

            ]);

            $brand = $this->productbrand->getdetailWithCategory($request['request_id']);
            return new SuccessWithData($brand);
        } else if ($type['type'] == "product"  &&  $type['screen'] == "Brand") {
            $validatedData = $request->validate([

                'request_id' => 'required'

            ]);


            $item = $this->uniqueItem
                ->join('finascop_stock_itemmaster as fs', 'fs.stit_fsiuid', 'finascop_stock_uniqueitem.fsi_uid')
                //->whereHas('itemMaster')
                ->with(['itemMaster' => function ($query) use ($domain) {
                    $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
                stit_GST as selling_prize,stit_MRP as mrp,isMedicine,cos_nos")
                        ->with(['mainImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 1)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }]);
                    $query->where('stit_status',1);
                }])
                ->select($this->getItemFieldbrand())->where('fsi_brand_id', $request['request_id'])
                // ->take(9)
                ->where("fs.stit_status", 1)
                ->groupBy('fsi_uid')
                ->get();


            return new SuccessWithData($this->checkField($item, $request['request_id']));
        }

        /**browse by category */

        if ($type['type'] == "SubCategory"  &&  $type['screen'] == "Category") {

            $validatedData = $request->validate([

                'request_id' => 'required'

            ]);

            $data = $this->subcategory->where('status', "1")
                ->where('main_category',  $request['request_id'])
                ->get();
            return new SuccessWithData($data);
        }
    }


   

    /**
     * viewall for web API
     */
    public function webviewall(Request $request)
    {
      
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $validatedData = $request->validate([
            'id' => 'required'
        ]);

        $type = HomePage::select('type', 'screen')
            ->where('id',  $request['id'])
            ->first();

        /**fetured products */

        if ($type['type'] == "Featured Products"  &&  $type['screen'] == "Home") {

            $stock_uniq = $this->itemmaster->where('featured', 1)
                ->select('stit_fsiuid')->get()->toArray();

            $id = array_unique(array_column($stock_uniq, 'stit_fsiuid'));

            if(count($request['filter']['category']) !== 0){
                $category_id = array();
                $category_id = $request['filter']['category'];
                
                $sub_category_id = array();
                $category_id = $this->sub->select('category_id')->whereIn('parent_category', $category_id)->get()->toArray();
               
                $subcategory_id = $this->subcategory->select('sub_category_id')->whereIn('main_category', array_column($category_id, 'category_id'))->get()->toArray();
                
    
                $sub_ids = array_column($subcategory_id, 'sub_category_id');
    
                foreach ($sub_ids as $sub_id) {
    
                    $isexit = $this->product->where('featured', 1)->where('product_category', $sub_id)->get();
                    if (count($isexit) > 0) {
    
                        array_push($sub_category_id, $sub_id);
                    }
                }
            }

            $query = $this->uniqueItem->query();

            if(count($request['filter']['category']) !== 0){

                $query->whereHas('itemMaster', function ($query) use ($sub_category_id) {
                    $query->whereIn('product_category', $sub_category_id);
                });
            }
                $query->with(['itemMaster' => function ($query) use ($domain) {
                    $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
        stit_GST as selling_prize,isMedicine,
        stit_MRP as mrp,cos_nos")
                        ->with(['mainImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 1)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }]);
                }])
                ->selectRaw($this->getItemFields())->whereIn('fsi_uid', $id)->where('isMedicine', 0);
                if (count($request['filter']['brands']) !== 0) {

                    $query->whereIn('fsi_brand_id', $request['filter']['brands']);
                }
                $product = $query->get();
                
              $data =$this->sortFilter($product,$request);

            return new SuccessWithData($data);

        } else if ($type['type'] == "Featured Products"  &&  $type['screen'] == "Category") {

            $validatedData = $request->validate([

                'requested_id' => 'required'

            ]);

            if(count($request['filter']['category']) == 0){
                $category_id = array($request['requested_id']);
                
            }else{
                $category_id = $request['filter']['category'];
            }
           
            $sub_category_id = array();
            $stock_uniq = $this->itemmaster->where('featured', 1)->select('stit_fsiuid')->get()->toArray();
            $id = array_unique(array_column($stock_uniq, 'stit_fsiuid'));
            $category_id = $this->sub->select('category_id')->whereIn('parent_category', $category_id)->where('isMedicine', 0)->get()->toArray();
            $subcategory_id = $this->subcategory->select('sub_category_id')->whereIn('main_category', array_column($category_id, 'category_id'))->where('isMedicine', 0)->get()->toArray();
          
            $sub_ids = array_column($subcategory_id, 'sub_category_id');

            foreach ($sub_ids as $sub_id) {

                $isexit = $this->product->where('featured', 1)->where('product_category', $sub_id)->get();
                if (count($isexit) > 0) {

                    array_push($sub_category_id, $sub_id);
                }
            }

            $product = array();
            $query = $this->uniqueItem->query();
            $query->whereHas('itemMaster', function ($query) use ($sub_category_id) {
                $query->whereIn('product_category', $sub_category_id);
        })
                ->with(['itemMaster' => function ($query) use ($sub_category_id, $domain) {
                    $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
                     stit_GST as selling_prize,
                     stit_MRP as mrp")->whereIn('product_category', $sub_category_id)
                        ->with(['mainImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 1)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }]);
                }])
                ->selectRaw($this->getItemFields())->whereIn('fsi_uid', $id)
                ->where('isMedicine', 0);
               
                if (count($request['filter']['brands']) !== 0) {

                    $query->whereIn('fsi_brand_id', $request['filter']['brands']);
                }
                $product =$query->get();
                
                $data =$this->sortFilter($product,$request);

                return new SuccessWithData($data);
        }
        /** popular products*/

        if ($type['type'] == "Popular products"  &&  $type['screen'] == "Home") {

            $stock_uniq = $this->itemmaster->where('popular', 1)->select('stit_fsiuid')->get()->toArray();

            $id = array_unique(array_column($stock_uniq, 'stit_fsiuid'));
            
            if(count($request['filter']['category']) !== 0){
                $category_id = array();
                $category_id = $request['filter']['category'];
                
                $sub_category_id = array();
                $category_id = $this->sub->select('category_id')->whereIn('parent_category', $category_id)->get()->toArray();
               
                $subcategory_id = $this->subcategory->select('sub_category_id')->whereIn('main_category', array_column($category_id, 'category_id'))->get()->toArray();
                
    
                $sub_ids = array_column($subcategory_id, 'sub_category_id');
    
                foreach ($sub_ids as $sub_id) {
    
                    $isexit = $this->product->where('featured', 1)->where('product_category', $sub_id)->get();
                    if (count($isexit) > 0) {
    
                        array_push($sub_category_id, $sub_id);
                    }
                }
            }
            $query = $this->uniqueItem->query();
            if(count($request['filter']['category']) !== 0){

                $query->whereHas('itemMaster', function ($query) use ($sub_category_id) {
                    $query->whereIn('product_category', $sub_category_id);
                });
            } 
                $query->with(['itemMaster' => function ($query) use ($domain) {
                    $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
            stit_GST as selling_prize,isMedicine,
            stit_MRP as mrp")
                        ->with(['mainImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 1)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }]);
                }])
                ->selectRaw($this->getItemFields())->whereIn('fsi_uid', $id)
                ->where('isMedicine', 0);
                if (count($request['filter']['brands']) !== 0) {

                    $query->whereIn('fsi_brand_id', $request['filter']['brands']);
                }
                $product = $query->get();

                $data =$this->sortFilter($product,$request);

                return new SuccessWithData($data);
        }
        /**brand */

        if ($type['type'] == "Brand"  &&  $type['screen'] == "Home") {

            $brand = $this->productbrands->get();

            return new SuccessWithData($brand);
        } else if ($type['type'] == "Brand"  &&  $type['screen'] == "Category") {

            $validatedData = $request->validate([

                'request_id' => 'required'

            ]);

            $brand = $this->productbrand->getdetailWithCategory($request['request_id']);

            return new SuccessWithData($brand);

        } else if ($type['type'] == "product"  &&  $type['screen'] == "Brand") {

            $validatedData = $request->validate([

                'request_id' => 'nullable'

            ]);
        if(count($request['filter']['brands']) == 0){
            $brand_id = array($request['requested_id']);
           
            
        }else{
            $brand_id = $request['filter']['brands'];
        }
       
        if(count($request['filter']['category']) !== 0){

            $category_id = array();
            $category_id = $request['filter']['category'];
            
            $sub_category_id = array();
            $category_id = $this->sub->select('category_id')->whereIn('parent_category', $category_id)->get()->toArray();
           
            $subcategory_id = $this->subcategory->select('sub_category_id')->whereIn('main_category', array_column($category_id, 'category_id'))->get()->toArray();
            

            $sub_ids = array_column($subcategory_id, 'sub_category_id');

            foreach ($sub_ids as $sub_id) {

                $isexit = $this->product->where('featured', 1)->where('product_category', $sub_id)->get();
                if (count($isexit) > 0) {

                    array_push($sub_category_id, $sub_id);
                }
            }
        }
        

            $query = $this->uniqueItem->query();
            if(count($request['filter']['category']) !== 0){

                $query->whereHas('itemMaster', function ($query) use ($sub_category_id) {
                    $query->whereIn('product_category', $sub_category_id);
                });
    
                }
                $query->with(['itemMaster' => function ($query) use ($domain) {
                    $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
                stit_GST as selling_prize,stit_MRP as mrp,isMedicine")
                        ->with(['mainImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 1)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }]);
                }])
                ->select($this->getItemFieldbrand())->whereIn('fsi_brand_id', $request['request_id']);
                $product =$query->get();


                $data =$this->sortFilter($product,$request);

                return new SuccessWithData($data);
        }

        /**browse by category */

        if ($type['type'] == "SubCategory"  &&  $type['screen'] == "Category") {

            $validatedData = $request->validate([

                'request_id' => 'required'

            ]);

            $data = $this->subcategory->where('status', "1")
                ->where('main_category',  $request['request_id'])
                ->get();

            return new SuccessWithData($data);
        }
    }
    private function sortFilter($product,$request)
    {
        $product = $this->checkField($product, $request['branch_id']);
        $product =collect($product);
     
        if ($request['sort']['price'] == 1) {
             
               
            $product = $product->sortBy(function ($products, $key) {
                return (int) $products['item_master'][0]['selling_price'];
            });
        }
        if ($request['sort']['price'] == 2) {
           
            $product = $product->sortByDesc(function ($products, $key) {
                return (int) $products['item_master'][0]['selling_price'];
            });
        }
       
        if (count($request['filter']['price_range']) !== 0) {
        
           
            $product = $product->filter(function (&$item) use($request) {
               
                $item['item_master'] = collect($item['item_master'])
                    ->whereBetween('selling_prize', [$request['filter']['price_range'][0], $request['filter']['price_range'][1]]);
                return count($item['item_master']) ? true : false;
                
             })->map(function (&$item) use($request) {
                $item['item_master'] = collect($item['item_master'])
                    ->whereBetween('selling_prize', [$request['filter']['price_range'][0], $request['filter']['price_range'][1]])->values();
                return $item;
             });
           
        
        
          
        }
   
    $data=$this->paginationpage($product,$request);

    return $data; 
    }
    private function paginationpage($product,$request)
    {
        $count = count($product);

        $productCollection = $product;


        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // Create a new Laravel collection from the array data
        //$productCollection = collect($pagenate);


        // Define how many products we want to be visible in each page
        $perPage = 10;

        // Slice the collection to get the products to display in current page
        $currentPageproducts = $productCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();

        // Create our paginator and pass it to the view
        $paginatedproducts = new LengthAwarePaginator($currentPageproducts, count($productCollection), $perPage);

        // set url path for generted links
        $paginatedproducts->setPath($request->url());

        $data = $paginatedproducts->toArray();

        $secondaryArray = array();
        foreach ($data['data'] as $key => $value) {

            $secondaryArray[] = $value;
        }

        //$secondaryArray['currentpage']=$data['current_page'];

        $std = new stdClass();
        $std->Product_details = $secondaryArray;
        $std->currentpage = $data['current_page'];
        $std->first_page_url = $data['first_page_url'];
        $std->from = $data['from'];
        $std->last_page = $data['last_page'];
        $std->last_page_url = $data['last_page_url'];
        $std->next_page_url = $data['next_page_url'];
        $std->path = $data['path'];
        $std->per_page = $data['per_page'];
        $std->prev_page_url = $data['prev_page_url'];
        $std->to = $data['to'];
        $std->total = $data['total'];

        return $std; 
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
    private function getItemFieldbrand()
    {
        return [
            "fsi_uid",
            "fsi_uid as item_group_id",
            "fsi_item_name as item_name",
            "fsi_brand_name as brand_name",
            "fsi_category_id as category_id",
            "fsi_categry_name as category_name",
            "fsi_variant as variant",
            "isMedicine"
        ];
    }


    private function getItemFields()
    {
        return
            "fsi_uid,fsi_uid as item_group_id,CONCAT(fsi_brand_name,' ',fsi_item_name) as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,finascop_stock_uniqueitem.isMedicine";
    }

    private function checkField($item, $branch_id)
    {

        return $item ? $this->addFields($item->toArray(), $branch_id) : [];
    }
    private function addFields(array $item, $branch_id)
    {

        $products = $this->getHomeProducts($item);
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
             
                $default_val = in_array($stitId, $cheap) ? 1 : 0;

                  $mrp=round($mrp,2);

                 $selling_price=round($selling_price,2);
                 $percentage=round($percentage,2);

                $item[$key]['item_master'][$i]['default_value'] = $default_val;
                $item[$key]['item_master'][$i]['stock_available'] = $stock_count;
                $item[$key]['item_master'][$i]['mrp'] = $mrp;
                $item[$key]['item_master'][$i]['selling_prize'] = $selling_price;
                $item[$key]['item_master'][$i]['selling_price'] = $selling_price;
                $item[$key]['item_master'][$i]['percentage'] = $percentage;
                $item[$key]['item_master'][$i]['godown_itemId'] = $this->getRand();
            }
        }
        return $item;
    }

    private function getHomeProducts(array $items)
    {

        $group_id = array();
        $group = array();
        $group_product = array();
        $product_id = array();
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



    public function allbrands(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required',
            'category_id' => 'nullable'

        ]);

        $type = HomePage::select('type', 'screen')
            ->where('id',  $request['id'])
            ->first();


        if ($type['type'] == "Brand"  &&  $type['screen'] == "Home") {

            $brand = $this->productbrands->get();

            return new SuccessWithData($brand);
        } else if ($type['type'] == "Brand"  &&  $type['screen'] == "Category") {

            $brand = $this->productbrand->getdetailWithCategory($request['category_id']);

            return new SuccessWithData($brand);
        }
    }

    public function popularproductslist(Request $request)
    {
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $validatedData = $request->validate([
            'id' => 'required',
            'category_id' => 'nullable'

        ]);

        $type = HomePage::select('type', 'screen')
            ->where('id',  $request['id'])
            ->first();

        if ($type['type'] == "Popular products"  &&  $type['screen'] == "Home") {

            $stock_uniq = $this->itemmaster->where('popular', 1)->select('stit_fsiuid')->get()->toArray();

            $id = array_unique(array_column($stock_uniq, 'stit_fsiuid'));

            $item = $this->uniqueItem
                ->whereHas('itemMaster')
                ->with(['itemMaster' => function ($query) use ($domain) {
                    $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
            stit_GST as selling_prize,isMedicine,
            stit_MRP as mrp")
                        ->with(['mainImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 1)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }]);
                }])
                ->selectRaw($this->getItemFields())->whereIn('fsi_uid', $id)
                ->where('isMedicine', 0)

                ->get();

            return new SuccessWithData($item);
        }
    }


    public function browsebyCategory(Request $request)
    {

        $validatedData = $request->validate([
            'id' => 'required',
            'category_id' => 'nullable'

        ]);

        $type = HomePage::select('type', 'screen')
            ->where('id',  $request['id'])
            ->first();
        if ($type['type'] == "SubCategory"  &&  $type['screen'] == "Category") {
            $data = $this->subcategory->where('status', "1")
                ->where('main_category',  $request['id'])
                ->get();
        }
        return new SuccessWithData($data);
    }
    public function Grouppricerange(Request $request){
        $pricerange = DB::select('SELECT MIN(bi.fpod_customerRatePikup) AS minPrice, MAX(bi.fpod_customerRatePikup) AS maxprice, stit_itemId FROM finascop_stock_branch_inventory bi 
INNER JOIN finascop_stock_itemmaster fs ON fs.stit_id=bi.stit_id  WHERE fs.stit_itemId in('. $request['ids'] .') AND fpod_customerRatePikup > 0 AND fs.stit_status =1 GROUP BY stit_itemId');

        return new SuccessWithData($pricerange);
    }

}
