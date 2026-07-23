<?php


namespace App\Http\Controllers;

use stdClass;
use App\Models\Adzone;
use App\Models\Category;
use App\Models\HomePage;
use App\Models\Advertisement;
use App\Models\HealthConcern;
use App\Models\StockItemMaster;
use App\Models\StockUniqueItem;
use App\Models\MstParentCategory;
use App\Models\HomepageICanCollect;
use App\Models\AppConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\Item\Price;
use App\Http\Repositories\Item\Stock;
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\Item\CheapPrice;
use App\Http\Repositories\Product\ProductBrandRepository;
use Illuminate\Support\Facades\Log;
use App\Http\Repositories\Category\CategoryRepository;

use App\Http\Repositories\Item\ItemMasterCollection;
use Illuminate\Support\Collection;

class HomeScreenController extends Controller
{
    
    protected $uniqueItem;
    protected $parentCategory;
    protected $homePage;
    protected $subcategory;
    protected $productbrand;
    protected $itemmaster;
    protected $adzone;
    protected $advertisement;
    protected $homepageCollect;
    protected $concern;
    protected $_itemMasterCollection;
    protected $catRepo;

    public function __construct(HealthConcern $concern,Advertisement $advertisement, Adzone $adzone, StockItemMaster $itemmaster, ProductBrandRepository $productbrand, StockUniqueItem $uniqueItem, HomePage $homePage, HomepageICanCollect $homepageCollect, MstParentCategory $parentCategory, Category $subcategory, ItemMasterCollection $itemMasterCollection, CategoryRepository $categoryRepo)
    {

        $this->uniqueItem = $uniqueItem;
        $this->parentCategory = $parentCategory;
        $this->homePage = $homePage;
        $this->subcategory = $subcategory;
        $this->productbrand = $productbrand;
        $this->itemmaster = $itemmaster;
        $this->advertisement = $advertisement;
        $this->adzone = $adzone;
        $this->homepageCollect = $homepageCollect;
        $this->concern=$concern;
        $this->_itemMasterCollection = $itemMasterCollection;
        $this->catRepo = $categoryRepo;
    }

