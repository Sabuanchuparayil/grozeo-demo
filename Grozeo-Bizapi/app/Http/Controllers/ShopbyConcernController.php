<?php

namespace App\Http\Controllers;

use stdClass;
use App\Models\Adzone;
use App\Models\Category;
use App\Models\HomePage;
use App\Models\Categorys;
use App\Models\Advertisement;
use App\Models\HealthConcern;
use App\Models\StockItemMaster;
use App\Models\StockUniqueItem;
use App\Models\MstParentCategory;
use App\Models\MapDiseaseMedicine;

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

class ShopbyConcernController extends Controller
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
    protected $mapdiasease;
    protected $sub;
    public function __construct(MapDiseaseMedicine $mapdiasease, HealthConcern $concern, Advertisement $advertisement, Adzone $adzone, StockItemMaster $itemmaster, ProductBrandRepository $productbrand, StockUniqueItem $uniqueItem, HomePage $homePage, HomepageICanCollect $homepageCollect, MstParentCategory $parentCategory, Category $subcategory, Categorys $sub)
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
        $this->concern = $concern;
        $this->mapdiasease = $mapdiasease;
        $this->sub = $sub;
    }

    public function getdata(Request $request)
    {
        if ($request['order_method'] == 1 || $request['order_method'] == 2)
            $home = $this->homePage->where('screen', 'Shop_by_concern')->where('is_active', 1)->get();
        /*if ($request['order_method'] == 2)
            $home = $this->homepageCollect->where('screen', 'Shop_by_concern')->where('is_active', 1)->get();
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
            if ($type == "product") {

                $data = $this->productRequest($request, $hom);
                $home[$key]['value'] = $data[0];
                $home[$key]['total_count'] = $data[1];
                $home[$key]['min_count'] = 9;
                $home[$key]['pagenate_details'] = $data[2];
            }
        }


        $homepage = array();
        $order="";
        foreach ($home as $key => $home1) {
            if (isset($home1['value']) && count($home1['value']) > 0) {
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



    private function productRequest($request, $home)
    {
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $count = '';
        $product = '';
        $concern_id = $request['requested_id'];

        $products = $this->mapdiasease->where('disease_id', $concern_id)->get()->toArray();

        if(count($request['filter']['category']) !== 0){
            $category_id = array();
            $category_id = $request['filter']['category'];

            $sub_category_id = array();
            $category_id = $this->sub->select('category_id')->whereIn('parent_category', $category_id)->get()->toArray();

            $subcategory_id = $this->subcategory->select('sub_category_id')->whereIn('main_category', array_column($category_id, 'category_id'))->get()->toArray();



            $sub_ids = array_column($subcategory_id, 'sub_category_id');

            foreach ($sub_ids as $sub_id) {

                $isexit = $this->itemmaster->where('featured', 1)->where('product_category', $sub_id)->get();
                if (count($isexit) > 0) {

                    array_push($sub_category_id, $sub_id);
                }
            }
        }



        $query = $this->uniqueItem->query();
                $query->join('finascop_stock_itemmaster as fs', 'fs.stit_fsiuid', 'finascop_stock_uniqueitem.fsi_uid')
                ->join(DB::raw('(SELECT bi.stit_id, bi.branch_id, b.br_storeGroup, bi.selling_price, bi.item_count, MAX(b.br_directDelivery)AS br_directDelivery, MAX(b.br_courierDelivery) AS br_courierDelivery
                    FROM finascop_stock_branch_inventory AS bi INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id WHERE bi.item_count > 0 GROUP BY bi.stit_id) br'), function($join)
                        {
                           $join->on('br.stit_id', '=', 'fs.stit_ID');
                        })
                ->whereHas('itemMaster', function ($q) use ($products, $domain) {
                    $q->whereIn('stit_ID', array_column($products, 'stit_ID'));
                    $q->where('stit_status', 1);
                });

            $storegroupid = getHeaderStoreGroup();
            if($request['branch_id'] > 0){
                $query->where(function($query1) use ($branch_id,$storegroupid){
                    $query1->where([
                        ['br.br_branch_id', '=', DB::raw($branch_id)],
                        ['br.br_directDelivery', '=', DB::raw(1)],
                        ['fs.directDelivery', '=', DB::raw(1)]
                    ])->orWhere([
                        ['br.br_branch_id', '=', DB::raw($branch_id)],
                        ['br.br_courierDelivery', '=', DB::raw(1)],
                        ['fs.courierDelivery', '=', DB::raw(1)]
                    ])->orWhere([
                        [DB::raw($storegroupid), '>', '0'],
                        ['br.br_courierDelivery', '=', DB::raw(1)],
                        ['br.br_storeGroup', '=', DB::raw($storegroupid)],
                        ['fs.courierDelivery', '=', DB::raw(1)]
                    ])->orWhere([
                        [DB::raw($storegroupid), '=', '0'],
                        ['br.br_courierDelivery', '=', DB::raw(1)],
                        ['fs.courierDelivery', '=', DB::raw(1)]
                    ]);
                });

            }
            else{
                if($storegroupid > 0)
                    $query = $query->where([
                        ['br.br_storeGroup', '=', DB::raw($storegroupid)],
                        ['br.br_courierDelivery', '=', DB::raw(1)],
                        ['fs.courierDelivery', '=', DB::raw(1)]
                    ]);
                else
                    $query = $query->where([
                        ['br.br_courierDelivery', '=', DB::raw(1)],
                        ['fs.courierDelivery', '=', DB::raw(1)]
                    ]);
            }


        if(count($request['filter']['category']) !== 0){

            $query->whereHas('itemMaster', function ($query) use ($sub_category_id) {
                $query->whereIn('product_category', $sub_category_id);
            });

        }
            $query->with(['itemMaster' => function ($query) use ($domain) {
                $query->with(['mainImage' => function ($qry) use ($domain) {
                    $qry->where('image_type', 1)
                        ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                }])
                    ->with(['additionalImage' => function ($qry) use ($domain) {
                        $qry->where('image_type', 0)
                            ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                    }])

                    ->selectRaw($this->getProductFields());
            }]);
            if (count($request['filter']['brands']) !== 0) {

                $query->whereIn('fsi_brand_id', $request['filter']['brands']);
            }

            $item = $query->selectRaw($this->getItemFields(1))->get();

        foreach ($item as $key => $value) {
            if ($value['isMedicine'] == 0) {
                $item[$key]['item_name'] = $value['fsi_brand_name'] . " " . $value['fsi_item_name'];
                $item[$key]['brand_name'] = $value['fsi_brand_name'];
                $item[$key]['category_id'] = $value['fsi_category_id'];
                $item[$key]['category_name'] = $value['fsi_categry_name'];
                $item[$key]['item_group_id'] = $value['fsi_uid'];
                $item[$key]['variant'] = $value['fsi_variant'];
                unset($value['fsi_item_name']);
                unset($value['fsi_category_id']);
                unset($value['fsi_brand_name']);
                unset($value['fsi_categry_name']);
                unset($value['fsi_variant']);
            } else {
                $item[$key]['item_group_id'] = $value['fsi_uid'];
                $item[$key]['item_name'] = $value['fsi_item_name'] . " " . $value['fsi_variant'];
                $item[$key]['brand_name'] = $value['fsi_brand_name'];
                $item[$key]['category_id'] = $value['fsi_category_id'];
                $item[$key]['category_name'] = $value['fsi_categry_name'];
                $item[$key]['variant'] = $value['fsi_variant'];
                unset($value['fsi_item_name']);
                unset($value['fsi_brand_name']);
                unset($value['fsi_category_id']);
                unset($value['fsi_categry_name']);
                unset($value['fsi_variant']);
            }
        }



        $item = $item->toArray();




        $product = $this->checkField($item, $request['branch_id'],$request['order_method']);

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






    private function getItemFields($dat)
    {

        if ($dat == 0) //product
        {
            return
                "fsi_item_id,fsi_uid,fsi_uid as item_group_id,CONCAT(fsi_brand_name,' ',fsi_item_name) as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,fs.isMedicine,fsi_displaylabel,fsi_def_itemmaster_id, courierDelivery, directDelivery, branch_id, br_storeGroup, br_directDelivery, br_courierDelivery";
        } else { //medinice
            return
                "fsi_item_id,fsi_uid,fsi_uid as item_group_id,fsi_item_name as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,fs.isMedicine,fsi_displaylabel,fsi_def_itemmaster_id, courierDelivery, directDelivery, branch_id, br_storeGroup, br_directDelivery, br_courierDelivery";
        }
    }



    private function getProductFields()
    {
        return "stit_ID,
                stit_fsiuid,
                stit_quantity as quantity,
                stit_ID as itemId,
                stit_Description as short_description,
                stit_long_description as long_description,
                stit_item_volume as stock_available,
                stit_GST as selling_prize,
                stit_MRP as mrp,cos_nos";
    }

    private function checkField($item, $branch_id,$order_method)
    {

        return $item ? $this->addFields($item, $branch_id,$order_method) : [];
        //return $item ? $this->addFields($item->toArray(), $branch_id) : [];
    }
    private function addFields(array $item, $branch_id,$order_method)
    {
 
        $products = $this->getHomeProducts($item);
        $stock = Stock::getStock($products['product'], $branch_id);
        $price = Price::findPrice($products['product'], $branch_id);
        $cheap = CheapPrice::getDefault($products['group'], $stock, $price);
/*
        $cpoproducts = DB::select('select * from vw_cpo_products');
        $cpparray = [
               "branch_id" => array_column($cpoproducts, "branch_id", "fcpod_itemid"),
               "fcpod_price" => array_column($cpoproducts, "fcpod_price", "fcpod_itemid"),
               "mrp" => array_column($cpoproducts, "mrp", "fcpod_itemid"),
          ];
*/
        $outOfStock_ids = array();
        foreach ($item as $key => $itm) {
            $count = count($item[$key]['item_master']);

                $brid = $itm['branch_id'];                
                $brDirectDelivery = $itm['br_directDelivery'];
                $itemDirectDelivery = $itm['directDelivery'];
		        $brTypeId= ($branch_id != $brid ? 1 : ($brDirectDelivery == 1 && $itemDirectDelivery == 1? 3 : 1));

            for ($i = 0; $i < $count; $i++) {
               /* $stitId = $item[$key]['item_master'][$i]['stit_ID'];
                $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
                $mrp = array_key_exists($stitId, $price['mrp']) ? $price['mrp'][$stitId] : 0;
                $selling_price = array_key_exists($stitId, $price['selling_price']) ? $price['selling_price'][$stitId] : 0;*/

                $stitId = $item[$key]['item_master'][$i]['stit_ID'];
                $cos_nos= $item[$key]['item_master'][$i]['cos_nos'];
                
                $cos_nos = ($cos_nos>0)?$cos_nos:1;
                $brid = $branch_id;
		        $brTypeId= 1;
                $default_br_id = getBranchIdForll();
                if($brid != $default_br_id)
		            $brTypeId= 3;

                


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
/*
                if($order_method==1 && ($stock_count <=0 || $selling_price <= 0 ) && array_key_exists($stitId, $cpparray['fcpod_price']) ){
                    $brid = $cpparray['branch_id'][$stitId];
                    $selling_price = $cpparray['fcpod_price'][$stitId];
                    $mrp = $cpparray['mrp'][$stitId];
                    $stock_count = 1000;
                    $brTypeId = 2;
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
                $item[$key]['item_master'][$i]['selling_prize'] = $selling_price;
                $item[$key]['item_master'][$i]['selling_price'] = $selling_price;
                $item[$key]['item_master'][$i]['godown_itemId'] = $this->getRand();
                $item[$key]['item_master'][$i]['percentage'] = $percentage;
                $item[$key]['item_master'][$i]['branch_id'] = $brid;
                $item[$key]['item_master'][$i]['branch_type_id'] = $brTypeId;
            }
        }

        return $item;
    }
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
