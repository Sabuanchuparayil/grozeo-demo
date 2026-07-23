<?php



namespace App\Http\Controllers;

use stdClass;
use App\Models\Adzone;
use App\Models\Category;
use App\Models\HomePage;
use App\Models\StockItemMaster;
use App\Models\StockUniqueItem;
use App\Models\MstParentCategory;
use App\Models\HomepageICanCollect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CheckFilter;
use App\Http\Repositories\Item\Price;
use App\Http\Repositories\Item\Stock;
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\Item\CheapPrice;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Repositories\Product\ProductBrandRepository;
use App\Http\Requests\CategoryScreen\CategoryScreenRequest;
use Illuminate\Support\Collection;
use App\Http\Repositories\Item\ItemMasterCollection;

class InnerSubCategoryScreen extends Controller
{
    protected $parentCategory;
    protected $product;
    protected $subcategory;
    protected $uniqueItem;
    protected $homePage;
    protected $adzone;
    protected $_itemMasterCollection;

    public function __construct(Adzone $adzone, HomepageICanCollect $homepageCollect, HomePage $homePage, ProductBrandRepository $productbrand, StockUniqueItem $uniqueItem, MstParentCategory $parentCategory, StockItemMaster $product, Category $subcategory, ItemMasterCollection $itemMasterCollection)
    {
        $this->parentCategory = $parentCategory;
        $this->product = $product;
        $this->subcategory = $subcategory;
        $this->uniqueItem = $uniqueItem;
        $this->productbrand = $productbrand;
        $this->homePage = $homePage;
        $this->homepageCollect = $homepageCollect;
        $this->adzone = $adzone;
        $this->_itemMasterCollection = $itemMasterCollection;
    }
    public function getdata(CategoryScreenRequest $request)
    {
        DB::enableQueryLog();
        $data = array();
        $homepage = array();

        if ($request['order_method'] == 1 || $request['order_method'] == 2)

            $home = $this->homePage->where('screen', 'InnerSubcategory')->where('is_active', 1)->get();
        /*if ($request['order_method'] == 2)
            $home = $this->homepageCollect->where('screen', 'InnerSubcategory')->where('delivery_type', 1)->where('is_active', 1)->get();
        */
        $data = $this->fetchdata($home, $request);

        return new SuccessWithData(
            $data
        );
    }

    public function fetchdata($home, $request)
    {


        if (!isset($request['page']) || $request['page'] == 1) {

            $check = app(CheckFilter::class)->filterCheck($home, $request);

            if ($check) {
                $home = $this->filter($home, $request);
            } else {
                $home = $this->getactualdata($home, $request);
            }

        } else {


            $home = $this->filter($home, $request);
        }

        return $home;
    }
    public function filter($home, $request)
    {

        $homepage = array();
        foreach ($home as $key => $hom) {

            $type = $hom['type'];
            if ($type == 'product') {
                $data = $this->getProducts($request);

                $home[$key]['value'] = $data[0];
                $home[$key]['total_count'] = $data[1];
                $home[$key]['min_count'] = 50;
                $home[$key]['pagenate_details'] = $data[2];
            } else {
                $home[$key]['value'] = [];
            }
        }

        foreach ($home as $key => $home1) {
            if (count($home1['value']) > 0) {

                array_push($homepage, $home1);
            }
        }


        $home = $homepage;

        return $home;
    }





    private function advertisement($home, $branch_id)
    {

        $datas = $this->adzone->with(['adzone_details' => function ($q) {
            $q->where('adv_enddate', '>=', date('Y-m-d'))->where('adv_status',1);
        }])->where('adzone_screen', $home['screen'])->where('adzone_status', 1)->where('adzone_type', $home['type'])->get();

        $product_data = '';
        if (isset($datas[0]['adzone_details'])) {
            foreach ($datas[0]['adzone_details'] as $key => $data) {
                if ($data['adv_offer'] == 'Product') {

                    $datas[0]['adzone_details'][$key]["details"] = $this->itemmaster->selectRaw($this->getselect())->where('stit_ID', $data['adv_offerValueId'])->first();
                }
                if ($data['adv_offer'] == 'Category') {
                    $datas[0]['adzone_details'][$key]["details"] = array("request_id" => $data['adv_offerValueId']);
                }
                if ($data['adv_offer'] == 'Brand') {
                    $datas[0]['adzone_details'][$key]["details"] = array("request_id" => $data['adv_offerValueId']);
                }

                if ($data['adv_offer'] == 'Offer' && $data['adv_offerType'] == 'Product') {
                    $datas[0]['adzone_details'][$key]["details"] = $this->itemmaster->selectRaw($this->getselect())->where('stit_ID', $data['adv_offerValueId'])->first();
                }

                if ($data['adv_offer'] == 'Offer' && $data['adv_offerType'] == 'Category') {
                    $datas[0]['adzone_details'][$key]["details"] = array("request_id" => $data['adv_offerValueId']);
                }

                if ($data['adv_offer'] == 'Offer' && $data['adv_offerType'] == 'Brand') {
                    $datas[0]['adzone_details'][$key]["details"] = array("request_id" => $data['adv_offerValueId']);
                }
            }
        }


        if (count($datas[0]['adzone_details']) == 0) {
            $home['value'] = [];
        } else {
            $home['value'] = $datas;
        }


        return $home;
    }