    private function homePages($order_method)
    {

        if ($order_method == 1 || $order_method == 2)
            return $this->homePage->where('screen', 'home')->where('is_active', 1)->get();
       /*if ($order_method == 2)
            return $this->homepageCollect->where('screen', 'home')->where('is_active', 1)->get();
        */
        //  return $this->homePage->whereIn('id', [1, 2,3,7,9])->get()->toArray();
    }
    public function getfield(){
        $data = $this->itemmaster->where('stit_ID', 1)->first();
        
    }
    public function getCredentials()
    {
        // TODO: Security — this endpoint issues a dummy JWT without auth; may be intentional for guest browsing but should be reviewed.
        $data = AppConfig::select(
            'brac_id','brac_branch', 'brac_phone')->first();
       // dd($data);exit;
        $data["dummy"]=true;
        $data["token"]=createJwtToken($data);
        
        return new SuccessWithData($data);
    }
    public function get($branch_id, $order_method, $business_type=-1, $retailtype=-1)
    {
        $branch_id= getCurrentUserBranch();

        //$branch_id = 1;
        
        session(['order_method' => $order_method]);
        $home = $this->homePages($order_method);
        foreach ($home as $key => $hom) {
            $type = $hom['type'];
            if ($type == "advertisement") { //BANNERS
                $home[$key] = $this->advertisement($hom, $branch_id, $business_type, $retailtype);
            }

            if ($type == "category") {
                $datas = $this->shopByCategory($hom, $business_type, $retailtype);
                $home[$key]['total_count'] = $datas[1];
                $home[$key]['min_count'] = 9;
                $home[$key] = $datas[0];
            }
            if ($type == "combinedcategory") { // TOP CATEGORY CAROUSEL
                $datas = $this->shopByCombinedCategory($hom, $business_type, $retailtype);
                $home[$key]['total_count'] = $datas[1];
                $home[$key]['min_count'] = 9;
                $home[$key] = $datas[0];
            }

            if ($type == "Featured Products") { // Suggested Products For You
                $datas = $this->featuredProduct($hom, $branch_id, $business_type, $retailtype);
                $home[$key]['total_count'] = $datas[1];
                $home[$key]['min_count'] = 9;
                $home[$key] = $datas[0];
            }

            // if ($type == "Sub Category") {
            //     $home[3] = $this->shopBySubCategory($hom, $branch_id);
            //     $home[3]['order'] = 4;
            // }

            if ($type == "Brand") {
                $datas = $this->brand($hom, $branch_id);
                $home[$key]['total_count'] = $datas[1];
                $home[$key]['min_count'] = 9;
                $home[$key] = $datas[0];
            }

            if ($type == "Popular products") {
                $datas  = $this->popularProduct($hom, $branch_id, $business_type, $retailtype);
                $home[$key]['total_count'] = $datas[1];
                $home[$key]['min_count'] = 9;
                $home[$key] = $datas[0];
            }
            if ($type == "shop by concern") {
                $datas  = $this->shopbyconcern($hom,$branch_id);
                $home[$key]['total_count'] = $datas[1];
                $home[$key]['min_count'] = 9;
                $home[$key] = $datas[0];
            }

            if ($type == "product") {
                $home[$key] = $this->getProduct($branch_id, 'product', $business_type, $retailtype);
            }

            if ($type == "small advertisement") {
                $home[$key] = $this->advertisement($hom, $branch_id, $business_type, $retailtype);
                //dd($home[$key]);
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

        return new SuccessWithData(
            $homepage
        );
    }


    public function products($branch_id)
    {
        $std = new stdClass();
        $std->id = 10;
        $std->type = "products";
        $std->image_url = "";
        $std->description = "";
        $std->title = "Products";
        $std->background_img = "";
        $std->sub_id = "0";
        $std->order = 10;
        $std->value = $this->getProduct($branch_id, "product");
        return $std;
    }


    private function smalladvertisement($home, $branch_id, $business_type=-1, $retailtype=-1)
    {
        $storegroupid = getHeaderStoreGroup();

        //  dd($this->adzone->with('ad')->where('adzone_screen',$home['screen'])->where('adzone_type',$home['type'])->get()->toArray());
        $datas = $this->adzone->with(['adzone_details' => function ($q) use($storegroupid, $business_type) {
            $q->where('adv_enddate', '>=', date('Y-m-d'))->where('adv_status',1);
            if($business_type > 0 || $storegroupid > 0)
                $q->whereRaw('(adv_applicable_category = 2 AND EXISTS( SELECT c.* FROM mypha_productcategory c INNER JOIN mypha_productparent_category pc ON c.parent_category = pc.parent_category_id 
    INNER JOIN finascop_branch_group_business_type sbt ON pc.parent_category_businessType = sbt.business_type_id WHERE c.STATUS=1 AND c.category_id=ad.adv_applicable_category_value ' . ($storegroupid > 0 ? ' AND sbt.store_group_id= ' . $storegroupid : '') . ($business_type > 0 ? ' AND pc.parent_category_businessType = ' . $business_type : '') . ' )) 
OR (adv_applicable_category = 1 AND EXISTS (SELECT * FROM mypha_productparent_category pc INNER JOIN finascop_branch_group_business_type sbt ON pc.parent_category_businessType = sbt.business_type_id
    WHERE pc.STATUS=1 AND pc.parent_category_id = ad.adv_applicable_category_value ' . ($storegroupid > 0 ? ' AND sbt.store_group_id= ' . $storegroupid : '') . ($business_type > 0 ? ' AND pc.parent_category_businessType = ' . $business_type : '') . '))');


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

    private function advertisement($home, $branch_id, $business_type=-1, $retailtype=-1)
    {
        $storegroupid = getHeaderStoreGroup(); //0-global
      /*  $datas = $this->adzone->with(['adzone_details' => function ($q) {
            $q->where('adv_enddate', '>=', date('Y-m-d'))->where('adv_status',1);
        }])->where('adzone_screen', $home['screen'])->where('adzone_status', 1)->where('adzone_type', $home['type'])->get();*/

        $datas = $this->adzone->with(['adzone_details' => function ($q) use($storegroupid, $business_type, $retailtype) {
            $q->where('adv_enddate', '>=', date('Y-m-d'))->where('adv_status',1);
            $q->select(["adv_id","adv_title","adv_imageurl","adv_offer","adv_offerValueId","adzone_id", "storegroup_id", "adv_offerType", "adv_usageType", "imageUrl_1", "imageUrl_2"])->orderBy('storegroup_id', 'desc');
            $q->where(function($query) use ($storegroupid){
                    $query->where('storegroup_id', $storegroupid)->orWhere('storegroup_id', DB::raw(0));
                });

	if($storegroupid == 0)
		$q->where('adv_applicable_for', '<>', 2);
	else
		$q->where('adv_applicable_for', '<>',1);

            if($retailtype > 0){
                $q->where(function($query) use ($retailtype){
                    /*
                    $query->where([
                        ['adv_applicable_category', DB::raw(2)],
                        ['adv_applicable_category_value', $retailtype]
                    ]);
                    */
                    $query->whereRaw('(adv_applicable_category = 2 and exists(SELECT rbc_business_type FROM retaline_business_category WHERE business_category_id='.$retailtype.' AND FIND_IN_SET(adv_applicable_category_value, rbc_business_type) > 0))');

                });
            }
            else if($business_type > 0){
                $q->where(function($query) use ($business_type){
                    $query->where([
                        ['adv_applicable_category', DB::raw(2)],
                        ['adv_applicable_category_value', $business_type]
                    ]);
                });
            }
            else if($storegroupid > 0){
                $q->where(function($query) use ($storegroupid){
                    $query->where('adv_applicable_category', DB::raw(0))
                    ->orWhereRaw('adv_applicable_category = 2 AND adv_applicable_category_value IN (SELECT business_type_id FROM `finascop_branch_group_business_type` WHERE store_group_id= ' . $storegroupid .')');
                })->orderBy('storegroup_id', 'desc');
            }
/*
                $q->whereRaw('(adv_applicable_category = 2 AND EXISTS( SELECT c.* FROM mypha_productcategory c INNER JOIN mypha_productparent_category pc ON c.parent_category = pc.parent_category_id 
    INNER JOIN finascop_branch_group_business_type sbt ON pc.parent_category_businessType = sbt.business_type_id WHERE c.STATUS=1 AND c.category_id=app_advertisements.adv_applicable_category_value ' . ($storegroupid > 0 ? ' AND sbt.store_group_id= ' . $storegroupid : '') . ($business_type > 0 ? ' AND pc.parent_category_businessType = ' . $business_type : '') . ' )) 
OR (adv_applicable_category = 1 AND EXISTS (SELECT * FROM mypha_productparent_category pc INNER JOIN finascop_branch_group_business_type sbt ON pc.parent_category_businessType = sbt.business_type_id
    WHERE pc.STATUS=1 AND pc.parent_category_id = app_advertisements.adv_applicable_category_value ' . ($storegroupid > 0 ? ' AND sbt.store_group_id= ' . $storegroupid : '') . ($business_type > 0 ? ' AND pc.parent_category_businessType = ' . $business_type : '') . '))');
*/
        }])->where('adzone_screen', $home['screen'])->where('adzone_status', 1)->where('adzone_type', $home['type'])->select(["adzone_id","adzone_name","adzone_type"])->get();


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


        /* if (count($datas[0]['adzone_details']) == 0) {
            $home['value'] = [];
        } else { */
            $home['value'] = $datas;
        // }


        return $home;
    }

    private function getselect()
    {
        return "stit_fsiuid as item_group,
                stit_ID as possible_keys,
                stit_ID as item,
                isMedicine
                ";
    }



    public function shopbyconcern($home,$branch_id)
    {
        $data=$this->concern->select(["disease_id","disease_name","disease_description","disease_image"])->get();
        $home['value'] = $data;
        return array($home,count($data));
    }

    private function getProduct($branch_id, $type, $business_type=-1, $retailtype=-1)
    {

        DB::enableQueryLog();
       
        if ($type == "Featured Products") {

            $collection = collect();
            $collection->push(['featured' => 1]);

            $featuredVC = DB::table('retaline_virtual_category')->select('vc_id', 'isFeatured')->where([
                ['store_group_id', getHeaderStoreGroup()],
                ['isFeatured', 1]
            ])->first();
            $featuredVCId = @$featuredVC->vc_id ?? 0;

            // filter by featured is outdated. So temporary deleing this filter. Featured items will be normal items order by percentage desc as default.
            $items = $this->_itemMasterCollection->getProducts(null, $featuredVCId, null, 0, $business_type, $retailtype)->orderBy('featured', 'desc')->take(12)->get();
            if(!isset($items) || count($items) < 12)
            {
                $itemCount = (@$items) ? count($items) : 0;
                $count = 12 - $itemCount;

                $featured = $this->_itemMasterCollection->getProducts(null, 0, null, 0, $business_type, $retailtype)->orderBy('featured', 'desc')->take($count)->get();

        		if(isset($items))
                	$items = $items->merge($featured);
		        else if(isset($featured))
                	$items = $featured;
            }
            return array($items, 0);
        }
        if ($type == "Popular products") {

             //return $this->getProductsByType($branch_id,"popular",1);
            $collection = collect();
            $collection->push(['popular' => 1]);
            $items = $this->_itemMasterCollection->getProducts($collection, 0, null, 0, $business_type, $retailtype);
            return array($items->get(), 0);
           
        }

        if ($type == "product") {

             //return $this->getProductsByType($branch_id);
            return array($this->_itemMasterCollection->getProducts(null, 0, null, 0, $business_type, $retailtype)->get(), 0);
        }
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


    private function shopByCategory($home, $business_type=-1, $retailtype=-1)
    {

        $datas = $this->getCategoryList($business_type, $retailtype);
        $home['value'] = $datas[0];

        return array($home, $datas[1]);
    }

    private function shopByCombinedCategory($home, $business_type=-1, $retailtype=-1)
    {
        $datas = $this->getHomeCategoryList($business_type, $retailtype);
        $home['value'] = $datas[0];

        return array($home, $datas[1]);

    }

    private function shopBySubCategory($home)
    {
        $home['value'] = $this->getSubCategoryList();


        return $home;
    }

    private function brand($home)
    {
        $datas = $this->getBrandList();
        $home['value'] = $datas[0];

        return array($home, $datas[1]);
    }


    private function getSubCategoryList()
    {
        return $this->subcategory->get();
    }


    private function getBrandList()
    {
        return  $this->productbrand->getdetails();
    }
    private function getCategoryList($business_type=-1, $retailtype=-1)
    {



        // return $this->parentCategory->with(['subcategories' => function ($query) {
        //     $query->where('status', "1")
        //         //->take(9)
        //         ->with(['subcategory' => function ($qry) {
        //             $qry->where('status', '1');
        //         }]);
        // }])->select('parent_category_id', 'parent_category as parent_category_name', 'image_url', 'status')
        //     ->take(9)
        //     ->get();
        $storegroupid = getHeaderStoreGroup();

        $data = $this->parentCategory->select(
            'parent_category_id',
            'parent_category as parent_category_name',
            'thumb_url',
            'image_url',
            'status',
            'isHome',
            'isInCategory',
            DB::raw('0 as isVirtualCategory'),
            'parent_category_id as id',
            // 'status as STATUS',
            'parent_category',
            DB::raw('1 as cattype'),
            DB::raw('0 as horder'),
            // DB::raw('-1 as parent_category_id'),
            DB::raw('0 as category_id')
        )->where('status', 1);
        if($storegroupid > 0 || $business_type > 0){
            $data = $data->join('finascop_branch_group_business_type as sbt', 'sbt.business_type_id', 'mypha_productparent_category.parent_category_businessType');
            if($storegroupid > 0)
                $data = $data->where('sbt.store_group_id', $storegroupid);
            if($business_type > 0)
                $data = $data->where('sbt.business_type_id', $business_type);
        }

            $data = $data->whereHas('subcategories', function($q) use($storegroupid){
                $q->whereHas('subcategory', function($sq) use($storegroupid){
                    $sq->whereRaw('sub_category_id IN( SELECT DISTINCT product_category FROM finascop_stock_itemmaster fs INNER JOIN finascop_stock_branch_inventory bi ON bi.stit_id = fs.stit_ID INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id ' . ($storegroupid >0 ? ' WHERE b.br_storeGroup= ' . $storegroupid : '') . ')');
                });
            });
            //->take(9)
        $data = $data->get();
        /*$count = count($this->parentCategory->select('parent_category_id', 'parent_category as parent_category_name', 'image_url', 'status')->where('status', 1)
            ->get());*/
            $count =0;    
        return array($data, $count);
    }

    // Select combined categories (parent, category, sub category) where isHome flag set to 1
    private function getHomeCategoryList($business_type=-1, $retailtype=-1)
    {
        try
        {
            $data = $this->catRepo->getCategoryList("", $business_type, $retailtype, "ORDER BY isHome desc, horder DESC, cattype DESC, parent_category ");
                $count =0;
            return array($data, $count);
        }
        catch (\Exception $e)
        {
            // info($e->getMessage()); 
            return array([], 0);
        }

    }

    private function featuredProduct($home, $branch_id, $business_type=-1, $retailtype=-1)
    {
        if ($home['sub_id'] == 0) {
            $datas = $this->getProduct($branch_id, $home['type'], $business_type, $retailtype);

            $home['value'] =  $datas[0];

            return array($home, $datas[1]);
        }

        //  return $home;
    }

    private function popularProduct($home, $branch_id, $business_type=-1, $retailtype=-1)
    {
        $datas =  $this->getProduct($branch_id, $home['type'], $business_type, $retailtype);
        $home['value'] = $datas[0];

        return array($home, $datas[1]);
    }

    public function getOffers($business_type=-1, $retailtype=-1, $sort=null, $filter_type=-1, $filter_value=0)
    {
        // $items = $this->_itemMasterCollection->getProducts(null, 0, null, 0, $business_type, $retailtype)->orderBy('percentage', 'desc')->paginate(10);

        // return new SuccessWithData(
        //     $items
        // );

        $items = $this->_itemMasterCollection->getProducts(null, 0, null, 0, $business_type, $retailtype);
        if($filter_value > 0)
        {
            if($filter_type == 'Brand')
            {
                $items = $items->where('fsi_brand_id', $filter_value);
            }
            else if($filter_type == 'Product')
            {
                $items = $items->where('stit_ID', $filter_value);
            }
            else if($filter_type == 'Category')
            {
                $items = $items->where('fsi_category_id', $filter_value);
            }
        }
        $items  = $items->orderBy('percentage', 'desc')->paginate(10);

        return new SuccessWithData(
            $items
        );
      
    }

    public function getProductsPaged($type=1, $business_type=-1, $retailtype=-1, $size=12)
    {
        //DB::enableQueryLog();       
        if ($type == 1) { // "Featured Products"
            $items = $this->_itemMasterCollection->getProducts(null, 0, null, 0, $business_type, $retailtype)->orderBy('featured', 'desc')->paginate($size);
            return new SuccessWithData(
                $items
            );
        }
        else if ($type == 2) { // "Popular products"
            $collection = collect();
            $collection->push(['popular' => 1]);

            $items = $this->_itemMasterCollection->getProducts($collection, 0, null, 0, $business_type, $retailtype)->paginate($size);
            return new SuccessWithData(
                $items
            );           
        }
        else {
            $items = $this->_itemMasterCollection->getProducts(null, 0, null, 0, $business_type, $retailtype)->paginate($size);
            return new SuccessWithData(
                $items
            );

        }
    }
    
    public function getSideBannerAdvertisement($business_type=-1, $retailtype=-1)
    {
        $storegroupid = getHeaderStoreGroup();
        $ad_data = $this->adzone->with(
            [
                'adzone_details' => function ($q) use($storegroupid, $business_type, $retailtype)
                {
                    $q->where('adv_enddate', '>=', date('Y-m-d'))->where('adv_status',1);
                    $q->select(["adv_id","adv_title","adv_imageurl","adv_offer","adv_offerValueId","adzone_id", "storegroup_id", "adv_offerType", "adv_usageType"])->orderBy('storegroup_id', 'desc');
                    $q->where(
                        function($query) use ($storegroupid)
                        {
                            $query->where('storegroup_id', $storegroupid)->orWhere('storegroup_id', DB::raw(0));
                        }
                    );
                    if($storegroupid == 0)
                        $q->where('adv_applicable_for', '<>', 2);
                    else
                        $q->where('adv_applicable_for', '<>',1);

                    if($retailtype > 0)
                    {
                        $q->where(function($query) use ($retailtype)
                        {
                            $query->whereRaw('(adv_applicable_category = 2 and exists(SELECT rbc_business_type FROM retaline_business_category WHERE business_category_id='.$retailtype.' AND FIND_IN_SET(adv_applicable_category_value, rbc_business_type) > 0))');

                        });
                    }
                    else if($business_type > 0)
                    {
                        $q->where(
                            function($query) use ($business_type)
                            {
                                $query->where([
                                    ['adv_applicable_category', DB::raw(2)],
                                    ['adv_applicable_category_value', $business_type]
                                ]);
                            }
                        );
                    }
                    else if($storegroupid > 0)
                    {
                        $q->where(
                            function($query) use ($storegroupid)
                            {
                                $query->where('adv_applicable_category', DB::raw(0))
                                ->orWhereRaw('adv_applicable_category = 2 AND adv_applicable_category_value IN (SELECT business_type_id FROM `finascop_branch_group_business_type` WHERE store_group_id= ' . $storegroupid .')');
                            }
                        )->orderBy('storegroup_id', 'desc');
                    }
                }
            ]
        )->where('adzone_name', 'Side small banner')->where('adzone_status', 1)->select(["adzone_id","adzone_name","adzone_type"])->get();

        $i = 0;
        foreach ($ad_data[0]['adzone_details'] as $data)
        {
            if ($data['adv_offer'] == 'Product')
            {   
                $ad_data[0]['adzone_details'][$i]["details"] = $this->itemmaster->selectRaw($this->getselect())->where('stit_ID', $data['adv_offerValueId'])->first();
            }   
            if ($data['adv_offer'] == 'Category') { 
                $ad_data[0]['adzone_details'][$i]["details"] = array("request_id" => $data['adv_offerValueId']);
            }   
            if ($data['adv_offer'] == 'Brand') {    
                $ad_data[0]['adzone_details'][$i]["details"] = array("request_id" => $data['adv_offerValueId']);
            }
        
            if ($data['adv_offer'] == 'Offer' && $data['adv_offerType'] == 'Product') { 
                $ad_data[0]['adzone_details'][$i]["details"] = $this->itemmaster->selectRaw($this->getselect())->where('stit_ID', $data['adv_offerValueId'])->first();
            }
        
            if ($data['adv_offer'] == 'Offer' && $data['adv_    offerType'] == 'Category') {
                $ad_data[0]['adzone_details'][$i]["details"] = array("request_id" => $data['adv_offerValueId']);
            }
        
            if ($data['adv_offer'] == 'Offer' && $data['adv_offerType'] == 'Brand')
            {   
                $ad_data[0]['adzone_details'][$i]["details"] = array("request_id" => $data['adv_offerValueId']);
            }
            $i++;
        }
        return new SuccessWithData($ad_data);
    }
}
