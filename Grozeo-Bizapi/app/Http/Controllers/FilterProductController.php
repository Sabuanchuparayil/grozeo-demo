<?php

namespace App\Http\Controllers;

use stdClass;
use App\Models\Category;
use App\Models\HomePage;
use App\Models\Categorys;
use App\Models\ProductBrand;
use App\Models\SortAndFilter;
use App\Models\ParentCategory;
use App\Models\StockItemMaster;
use App\Models\StockUniqueItem;
use App\Models\MstParentCategory;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use App\Http\Repositories\Item\Price;
use App\Http\Repositories\Item\Stock;
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\Item\CheapPrice;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Repositories\Product\ProductBrandRepository;
use App\Http\Repositories\Product\FilterProductRepository;

class FilterProductController extends Controller
{
    protected $productbrands;
    protected $productbrand;
    protected $subcategory;
    protected $uniqueItem;
    protected $itemmaster;
    protected $sub;
    protected $product;
    protected $brand;
    protected $parentCategory;
    private $parentcategory;
    public function __construct(ProductBrandRepository $productbrand, MstParentCategory $parentCategory)
    {
        $this->productbrand = $productbrand;
        $this->parentCategory = $parentCategory;
    }


    public function sortfilter(Request $request)
    {

        $validatedData = $request->validate([
            'screen' => 'nullable',
            'required_id' => 'required',

        ]);
        //  dd($request['min_price']);

        $id = $request['required_id'];

        $data = $this->getdata($request['screen'], $id);

        return new SuccessWithData($data);
    }

    public function getdata($screen, $id)
    {

        $sortfilter = $this->getSortFilterValues();

        $sortitem = array();
        $filteritem = array();
        foreach ($sortfilter as $key => $sort) {

            if ($sort['sort_filter'] == 0) {

                if ($sort['name'] == "price") {

                    $sortfilter[$key]['value'] = $this->getPrice();
                    array_push($sortitem, $sortfilter[$key]);
                }
            } else {

                if ($sort['name'] == "Brand") {


                    $sortfilter[$key]['value'] = $this->brand($id);
                    array_push($filteritem, $sortfilter[$key]);
                }
                if ($sort['name'] == "Category") {

                    $sortfilter[$key]['value'] = $this->getCategoryList($id);
                    array_push($filteritem, $sortfilter[$key]);
                }
                if ($sort['name'] == "pricerange") {

                    $sortfilter[$key]['value'] = $this->getpricerange();
                    array_push($filteritem, $sortfilter[$key]);
                }
            }
        }
        $std = new stdClass();
        $std->sort = $sortitem;
        $std->filter = $filteritem;
        return $std;
    }

    private function getpricerange()
    {
        return[ [
            "min" => 0,
            "max" =>1000
        ]];
    }
    private function getPrice()
    { return [[
        "id" => 1,
        "name" =>"HIGH TO LOW"
    ],
    [
        "id" => 2,
        "name" =>"LOW TO HIGH"
    ]
    ];
        // return '[{"id": 1,"name": "HIGH TO LOW","id": 2,"name": "LOW TO HIGH"]';
    }
    private function getSortFilterValues()
    {
        $data = SortAndFilter::select()->where('status', 1)->get()->toArray();
        return $data;
    }

    private function brand($category_id)
    {
        $data = $this->productbrand->getdetailWithCategory($category_id);
        return $data;
    }

    private function brandsubcategory($category_id)
    {
        $data = $this->productbrand->getdetailWithSubCategory($category_id);
        return $data;
    }



    private function getCategoryList($id)
    {

        $data = $this->parentCategory->select('parent_category_id as id', 'parent_category as parent_category_name')->where('status', 1)
            ->take(10)
            ->get();


        return $data;
    }


