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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CheckFilter;
use App\Http\Repositories\Item\Price;
use App\Http\Repositories\Item\Stock;
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\Item\CheapPrice;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Repositories\Product\ProductBrandRepository;
use App\Http\Requests\CategoryScreen\CategoryScreenRequest;

use App\Http\Repositories\Item\ItemMasterCollection;
//use Illuminate\Support\Collection;

class BrandScreenController extends Controller
{

    protected $uniqueItem;
    protected $parentCategory;
    protected $homePage;
    protected $subcategory;
    protected $productbrand;
    protected $sub;
    protected $product;
    protected $sortfilter;
    protected $sortbrand;
    protected $homepageCollect;
    protected $adzone;
    protected $_itemMasterCollection;

    public function __construct(Adzone $adzone,HomepageICanCollect $homepageCollect, SortAndFilter $sortfilter, ProductBrand $sortbrand, StockItemMaster $product, Categorys $sub, ProductBrandRepository $productbrand, StockUniqueItem $uniqueItem, HomePage $homePage, MstParentCategory $parentCategory, Category $subcategory, ItemMasterCollection $itemMasterCollection)
    {
        $this->uniqueItem = $uniqueItem;
        $this->parentCategory = $parentCategory;
        $this->homePage = $homePage;
        $this->subcategory = $subcategory;
        $this->productbrand = $productbrand;
        $this->sub = $sub;
        $this->product = $product;
        $this->sortfilter = $sortfilter;
        $this->sortbrand = $sortbrand;
        $this->homepageCollect = $homepageCollect;
        $this->adzone = $adzone;
        $this->_itemMasterCollection = $itemMasterCollection;
    }



    public function getdata(CategoryScreenRequest $request)
    {
        $data = array();


        if ($request['order_method'] == 1 || $request['order_method'] == 2)
            $home = $this->homePage->where('screen', 'Brand')->where('is_active', 1)->get();
       /* if ($request['order_method'] == 2)
            $home = $this->homepageCollect->where('screen', 'Brand')->where('is_active', 1)->get();
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
                $data = $this->getProduct($request);
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
            //    unset($home[$key]);
                $home[$key] = $this->advertisement($hom, $request['branch_id']);
            }
            
            if ($type == 'category') {
                unset($home[$key]);
               /* $data = $this->getCategoryList();
                $home[$key]['value'] = $data[0];
                $home[$key]['total_count'] = $data[1];
                $home[$key]['min_count'] = 9;*/
            }


            if ($type == 'product') {
                $data = $this->getProduct($request);
                $home[$key]['value'] = $data[0];
                $home[$key]['total_count'] = $data[1];
                $home[$key]['min_count'] = 9;
                $home[$key]['pagenate_details'] = $data[2];
            }


            if ($type == 'SubCategory') {
                unset($home[$key]);
                /*$data = $this->getSubCategoryList();
                $home[$key]['value'] = $data[0];
                $home[$key]['total_count'] = $data[1];
                $home[$key]['min_count'] = 9;*/
            }
            if ($type == "small advertisement") {
               // unset($home[$key]);
                $home[$key] = $this->smalladvertisement($hom, $request['branch_id']);
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

        return $homepage;
    }




    public function sortcategory()
    {
        return $this->parentCategory->select('parent_category_id', 'parent_category')
            ->take(10)
            ->get();
    }



    private function getCategoryList()
    {

        $data = $this->parentCategory->select('parent_category_id', 'parent_category as parent_category_name', 'image_url', 'status')
            ->take(9)
            ->get();

        $count = count($this->parentCategory->select('parent_category_id', 'parent_category as parent_category_name', 'image_url', 'status')->get());
        return array($data, $count);
    }


    public function fetchproduct($request)
    {
        $product = array();

        if(count($request['filter']['brands']) == 0)
            $brand_id = array($request['requested_id']);
        else
            $brand_id = $request['filter']['brands'];

        $items = $this->_itemMasterCollection->getProducts();

        if(count($request['filter']['category']) !== 0){
            $category_id = array();
            $category_id = $request['filter']['category'];
            $sub_category_id = array();
            $category_id = $this->sub->select('category_id')->whereIn('parent_category', $category_id)->get()->toArray();
            $subcategory_id = $this->subcategory->select('sub_category_id')->whereIn('main_category', array_column($category_id, 'category_id'))->get()->toArray();
            $sub_ids = array_column($subcategory_id, 'sub_category_id');
        }

            if(count($request['filter']['category']) !== 0){
/*
                $items = $items->whereHas('itemMaster', function ($query) use ($sub_category_id) {
                    $query->whereIn('fsi_category_id', $sub_ids);
                    $query->whereIn('fsi_category_id', $sub_ids);                    
                });
*/
            }
                $product =$items->where('pdt_brand', $brand_id)->get();

        return $product;
    }
    public function getProduct($request)
    {

        $item = $this->fetchproduct($request);
        $product = $item; //$this->checkField($item, $request['branch_id']);

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


    private function getSubCategoryList()
    {
        $data =  $this->subcategory->take(9)->get();
        $count = count($this->subcategory->get());
        return array($data, $count);
    }
}
