<?php

namespace App\Http\Controllers;

use stdClass;
use App\Models\Adzone;
use App\Models\Category;
use App\Models\HomePage;
use App\Models\Categorys;
use App\Models\ProductBrand;
use App\Models\SortAndFilter;
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
use App\Models\HealthConcern;


class CategoryScreenController extends Controller
{
    protected $uniqueItem;
    protected $parentCategory;
    protected $homePage;
    protected $subcategory;
    protected $productbrand;
    protected $sub;
    protected $product;
    protected $itemmaster;
    protected $sortfilter;
    protected $sortbrand;
    protected $sortsubcategory;
    protected $homepageCollect;
    protected $adzone;
    protected $concern;


    public function __construct(Adzone $adzone, HomepageICanCollect $homepageCollect, SortAndFilter $sortfilter, Category $sortsubcategory, ProductBrand $sortbrand, StockItemMaster $product, Categorys $sub, ProductBrandRepository $productbrand, StockUniqueItem $uniqueItem, HomePage $homePage, MstParentCategory $parentCategory, Category $subcategory,HealthConcern $concern)
    {
        $this->uniqueItem = $uniqueItem;
        $this->parentCategory = $parentCategory;
        $this->homePage = $homePage;
        $this->subcategory = $subcategory;
        $this->productbrand = $productbrand;
        $this->sub = $sub;
        $this->product = $product;
        $this->itemmaster = $product;
        $this->sortfilter = $sortfilter;
        $this->sortbrand = $sortbrand;
        $this->sortsubcategory = $sortsubcategory;
        $this->homepageCollect = $homepageCollect;
        $this->adzone = $adzone;
        $this->concern=$concern;
    }