    public function sortfilterols(Request $request)
    {


        $validatedData = $request->validate([
            'screen' => 'required',
            'required_id' => 'required'

        ]);


        if ($request['screen'] == "Category") { } else if ($request['screen'] == "Brand") { } else if ($request['screen'] == "Subcategory") { } else if ($request['screen'] == "Search") { }



        $sort[0] = [
            "id" => 1,
            "name" => "HIGH TO LOW"
        ];
        $sort[1] = [
            "id" => 2,
            "name" => "LOW TO HIGH"
        ];

        $filters = $this->allbrands($validatedData);
        $category = $this->parentcategory->with(['subcategories' => function ($query) {
            $query->with('categories');
        }])->get();
        $filter = [
            "id" => 3,
            "name" => "Brand",
            "value" => $filters
        ];
        $category = [
            "id" => 4,
            "name" => "Category",
            "value" => $category
        ];
        $data = [
            'sort' => $sort,
            'filter' => [$filter, $category]
        ];
        return new SuccessWithData($data);
    }

    private function allbrands($request)
    {


        $type = HomePage::select('type', 'screen')
            ->where('id',  $request['id'])
            ->first();


        if ($type['type'] == "Brand"  &&  $type['screen'] == "Home") {

            $brand = $this->productbrands->get();

            return $brand;
        } else if ($type['type'] == "Brand"  &&  $type['screen'] == "Category") {

            $brand = $this->brand->getdetailWithCategory($request['category_id']);

            return $brand;
        }
    }
    public function sortFilterSearch(Request $request)
    {

        $validatedData = $request->validate([
            'id' => 'required',
            'sort' => 'nullable',
            'innerscreen_id' => 'nullable',
            'request_id' => 'nullable'
        ]);


        $type = HomePage::select('type', 'screen')->where('id', $request['id'])
            ->first();


        /**
         * category Screen Sort and filiter
         */
        if ($type['type'] == "category"  &&  $type['screen'] == "Home") {

            if ($request->has('innerscreen_id')) {

                $innertype = HomePage::select('type', 'screen')->where('id', $request['innerscreen_id'])
                    ->first();

                if ($innertype['type'] == "Featured Products"  &&  $innertype['screen'] == "Category") {

                    //view for Featured product

                    $product = $this->productDetails($request, $innertype['type']);

                    return new SuccessWithData($product);
                } else if ($innertype['type'] == "SubCategory"  &&  $innertype['screen'] == "Category") {

                    $validatedData = $request->validate([
                        'request_id' => 'required'
                    ]);
                    $request_id = $request['request_id'];

                    $product = $this->productDetails($request, $innertype['type']);

                    return new SuccessWithData($product);
                } else if ($innertype['type'] == "Brand"  &&  $innertype['screen'] == "Category") {

                    $validatedData = $request->validate([
                        'request_id' => 'required'
                    ]);
                    $request_id = $request['request_id'];

                    $product = $this->productDetails($request, $innertype['type']);

                    return new SuccessWithData($product);
                } else if ($innertype['type'] == "product"  &&  $innertype['screen'] == "Category") {


                    $product = $this->productDetails($request, $innertype['type']);

                    return new SuccessWithData($product);
                }
            }
        }

        /**
         * HomeScreen - Featured Product View page
         */
        if ($type['type'] == "Featured Products"  &&  $type['screen'] == "Home") {

            $product = $this->homeProductDetails($request, $type['type']);

            return new SuccessWithData($product);
        }

        /**
         * HomeScreen -Brand product
         */
        if ($type['type'] == "Brand"  &&  $type['screen'] == "Home") {

            $validatedData = $request->validate([
                'request_id' => 'required'
            ]);
            $request_id = $request['request_id'];

            $product = $this->productDetails($request, $type['type']);

            return new SuccessWithData($product);
        }
        /**
         * HomeScreen - popular Product View page
         */
        if ($type['type'] == "Popular products"  &&  $type['screen'] == "Home") {

            $product = $this->homeProductDetails($request, $type['type']);

            return new SuccessWithData($product);
        }
    }
    /////////////////////////////////////////////






    //  category Screen Productlist


