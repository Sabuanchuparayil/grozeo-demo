<?php

namespace App\Http\Repositories\Wishlist;

use App\Models\Customer;
use App\Models\SavedItem;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\ItemPrice;
use App\Http\Repositories\Item\Price;
use App\Http\Repositories\Item\Stock;
use App\Http\Repositories\StockAvailable;
use App\Http\Repositories\Item\CheapPrice;
use App\Http\Repositories\Cart\CartRepository;
use App\Http\Repositories\Item\ItemMasterCollection;

class SavedItemRepository
{
    protected $savedItem;
    protected $customer;
    protected $_itemMasterCollection;

    public function __construct(SavedItem $savedItem,Customer $customer, ItemMasterCollection $itemMasterCollection)
    {
        $this->savedItem = $savedItem;
        $this->customer=$customer;
        $this->_itemMasterCollection = $itemMasterCollection;
    }
    public function create($data)
    {
        $data['customer_id'] = auth()->user()->cust_id;
        $data['storegroupid'] = getHeaderStoreGroup();
        return $this->savedItem->updateOrCreate($data);
    }
    public function getIDs($order_method)
    {
        $savedItem =$this->savedItem->select('product_id')->where('order_method', $order_method)->where('customer_id', auth()->user()->cust_id)->get();
        return $savedItem->toArray();
    }
    public function get($order_method)
    {
        $storegroupid = getHeaderStoreGroup();
        $mylat=0; $mylng=0;
        $maxDirectDeliveryDistnace =  config('app.customer_location_to_branch_distance_circle_max')??0;
        if($maxDirectDeliveryDistnace == '')
            $maxDirectDeliveryDistnace = 0;

        $usr = $usr = auth()->user(); //json_decode(auth()->user());
        if(isset($usr)){
            $latlang = DB::select('SELECT deli_latitude, deli_longitude FROM retaline_customer_delivery_info WHERE deli_is_primary=1 AND deli_customer_id='. $usr->cust_id . ' limit 1');
            if(isset($latlang) && count($latlang) >0){
                $mylat = $latlang[0]->deli_latitude;
                $mylng = $latlang[0]->deli_longitude;
            }
        }       

        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $subquery = '(SELECT * FROM(SELECT calcDistance('.$mylat.', '.$mylng.', br_Lat, br_Lng) AS distance, bi.stit_id AS br_stit_id, bi.branch_id AS br_branch_id, b.br_storeGroup, bi.mrp AS br_mrp, bi.selling_price AS br_selling_price, (IFNULL(bi.item_count, 0) - IFNULL(blockedNum, 0)) AS br_stock,
        b.br_directDelivery, b.br_courierDelivery, fpod_customerRateHmDel + 0E0 as br_homeDelPrice, fpod_customerRateCouDel + 0E0 as br_courierPrice, fpod_customerRatePikup + 0E0 as br_pickupPrice
        FROM finascop_stock_branch_inventory AS bi INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id LEFT JOIN (SELECT item_id, SUM(`count`) AS blockedNum
            FROM finascop_stock_blocked GROUP BY item_id) blocked ON blocked.item_id = bi.stit_id WHERE (IFNULL(bi.item_count, 0) - IFNULL(blockedNum, 0)) > 0  AND b.br_status = \'Active\'  GROUP BY bi.stit_id, b.br_ID ORDER BY distance ASC, bi.fpod_customerRatePikup ASC)bi GROUP BY br_stit_id) br';

        $brtypeidfield = "(CASE WHEN distance <=  ". $maxDirectDeliveryDistnace ." and br_directDelivery =1 and directDelivery = 1 THEN 3 WHEN br_courierDelivery = 1 and courierDelivery = 1 THEN 1  ELSE 0 END) as branch_type_id";
        $savedItem = [];
        if(auth()->check())
        {
            $savedItem = $this->_itemMasterCollection->getProducts(null, 0, null, 1)
            ->join('retaline_saved_items as sv', 'sv.product_id', 'stit_id')
            ->where([
                ['sv.order_method', $order_method],
                ['sv.storegroupid', $storegroupid],
                ['sv.customer_id', $usr->cust_id]
            ])->get();
        }
        return $savedItem;
    }