    public function getdata(CategoryScreenRequest $request)
    {


        $data = array();


        if ($request['order_method'] == 1 || $request['order_method'] == 2)
            $home = $this->homePage->where('screen', 'Category')->where('is_active', 1)->get();
        /*if ($request['order_method'] == 2)
            $home = $this->homepageCollect->where('screen', 'Category')->where('is_active', 1)->get();
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

            if ($type == "product") {

                $data = $this->productRequest($request, $hom);
                $home[$key]['value'] = $data[0];
                $home[$key]['total_count'] = $data[1];
                $home[$key]['min_count'] = 9;
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
        foreach ($home as $key => $hom) {
            $type = $hom['type'];
            if ($type == "advertisement") {

                $home[$key] = $this->advertisement($hom, $request['branch_id']);
            }
            if ($type == "small advertisement") {
                $home[$key] = $this->smalladvertisement($hom, $request['branch_id']);
                //dd($home[$key]);
            }
            if ($type == "Featured Products") {

                $data = $this->productRequest($request, $hom);
                $home[$key]['value'] = $data[0];
                $home[$key]['total_count'] = $data[1];
                $home[$key]['min_count'] = 9;
            }
            if ($type == "SubCategory") {

                $data = $this->categoryRequest($request);
                $home[$key]['title'] =$this->parentCategory->where("parent_category_id",$request['requested_id'])->first()->parent_category;
                $home[$key]['value'] = $data[0];
                $home[$key]['total_count'] = $data[1];
                $home[$key]['min_count'] = 9;
            }
            if ($type == "Brand") {

                $data = $this->brand($request);
                $home[$key]['value'] = $data[0];
                $home[$key]['total_count'] = $data[1];
                $home[$key]['min_count'] = 9;
            } else if ($type == "product") {

                $data = $this->productRequest($request, $hom);
                $home[$key]['value'] = $data[0];
                $home[$key]['total_count'] = $data[1];
                $home[$key]['min_count'] = 9;
                $home[$key]['pagenate_details'] = $data[2];
            }else   if ($type == "shop by concern") {
                $datas  = $this->shopbyconcern($hom,$request['branch_id']);
                $home[$key]['total_count'] = $datas[1];
                $home[$key]['min_count'] = 9;
                $home[$key] = $datas[0];
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
    public function shopbyconcern($home,$branch_id)
    {
        $data=$this->concern->select(["disease_id","disease_name","disease_description","disease_image"])->get();
        $home['value'] = $data;
        return array($home,count($data));
    }

    private function productRequest($request, $home)
    {
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $count = '';
        $product = '';
        $type = $home['type'];

        if ($type == "product") {
            if (count($request['filter']['category']) == 0) {
                $category_id = array($request['requested_id']);
            } else {
                $category_id = $request['filter']['category'];
            }  

            $sub_category_id = array();
            $category_id = $this->sub->select('category_id')->whereIn('parent_category', $category_id)->get()->toArray();

            $subcategory_id = $this->subcategory->select('sub_category_id')->whereIn('main_category', array_column($category_id, 'category_id'))->get()->toArray();


            $sub_category_id = $sub_ids = array_column($subcategory_id, 'sub_category_id'); 
         /*   foreach ($sub_ids as $sub_id) {

                $isexit = $this->product->where('featured', 1)->where('product_category', $sub_id)->get();
                if (count($isexit) > 0) {

                    array_push($sub_category_id, $sub_id);
                }
            } 
            $product = array();*/



            $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
            $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

            $query = $this->uniqueItem->query();

            /*$query->whereHas('itemMaster', function ($query) use ($sub_category_id) {
            })*/
                $query->with(['itemMaster' => function ($query) use ($sub_category_id, $domain) {
                    $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
                 stit_GST as selling_prize,
                 stit_MRP as mrp,cos_nos")->whereIn('product_category', $sub_category_id)
                        ->with(['mainImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 1)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }]);
                }])
                
                ->selectRaw($this->getItemFields())->where('isMedicine', 0);

            if (count($request['filter']['brands']) !== 0) {

                $query->whereIn('fsi_brand_id', $request['filter']['brands']);
            }
            $query->whereIn('fsi_category_id',$sub_ids);
            $query->where('fsi_count','>',0);
            $product = $query->get();



            $product = $this->checkField($product->toArray(), $request['branch_id']);

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


                $product = $product->filter(function (&$item) use ($request) {
                    $item['item_master'] = collect($item['item_master'])
                        ->whereBetween('selling_prize', [$request['filter']['price_range'][0], $request['filter']['price_range'][1]]);
                    return count($item['item_master']) ? true : false;
                })->map(function (&$item) use ($request) {
                    $item['item_master'] = collect($item['item_master'])
                        ->whereBetween('selling_prize', [$request['filter']['price_range'][0], $request['filter']['price_range'][1]])->values();
                    return $item;
                });
            }

            $count = count($product);

            $currentPage = LengthAwarePaginator::resolveCurrentPage();

            // Create a new Laravel collection from the array data
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

        if ($type == "Featured Products") {
            $category_id = $request['requested_id'];
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
                ->whereHas('itemMaster')
                ->with(['itemMaster' => function ($query) use ($sub_category_id, $domain) {
                    $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
                     stit_GST as selling_prize,
                     stit_MRP as mrp,cos_nos")->whereIn('product_category', $sub_category_id)
                        ->with(['mainImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 1)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }]);
                }])
                ->selectRaw($this->getItemFields())->whereIn('fsi_uid', $id)->where('isMedicine', 0)
                ->take(9)
                ->get()->toArray();

            $count = count($this->uniqueItem
                ->whereHas('itemMaster')
                ->with(['itemMaster' => function ($query) use ($sub_category_id, $domain) {
                    $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
                     stit_GST as selling_prize,
                     stit_MRP as mrp")->whereIn('product_category', $sub_category_id)
                        ->with(['mainImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 1)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }]);
                }])
                ->selectRaw($this->getItemFields())->whereIn('fsi_uid', $id)->where('isMedicine', 0)
                ->get());

            foreach ($products as $prod) {
                if (count($prod['item_master']) > 0) {
                    array_push($product, $prod);
                }
            }
        }

        return array($this->checkField($product, $request['branch_id']), $count);

        if ($type == "Popular products") {
            $sub_category_id = array();
            $stock_uniq = $this->itemmaster->where('popular', 1)->select('stit_fsiuid')->get()->toArray();

            $id = array_unique(array_column($stock_uniq, 'stit_fsiuid'));

            $category_id = $request['requested_id'];

            $category_id = $this->sub->select('category_id')->where('parent_category', $category_id)->get()->toArray();
            $subcategory_id = $this->subcategory->select('sub_category_id')->whereIn('main_category', array_column($category_id, 'category_id'))->get()->toArray();
            //$product=$this->product->whereIn('product_category',array_column($subcategory_id, 'sub_category_id'))->get();
            $sub_ids = array_column($subcategory_id, 'sub_category_id');

            foreach ($sub_ids as $sub_id) {

                $isexit = $this->product->where('popular', 1)->where('product_category', $sub_id)->get();
                if (count($isexit) > 0) {

                    array_push($sub_category_id, $sub_id);
                }
            }
            $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
            $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

            $product = array();
            $products = $this->uniqueItem
                ->whereHas('itemMaster')
                ->with(['itemMaster' => function ($query) use ($sub_category_id, $domain) {
                    $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
                     stit_GST as selling_prize,
                     stit_MRP as mrp")->whereIn('product_category', $sub_category_id)
                        ->with(['mainImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 1)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }]);
                }])
                ->selectRaw($this->getItemFields())->whereIn('fsi_uid', $id)->where('isMedicine', 0)
                ->take(9)
                ->get()->toArray();
            $count = count($this->uniqueItem
                ->whereHas('itemMaster')
                ->with(['itemMaster' => function ($query) use ($sub_category_id, $domain) {
                    $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
                     stit_GST as selling_prize,
                     stit_MRP as mrp")->whereIn('product_category', $sub_category_id)
                        ->with(['mainImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 1)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }]);
                }])
                ->selectRaw($this->getItemFields())->whereIn('fsi_uid', $id)->where('isMedicine', 0)

                ->get());
            foreach ($products as $prod) {
                if (count($prod['item_master']) > 0) {
                    array_push($product, $prod);
                }
            }

            return array($this->checkField($product, $request['branch_id']), $count);
        }



        return array($this->checkField($product, $request['branch_id']), $count);
        //return $product;
    }




    private function brand($request)
    {
        $category_id = $request['requested_id'];
        return $this->getBrandList($category_id);
    }

    private function getBrandList($category_id)
    {
        $data = [];
        if (count($this->productbrand->getdetailWithCategory($category_id))) {
            $data = $this->productbrand->getdetailWithCategory($category_id)->take(9);
        }

        $count = count($this->productbrand->getdetailWithCategory($category_id));
        return array($data, $count);
        // return  $this->productbrand->getdetailWithCategory($category_id);
    }


    private function getProduct()
    {
        $item = $this->uniqueItem->whereHas('itemMaster')
            ->with(['itemMaster' => function ($query) {
                $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,isMedicine")
                    ->with(['mainImage' => function ($qry) {
                        $qry->where('image_type', 1)
                            ->select('id', 'product_id', 'image_url', 'image_thumb_url');
                    }]);
            }])
            ->selectRaw($this->getItemFields())
            ->take(9)
            ->get();

        return $item;
    }

    private function getItemFields()
    {
        return
            "fsi_uid,fsi_uid as item_group_id,CONCAT(fsi_brand_name,' ',fsi_item_name) as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,isMedicine";
    }

    private function getItemFields1()
    {
        return
            "fsi_uid,fsi_uid as item_group_id,CONCAT(fsi_brand_name,' ',fsi_item_name) as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,isMedicine";
    }

    private function categoryRequest($item)
    {
        $category_id = $item['requested_id'];
      /*  $data = $this->subcategory->where('status', "1")->where('main_category', $category_id)
            ->take(9)->get();
        $count = count($this->subcategory->where('status', "1")->where('main_category', $category_id)->get());
        return array($data, $count);*/
        if(isset($item['type']) && $item['type']==2){
            $data= $this->sub->where('parent_category',$category_id)->where('status',"1")->get();
        }elseif(isset($item['type']) && $item['type']==3){
            $data = $this->subcategory->where('status', "1")->where('main_category', $category_id)->get();
        }else{
            $data= $this->sub->where('parent_category',$category_id)->where('status',"1")->get();
        }


       
         return array($data, count($data));

    }

    private function getbrand($item)
    {
        $category_id = $item['requested_id'];
    }



    //

    private function checkField($item, $branch_id)
    {
        return $item ? $this->addFields($item, $branch_id) : [];
        //return $item ? $this->addFields($item->toArray(), $branch_id) : [];
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
                /*$stitId = $item[$key]['item_master'][$i]['stit_ID'];
                $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
                $mrp = array_key_exists($stitId, $price['mrp']) ? $price['mrp'][$stitId] : 0;
                $selling_price = array_key_exists($stitId, $price['selling_price']) ? $price['selling_price'][$stitId] : 0;*/

                $stitId = $item[$key]['item_master'][$i]['stit_ID'];
                $cos_nos= $item[$key]['item_master'][$i]['cos_nos'];
                
                $cos_nos = ($cos_nos>0)?$cos_nos:1;

                $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
                $mrp = array_key_exists($stitId, $price['fpod_leastSKUmrp']) ? $price['fpod_leastSKUmrp'][$stitId] *$cos_nos : 0;
             //   $order_method= \Session::get('order_method');
                
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
}