    private function productDetails($request, $type)
    {

        if ($type == "product") {
            if ($request['sort'] == 1 || $request['sort'] == 2) {
                $products = $this->sortProductDetails($request);

                return $products;
            }
        } elseif ($type == "Featured Products") {

            if ($request['sort'] == 1 || $request['sort'] == 2) {
                $products = $this->sortFeaturedProductDetails($request);

                return $products;
            }
        } elseif ($type == "Brand") {

            if ($request['sort'] == 1 || $request['sort'] == 2) {
                $products = $this->sortBrandProductDetails($request);

                return $products;
            }
        } elseif ($type == "SubCategory") {

            if ($request['sort'] == 1 || $request['sort'] == 2) {

                $products = $this->sortsubCategoryProducts($request);

                return $products;
            }
        }
    }

    //    sort category Screen Productlist


    private function sortProductDetails($request)
    {

        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $category_id = $request['request_id'];
        $sub = $request["filter"]["5"];
        $sub_category_id = array();
        $category_id = $this->sub->select('category_id')->where('parent_category', $category_id)->get()->toArray();

        $subcategory_id = $this->subcategory->select('sub_category_id')->whereIn('main_category', array_column($category_id, 'category_id'))->get()->toArray();

        $sub_ids = array_column($subcategory_id, 'sub_category_id');

        foreach ($sub_ids as $sub_id) {

            $isexit = $this->product->where('featured', 1)->where('product_category', $sub_id)->get();
            if (count($isexit) > 0) {

                array_push($sub_category_id, $sub_id);
            }
        }
        $query = $this->uniqueItem->query();
        $query->whereHas('itemMaster', function ($query) use ($sub_category_id, $sub) {
            if (!empty($sub)) {
                $query->where('product_category', $sub);
            } else {
                $query->whereIn('product_category', $sub_category_id);
            }
        })
            ->with(['itemMaster' => function ($query) use ($sub_category_id, $sub, $domain) {
                $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
                 stit_GST as selling_prize,
                 stit_MRP as mrp");
                if (!empty($sub)) {
                    $query->where('product_category', $sub);
                } else {
                    $query->whereIn('product_category', $sub_category_id);
                }
                $query->with(['mainImage' => function ($qry) use ($domain) {
                    $qry->where('image_type', 1)
                        ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                }]);
            }])
            ->selectRaw($this->getItemFields())->where('isMedicine', 0);
        $this->filterProduct($query, $request);
        $product = $query->get();

        if (($request->input('sort')) == 1) {
            $products = $product->sortBy(function ($product, $key) {
                return (int) $product['itemMaster'][0]['mrp'];
            });
        }
        if (($request->input('sort')) == 2) {
            $products = $product->sortByDesc(function ($product, $key) {
                return (int) $product['itemMaster'][0]['mrp'];
            });
        }

        $pro =  $products->toArray();
        $result = array_values($pro);

        return array($this->checkField($result, $request['branch_id'], $request));
    }



    //    sort category Screen Featured Productlist