    public function delete($groupId,$productId,$order_method)
    {
       // dd($productId);
        $this->savedItem
            ->where('customer_id', auth()->user()->cust_id)
            ->where('product_id', $productId)
            ->where('group_id', $groupId)
            ->where('order_method',$order_method)
            ->delete();

        return $productId;
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
            "fsi_variant as variant",
            "isMedicine"
        ];
    }
    /**
     *Item master table fields.
     *
     * @return Array
     */
    private function itemMasterFields()
    {
        return [
        'stit_ID',
        'stit_fsiuid',
        'stit_quantity as quantity',
        'stit_ID as itemId',
        // 'stit_ID as short_description',
        //'stit_ID as long_description',
        'cos_nos',
        'isMedicine',
        'stit_SKU',
        DB::raw('ifnull(br_branch_id, 0) + 0E0 as branch_id'),
        DB::raw('ifnull(br_mrp, 0) + 0E0 as mrp'),
        DB::raw('ifnull(br_selling_price, 0) + 0E0 as selling_price'),
        DB::raw('ifnull(br_stock, 0) + 0E0 as stock_available')
        ];
    }


     /**
     * Add Stock details in Wishlist..
     *
     * @param array $item
     * @return void
     */
    private function addFields(array $item)
    {
        $products = $this->findSavedProducts($item);

        $stock = Stock::getStock($products['product'], $products['branch']);
        $price = Price::findPrice($products['product'], $products['branch']);
        $cheap = CheapPrice::getDefault($products['group'], $stock, $price);

        foreach ($item as $key => $itm) {
            $count = count($item[$key]['item']['item_master']);
            for ($i = 0; $i < $count; $i++)
            {
                /*$stitId = $item[$key]['item']['item_master'][$i]['stit_ID'];
                $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
                $mrp = array_key_exists($stitId, $price['mrp']) ? $price['mrp'][$stitId] : 0;
                $selling_price = array_key_exists($stitId, $price['selling_price']) ? $price['selling_price'][$stitId] : 0;*/

                $stitId = $item[$key]['item']['item_master'][$i]['stit_ID'];
                $cos_nos= $item[$key]['item']['item_master'][$i]['cos_nos'];
                $cos_nos = ($cos_nos > 0)?$cos_nos:1;
                $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
                $mrp = array_key_exists($stitId, $price['fpod_leastSKUmrp']) ? $price['fpod_leastSKUmrp'][$stitId] *$cos_nos : 0;
                //$order_method= \Session::get('order_method');
             
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
                $percentage=($mrp>0)?(($mrp - $selling_price)*100 /$mrp):0 ;
                $default_val = in_array($stitId, $cheap) ? 1 : 0;
                
                $mrp=round($mrp,2);
                $selling_price=round($selling_price,2);
                $percentage=round($percentage,2);

                $item[$key]['item']['item_master'][$i]['stock_available'] = $stock_count;
                $item[$key]['item']['item_master'][$i]['selling_prize'] = $selling_price;
                $item[$key]['item']['item_master'][$i]['selling_price'] = $selling_price;
                $item[$key]['item']['item_master'][$i]['mrp'] = $mrp;
                $item[$key]['item']['item_master'][$i]['godown_itemId'] = app(CartRepository::class)->getStock();
                $item[$key]['item']['item_master'][$i]['default_value'] = $default_val;
                $item[$key]['item']['item_master'][$i]['percentage'] = $percentage;
            }


        }
        
        return $item;
    }

    private function  findSavedProducts(array $items)
    {
        $group_product=array();
        $product_id=array();

        foreach($items as $Itm)
        {
            $group_product=array();
            $branch = $Itm['branch_id'];
            $products = $Itm['item']['item_master'];
            $group_id = $Itm['item']['fsi_uid'];

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
            'branch' => $branch,
            'group' => $group,
        ];
    }


}