    private function smalladvertisement($home, $branch_id)
    {

        //  dd($this->adzone->with('ad')->where('adzone_screen',$home['screen'])->where('adzone_type',$home['type'])->get()->toArray());
        $datas = $this->adzone->with(['adzone_details' => function ($q) {
            $q->where('adv_enddate', '>=', date('Y-m-d'))->where('adv_status',1);
        }])->where('adzone_screen', $home['screen'])->where('adzone_status', 1)->where('adzone_type', $home['type'])->get();





        $product_data = '';
        if (isset($datas[0]['adzone_details'])) {
            foreach ($datas[0]['adzone_details'] as $key => $data) {
                if ($data['adv_offer'] == 'Product') {

                    $datas[0]['adzone_details'][$key]["details"] = $this->itemmaster->selectRaw($this->getselect())->where('stit_ID', $data['adv_offerValueId'])->first();
                }
                if ($data['adv_offer'] == 'Category') {
                    $datas[0]['adzone_details'][$key]["details"] = array("request_id" => $data['adv_offerValueId']);
                }
                if ($data['adv_offer'] == 'Brand') {
                    $datas[0]['adzone_details'][$key]["details"] = array("request_id" => $data['adv_offerValueId']);
                }

                if ($data['adv_offer'] == 'Offer' && $data['adv_offerType'] == 'Product') {
                    $datas[0]['adzone_details'][$key]["details"] = $this->itemmaster->selectRaw($this->getselect())->where('stit_ID', $data['adv_offerValueId'])->first();
                }

                if ($data['adv_offer'] == 'Offer' && $data['adv_offerType'] == 'Category') {
                    $datas[0]['adzone_details'][$key]["details"] = array("request_id" => $data['adv_offerValueId']);
                }

                if ($data['adv_offer'] == 'Offer' && $data['adv_offerType'] == 'Brand') {
                    $datas[0]['adzone_details'][$key]["details"] = array("request_id" => $data['adv_offerValueId']);
                }
            }
        }

        if (count($datas[0]['adzone_details']) == 0) {
            $home['value'] = [];
        } else {
            $home['value'] = $datas;
        }


        return $home;
    }

    public function getactualdata($home, $request)
    {
        $request['branch_id'] = getCurrentUserBranch();

        foreach ($home as $key => $hom) {

            $type = $hom['type'];
            if ($type == "advertisement") {
                //unset($home[$key]);
                $home[$key] = $this->advertisement($hom, getCurrentUserBranch());
            }
            if ($type == "small advertisement") {
              //  unset($home[$key]);
                $home[$key] = $this->smalladvertisement($hom, getCurrentUserBranch());
                //dd($home[$key]);
            }
            if ($type == 'product') {
                $data = $this->getProducts($request);
                $home[$key]['value'] = $data[0];
                $home[$key]['total_count'] = $data[1];
                $home[$key]['min_count'] = 50;
                $home[$key]['pagenate_details'] = $data[2];
            }

            // if ($type == 'category') {
            //     $data = $this->getallCategoryLIst($request);
            //     $home[$key]['value'] = $data[0];
            //     $home[$key]['total_count'] = $data[1];
            //     $home[$key]['min_count'] = 50;
            // }


            if ($type == 'SubCategory') {

                $data = $this->categoryRequest($request);
                $home[$key]['value'] = $data[0];
                $home[$key]['total_count'] = $data[1];
                $home[$key]['min_count'] = 50;
            }
        }

        $homepage = array();
        $order="";
        foreach ($home as $key => $home1) {
            if (count($home1['value']) > 0) {
                if(!empty($order))
                {
                    $home1['order']=$order;
                    $order=++$order;
                }
                array_push($homepage, $home1);
            }
            else{
                if(empty($order))
                {
                    $order=$home1['order'];
                }

            }
        }
        return $homepage;
    }