    private function sortFeaturedProductDetails($request)
    {
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $category_id = $request['request_id'];
        $sub = $request["filter"]["5"];
        $sub_category_id = array();
        $stock_uniq = $this->itemmaster->where('featured', 1)->select('stit_fsiuid')->get()->toArray();
        $id = array_unique(array_column($stock_uniq, 'stit_fsiuid'));
        $category_id = $this->sub->select('category_id')->where('parent_category', $category_id)->where('isMedicine', 0)->get()->toArray();
        $subcategory_id = $this->subcategory->select('sub_category_id')->whereIn('main_category', array_column($category_id, 'category_id'))->where('isMedicine', 0)->get()->toArray();

        $sub_ids = array_column($subcategory_id, 'sub_category_id');

        foreach ($sub_ids as $sub_id) {

            $isexit = $this->product->where('featured', 1)->where('product_category', $sub_id)->get();
            if (count($isexit) > 0) {

                array_push($sub_category_id, $sub_id);
            }
        }

        $query = $this->uniqueItem->query();
        $query->whereHas('itemMaster', function ($query) use ($sub_category_id, $sub, $domain) {
            if (!empty($sub)) {
                $query->where('product_category', $sub);
            } else {
                $query->whereIn('product_category', $sub_category_id);
            }
        })
            ->with(['itemMaster' => function ($query) use ($sub_category_id, $sub, $domain) {
                $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
                     stit_GST as selling_prize,
                     stit_MRP as mrp");
                if (!empty($sub)) {
                    $query->where('product_category', $sub);
                } else {
                    $query->whereIn('product_category', $sub_category_id);
                }
                $query->with(['mainImage' => function ($qry) use ($domain) {
                    $qry->where('image_type', 1)
                        ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                }]);
            }])
            ->selectRaw($this->getItemFields())->whereIn('fsi_uid', $id)->where('isMedicine', 0);
        $this->filterProduct($query, $request);
        $product = $query->get();

        if (($request->input('sort')) == 1) {
            $products = $product->sortBy(function ($product, $key) {
                return (int) $product['itemMaster'][0]['mrp'];
            });
        }
        if (($request->input('sort')) == 2) {
            $products = $product->sortByDesc(function ($product, $key) {
                return (int) $product['itemMaster'][0]['mrp'];
            });
        }

        $pro =  $products->toArray();
        $res = array_values($pro);

        return array($this->checkField($res, $request['branch_id'], $request));
    }

    private function filterProduct($query, $request)
    {

        if (!empty($request["filter"]["3"])) {
            $brand = $request["filter"]["3"];

            $query->where('fsi_brand_id', $brand);
        }
    }


    private function sortBrandProductDetails($request)
    {
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $category_id = $request["filter"]["3"];
        $sub = $request["filter"]["5"];
        $sub_category_id = [];
        if (!empty($category_id)) {

            $sub_category_id = array();
            $category_id = $this->sub->select('category_id')->where('parent_category', $category_id)->get()->toArray();

            $subcategory_id = $this->subcategory->select('sub_category_id')->whereIn('main_category', array_column($category_id, 'category_id'))->get()->toArray();

            $sub_ids = array_column($subcategory_id, 'sub_category_id');

            foreach ($sub_ids as $sub_id) {

                $isexit = $this->product->where('pdt_brand', $request['request_id'])->where('product_category', $sub_id)->get();
                if (count($isexit) > 0) {

                    array_push($sub_category_id, $sub_id);
                }
            }
        }
        $sub = $request["filter"]["5"];
        $query = $this->uniqueItem->query();
        $query->whereHas('itemMaster', function ($query) use ($sub, $sub_category_id, $category_id) {
            if (!empty($sub)) {
                $query->where('product_category', $sub);
            }
            if (!empty($category_id)) {
                $query->whereIn('product_category', $sub_category_id);
            }
        })
            ->with(['itemMaster' => function ($query) use ($sub, $sub_category_id, $category_id, $domain) {
                $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
                stit_GST as selling_prize,stit_MRP as mrp,isMedicine");
                if (!empty($sub)) {
                    $query->where('product_category', $sub);
                }
                if (!empty($category_id)) {
                    $query->whereIn('product_category', $sub_category_id);
                }
                $query->with(['mainImage' => function ($qry) use ($domain) {
                    $qry->where('image_type', 1)
                        ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                }]);
            }])
            ->select($this->getItemField())->where('fsi_brand_id', $request['request_id']);
        $product = $query->get();
        //    dd($product);


        if (($request->input('sort')) == 1) {
            $products = $product->sortBy(function ($product, $key) {
                return (int) $product['itemMaster'][0]['mrp'];
            });
        }
        if (($request->input('sort')) == 2) {
            $products = $product->sortByDesc(function ($product, $key) {
                return (int) $product['itemMaster'][0]['mrp'];
            });
        }

        $pro =  $products->toArray();
        $res = array_values($pro);
        //  dd($res);

        return array($this->checkField($res, $request['branch_id'], $request));
    }
    private function sortsubCategoryProducts($request)
    {
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $query = $this->uniqueItem->query();
        $query->whereHas('itemMaster')
            ->with(['itemMaster' => function ($query) use ($domain) {
                $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,cos_nos")
                    ->with(['mainImage' => function ($qry) use ($domain) {
                        $qry->where('image_type', 1)
                            ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                    }]);
            }])
            ->selectRaw($this->getItemFields())
            ->where('fsi_category_id', $request['request_id']);
        $this->filterProduct($query, $request);
        $product = $query->get();

        if (($request->input('sort')) == 1) {
            $products = $product->sortBy(function ($product, $key) {
                return (int) $product['itemMaster'][0]['mrp'];
            });
        }
        if (($request->input('sort')) == 2) {
            $products = $product->sortByDesc(function ($product, $key) {
                return (int) $product['itemMaster'][0]['mrp'];
            });
        }

        $pro =  $products->toArray();
        $res = array_values($pro);

        return array($this->checkField($res, $request['branch_id'], $request));
    }

