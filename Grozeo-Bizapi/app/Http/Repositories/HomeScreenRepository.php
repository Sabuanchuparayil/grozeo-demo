<?php

namespace App\Http\Repositories\HomeScreen;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Category;
use App\Models\HomePage;
use App\Models\AppConfig;
use App\Models\Advertisement;
use App\Models\StockUniqueItem;
use App\Models\MstParentCategory;
use App\Http\Repositories\ItemPrice;
use App\Http\Repositories\Item\Price;
use App\Http\Repositories\Item\Stock;
use App\Http\Repositories\StockAvailable;
use App\Http\Repositories\Item\CheapPrice;

class HomeScreenRepository
{
    protected $category;

    protected $advertisement;

    protected $product;

    protected $uniqueItem;

    protected $homePage;

    protected $parentCategory;

    public function __construct(
                            Category $category, 
                            Advertisement $advertisement, 
                            Product $product,
                            StockUniqueItem $uniqueItem,
                            HomePage $homePage,
                            MstParentCategory $parentCategory
                            )
    {
        $this->category = $category;
        $this->advertisement = $advertisement;
        $this->product = $product;
        $this->uniqueItem = $uniqueItem;
        $this->homePage = $homePage;
        $this->parentCategory = $parentCategory;
    }

    /**
     * Fetch details to be shown at the home main screen
     *
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {
        return collect(['categories' => $this->getCategories()])
                ->merge($this->getOffersAndAdvertisements())
                ->merge([
                    'featured' => $this->getFeatured(),
                    'popular' => $this->getPopular(),
                    'cart_product_ids' => $this->getCartItems(),
                ]);
    }

    /**
     * Fetch all categories
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCategories()
    {
        return $this->category
                    ->where('status', "1")
                    ->where('parent_category', 0)
                    ->get();
    }

    /**
     * Fetch offers and advertisements
     *
     * @return \Illuminate\Support\Collection
     */
    public function getOffersAndAdvertisements()
    {
        return $this->advertisement
                    ->where('adv_status', 'active')
                    ->get()
                    ->groupBy('adv_type');
    }

    /**
     * Fetch featured products
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFeatured()
    {
        return $this->product
                    ->select('product_id', 'product_name', 'product_image_url', 'pdt_mrp', 'pro_nutri_info')
                    ->where('featured', 1)
                    ->limit(20)
                    ->get();
    }

    /**
     * Fetch popular products
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPopular()
    {
        return $this->product
                    ->select('product_id', 'product_name', 'product_image_url', 'pdt_mrp', 'pro_nutri_info')
                    ->where('popular', 1)
                    ->limit(30)
                    ->get();
    }

    /**
     * Retrieve all items in the cart.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCartItems()
    {
        return Cart::select('cart_product_id', 'cart_order_qty')
                    ->where('cart_customer_id', auth_user()->cust_id)
                    ->get();
    }
    
    /**
     * Undocumented function
     *
     * @return void
     */
    private function getProduct($branch_id)
    {
        $item = $this->uniqueItem->whereHas('itemMaster')
        ->with(['itemMaster' => function ($query) {
            $query->selectRaw("stit_ID,stit_quantity as quantity,stit_fsiuid")
                ->with(['mainImage' => function ($qry) {
                    $qry->where('image_type', 1)
                        ->select('id', 'product_id', 'image_url', 'image_thumb_url');
                }]);
        }])
        ->select($this->getItemFields())
        ->take(9)
        ->get();

       return $this->checkField($item, $branch_id);
    }

     /**
     * Return stock unique items fields
     *
     * @return Array
     */
    private function getItemFields()
    {
        return [
            "fsi_uid",
            "fsi_uid as item_group_id",
            "fsi_item_name as item_name",
            "fsi_brand_name as brand_name",
            "fsi_category_id as category_id",
            "fsi_categry_name as category_name",
            "fsi_variant as variant"
        ];
    }

     /**
     * Add more fields in getItem function
     *
     * @param array $item
     * @return void
     */
    private function addFields(array $item, $branch_id)
    {
        $products = $this->getHomeProducts($item);
        $stock = Stock::getStock($products['product'], $branch_id);
        $price = Price::findPrice($products['product'], $branch_id);
        $cheap = CheapPrice::getDefault($products['group'], $stock, $price);
        foreach ($item as $key => $itm) {
            $count = count($item[$key]['item_master']);
            for ($i = 0; $i < $count; $i++) {
                $stitId = $item[$key]['item_master'][$i]['stit_ID'];
                $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
                $mrp = array_key_exists($stitId, $price['mrp']) ? $price['mrp'][$stitId] : 0;
                $selling_price = array_key_exists($stitId, $price['selling_price']) ? $price['selling_price'][$stitId] : 0;
                $default_val = in_array($stitId, $cheap) ? 1 : 0;

                $item[$key]['item_master'][$i]['default_value'] = $default_val;
                $item[$key]['item_master'][$i]['stock_available'] = $stock_count;
                $item[$key]['item_master'][$i]['mrp'] = $mrp;
                $item[$key]['item_master'][$i]['selling_price'] = $selling_price;
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
        $def = $this->uniqueItem->where('fsi_def_itemmaster_id', $id)->first();
        return $def ? 1 : 0;
    }

    private function checkField($item, $branch_id)
    {
        return $item ? $this->addFields($item->toArray(), $branch_id) : [];
    }

    private function homePages()
    {
        return $this->homePage->all()->toArray();
    }

    public function homeData($branch_id)
    {
        $home = $this->homePages();
        foreach($home as $key => $hom)
        {
            $typeId = $hom['type_id']; 
            if($typeId == 1)
            $home[0] = $this->advertisement($hom, $branch_id);
            if($typeId == 2)
            $home[1] = $this->shopByCategory($hom);
            if($typeId == 3)
            $home[2] = $this->featuredProduct($hom, $branch_id);
          }
          
        return $home;
    }
    
    private function advertisement($home, $branch_id)
    {
        $home['value'][] = [
                'image' => "http://uat.api.brm.velosit.in/images/banner1.jpg",
                'description' => 'LLeorem fjf fkjf',
                'value' => $this->getProduct($branch_id),
                ];
        $home['value'][] = [
                    'image' => "http://uat.api.brm.velosit.in/images/banner2.jpg",
                    'description' => 'ertyu jfjf fjfjf',
                    'value' => $this->getProduct($branch_id),
                    ];
        $home['value'][] = [
                'image' => "http://uat.api.brm.velosit.in/images/banner3.jpg",
                'description' => 'Lertnm fnf f',
                'value' => $this->getProduct($branch_id),
                        ];
        return $home;
    }

    private function shopByCategory($home)
    {
        $home['value'] = $this->getCategoryList();
        return $home;
    }
    /**
     * Undocumented function
     *
     * @param [type] $home
     * @return void
     */
    private function featuredProduct($home, $branch_id)
    {
        $home['value'] = $this->getProduct($branch_id);
        return $home;
    }

     /**
     * Fetch all categories
     *
     * @return \Illuminate\Support\Collection
     */
    private function getCategoryList()
    {
         return $this->parentCategory->with(['subcategories' => function($query){
                        $query->where('status', "1")
                        //->take(9)
                        ->with(['subcategory' => function ($qry) {
                            $qry->where('status', '1');
                        }]);
                    }])->select('parent_category_id','parent_category as parent_category_name','image_url','status')
                    ->take(9)
                    ->get();
    }

    public function getCredentials()
    {
        return AppConfig::select('brac_branch', 'brac_phone')->first();
    }

    private function getHomeProducts(array $items)
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