    private function categoryRequest($item)
    {
        $category_id = $item['requested_id'];

        $id= $this->subcategory->select('main_category')->where('sub_category_id',$category_id)->first();

        $data = $this->subcategory->where('status', "1")->where('main_category',$id['main_category'])
            ->take(50)->get();
        $count = count($this->subcategory->where('status', "1")->where('main_category', $category_id)->get());
        return array($data, $count);



        // return $this->sub->with(['subcategory'=>function($q) use($category_id){
        //     $q->where('status',"1")->where('main_category',$category_id);
        // }])->where('parent_category',$category_id)->first();

    }

    private function getCategoryList()
    {
        $this->parentCategory->select('parent_category_id', 'parent_category as parent_category_name', 'image_url', 'status')
            ->take(50)
            ->get();
    }

    private function getallCategoryLIst()
    {
        $data = $this->parentCategory->select('parent_category_id', 'parent_category as parent_category_name', 'image_url', 'status')
            ->take(50)
            ->get();
        $count = count($this->parentCategory->select('parent_category_id', 'parent_category as parent_category_name', 'image_url', 'status')->where('status', 1)
            ->get());
        return array($data, $count);
    }


    private function fetchproduct($request)
    {
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $product = array();

        $virtualCategoryId=0;
        $collection = collect();
        $items = null;
        if($request->virtualcategoryid > 0)
            $virtualCategoryId = $request['virtualcategoryid'];//array($request['virtualcategoryid']);

        if (count($request['filter']['brands']) !== 0)
            $collection->push(['fsi_brand_id' => $request['filter']['brands']]);
        if (@$request['filter']['attributes'] != "")
            $collection->push(['attr' => $request['filter']['attributes']]);

        if($request->category_level > 0){
            if($request->category_level == 1)
		        $catsql = 'SELECT sc.sub_category_id as catids FROM mypha_productsubcategory sc INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id WHERE sc.STATUS=1 AND c.parent_category=' . $request['requested_id'];
            else
                $catsql = 'SELECT sub_category_id as catids FROM mypha_productsubcategory WHERE main_category =' . $request['requested_id'];

            $categoryid = DB::select($catsql);
		    $category_id = array_column($categoryid, 'catids');
            //$query = $query->whereIn('product_category', $category_id);
            if(!isset($category_id) || count($category_id) < 1)
	            $category_id = [0];

            $items = $this->_itemMasterCollection->getProducts($collection, $virtualCategoryId, $category_id);

        }
        else if(count($request['filter']['category']) == 0){
            //$category_id = array($request['requested_id']);            
            if(($request['requested_id'] > 0) && (@$request['filter']['attributes'] == ""))
                $collection->push(['product_category' => $request['requested_id']]);
            $items = $this->_itemMasterCollection->getProducts($collection, $virtualCategoryId);

        }
        else {
            //$category_id = $request['filter']['category'];
            if(@$request['filter']['attributes'] == "")
                $collection->push(['product_category' => $request['filter']['category']]);
            $items = $this->_itemMasterCollection->getProducts($collection, $virtualCategoryId);
        }

        return $items->get();


    }
    public function getProducts($request)
    {

        $item = $this->fetchproduct($request);

        $product = $item; //$this->checkField($item, getCurrentUserBranch());
           $product = collect($product);
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


        $data = $this->paginationpage($product, $request);
        return $data;
    }


    private function paginationpage($product, $request)
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

        return array($secondaryArray, $count, $std);
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
    private function getHomeProducts(array $items)
    {

        $group_id = array();
        $group = array();
        $group_product = array();
        $product_id = array();
        foreach ($items as $Itm) {
            $group_product = array();
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

    private function getItemFields()
    {
     /*   return [
            "fsi_uid",
            "fsi_uid as item_group_id",
            "fsi_item_name as item_name",
            "fsi_brand_name as brand_name",
            "fsi_category_id as category_id",
            "fsi_categry_name as category_name",
            "fsi_variant as variant",
            "finascop_stock_uniqueitem.isMedicine"
        ];*/
      return   "fsi_item_id,fsi_uid,fsi_uid as item_group_id,fsi_item_name as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,finascop_stock_uniqueitem.isMedicine,fsi_displaylabel,fsi_def_itemmaster_id, courierDelivery, directDelivery, br_branch_id as branch_id, br_storeGroup, br_directDelivery, br_courierDelivery";
        
    }

    private function brand($request)
    {
        $category_id = $request['requested_id'];
        return $this->getBrandList($category_id);
    }

    private function getBrandList($category_id)
    {
        return  $this->productbrand->getdetailWithSubCategory($category_id);
    }
}