    //  Home Screen Productlist


    private function homeProductDetails($request, $type)
    {
        if ($type == "Popular products") {
            if ($request['sort'] == 1 || $request['sort'] == 2) {
                $products = $this->sortHomePopularProduct($request);

                return $products;
            }
        } elseif ($type == "Featured Products") {

            if ($request['sort'] == 1 || $request['sort'] == 2) {
                $products = $this->sortHomeFeaturedProduct($request);

                return $products;
            }
        }
    }
    private function sortHomePopularProduct($request)
    {
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $stock_uniq = $this->itemmaster->where('popular', 1)->select('stit_fsiuid')->get()->toArray();

        $id = array_unique(array_column($stock_uniq, 'stit_fsiuid'));

        $category_id = $request["filter"]["4"];
        $sub = $request["filter"]["5"];
        $sub_category_id = [];
        if (!empty($category_id)) {
            $sub_category_id = array();
            $category_id = $this->sub->select('category_id')->where('parent_category', $category_id)->get()->toArray();

            $subcategory_id = $this->subcategory->select('sub_category_id')->whereIn('main_category', array_column($category_id, 'category_id'))->get()->toArray();

            $sub_ids = array_column($subcategory_id, 'sub_category_id');

            foreach ($sub_ids as $sub_id) {

                $isexit = $this->itemmaster->where('popular', 1)->get()->toArray();
                if (count($isexit) > 0) {

                    array_push($sub_category_id, $sub_id);
                }
            }
        }

        $sub = $request["filter"]["5"];
        $query = $this->uniqueItem->query();
        $query->whereHas('itemMaster', function ($query) use ($sub, $sub_category_id, $category_id) {
            if (!empty($sub)) {
                $query->where('product_category', $sub);
            }
            if (!empty($category_id)) {
                $query->whereIn('product_category', $sub_category_id);
            }
        })
            ->with(['itemMaster' => function ($query) use ($sub, $sub_category_id, $category_id, $domain) {
                $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
            stit_GST as selling_prize,isMedicine,
            stit_MRP as mrp");
                if (!empty($sub)) {
                    $query->where('product_category', $sub);
                }
                if (!empty($category_id)) {
                    $query->whereIn('product_category', $sub_category_id);
                }
                $query->with(['mainImage' => function ($qry) use ($domain) {
                    $qry->where('image_type', 1)
                        ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                }]);
            }])
            ->selectRaw($this->getItemFields())->whereIn('fsi_uid', $id)
            ->where('isMedicine', 0);
        $this->filterProduct($query, $request);
        $product = $query->get();

        if (($request->input('sort')) == 1) {
            $products = $product->sortBy(function ($product, $key) {
                return (int) $product['itemMaster'][0]['mrp'];
            });
        }
        if (($request->input('sort')) == 2) {
            $products = $product->sortByDesc(function ($product, $key) {
                return (int) $product['itemMaster'][0]['mrp'];
            });
        }

        $pro =  $products->toArray();
        $res = array_values($pro);

        return array($this->checkField($res, $request['branch_id'], $request));
    }
    private function sortHomeFeaturedProduct($request)
    {

        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $stock_uniq = $this->itemmaster->where('featured', 1)
            ->select('stit_fsiuid')->get()->toArray();

        $id = array_unique(array_column($stock_uniq, 'stit_fsiuid'));


        $category_id = $request["filter"]["4"];
        $sub = $request["filter"]["5"];
        $sub_category_id = [];
        if (!empty($category_id)) {
            $sub_category_id = array();
            $category_id = $this->sub->select('category_id')->where('parent_category', $category_id)->get()->toArray();

            $subcategory_id = $this->subcategory->select('sub_category_id')->whereIn('main_category', array_column($category_id, 'category_id'))->get()->toArray();

            $sub_ids = array_column($subcategory_id, 'sub_category_id');

            foreach ($sub_ids as $sub_id) {

                $isexit = $this->itemmaster->where('featured', 1)->get()->toArray();
                if (count($isexit) > 0) {

                    array_push($sub_category_id, $sub_id);
                }
            }
        }

        $sub = $request["filter"]["5"];
        $query = $this->uniqueItem->query();
        $query->whereHas('itemMaster', function ($query) use ($sub, $sub_category_id, $category_id, $domain) {
            if (!empty($sub)) {
                $query->where('product_category', $sub);
            }
            if (!empty($category_id)) {
                $query->whereIn('product_category', $sub_category_id);
            }
        })
            ->with(['itemMaster' => function ($query) use ($sub, $domain) {
                $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid,stit_item_volume as stock_available,
                stit_GST as selling_prize,isMedicine,
                stit_MRP as mrp.cos_nos");
                if (!empty($sub)) {
                    $query->where('product_category', $sub);
                }
                $query->with(['mainImage' => function ($qry) use ($domain) {
                    $qry->where('image_type', 1)
                        ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                }]);
            }])
            ->selectRaw($this->getItemFields())->whereIn('fsi_uid', $id)->where('isMedicine', 0);
        $this->filterProduct($query, $request);
        $product = $query->get();

        if (($request->input('sort')) == 1) {
            $products = $product->sortBy(function ($product, $key) {
                return (int) $product['itemMaster'][0]['mrp'];
            });
        }
        if (($request->input('sort')) == 2) {
            $products = $product->sortByDesc(function ($product, $key) {
                return (int) $product['itemMaster'][0]['mrp'];
            });
        }

        $pro =  $products->toArray();
        $res = array_values($pro);

        return array($this->checkField($res, $request['branch_id'], $request));
        //return $item ;
    }

    private function checkField($item, $branch_id, $request)
    {

        return $item ? $this->addFields($item, $branch_id, $request) : [];
    }
    private function addFields(array $item, $branch_id, $request)
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
                
                $cos_nos = ($cos_nos > 0)?$cos_nos:1;

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
               
                $default_val = in_array($stitId, $cheap) ? 1 : 0;

                $mrp=round($mrp,2);
                $selling_price=round($selling_price,2);
                $percentage=round($percentage,2);

                $item[$key]['item_master'][$i]['default_value'] = $default_val;
                $item[$key]['item_master'][$i]['stock_available'] = $stock_count;
                $item[$key]['item_master'][$i]['mrp'] = $mrp;
                $item[$key]['item_master'][$i]['selling_price'] = $selling_price;
                $item[$key]['item_master'][$i]['godown_itemId'] = $this->getRand();
                $item[$key]['item_master'][$i]['percentage'] = $percentage;
            }
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // Create a new Laravel collection from the array data
        $productCollection = collect($item);

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



        $std = new stdClass();
        $std->currentpage = $data['current_page'];
        $std->ProductList = $secondaryArray;
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



    private function getItemFields()
    {
        return
            "fsi_uid,fsi_uid as item_group_id,CONCAT(fsi_brand_name,' ',fsi_item_name) as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,isMedicine";
    }

    private function getItemField()
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
}
