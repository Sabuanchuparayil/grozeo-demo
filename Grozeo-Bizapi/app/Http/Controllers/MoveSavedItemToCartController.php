<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\Cart\CartRepository;
use App\Http\Repositories\Wishlist\SavedItemRepository;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\ErrorResponse;

class MoveSavedItemToCartController extends Controller
{
    protected $savedItem;

    protected $cart;

    public function __construct(SavedItemRepository $savedItem, CartRepository $cart)
    {
        $this->savedItem = $savedItem;
        $this->cart = $cart;
    }

    public function index($group_id,$productId,$order_method)
    {
        $storegroupid = getHeaderStoreGroup();
        $mylat=0; $mylng=0;
        $maxDirectDeliveryDistnace =  config('app.customer_location_to_branch_distance_circle_max')??0;
        if($maxDirectDeliveryDistnace == '')
            $maxDirectDeliveryDistnace = 0;

        $usr = json_decode(auth()->user());
        if(isset($usr) && isset($usr->primary_address)){
            $mylat = $usr->primary_address->deli_latitude;
            $mylng = $usr->primary_address->deli_longitude;
            
        }       

        $subquery = '(SELECT * FROM(SELECT calcDistance('.$mylat.', '.$mylng.', br_Lat, br_Lng) AS distance, bi.stit_id AS br_stit_id, bi.branch_id AS br_branch_id, b.br_storeGroup, bi.mrp AS br_mrp, bi.fpod_customerRatePikup AS br_selling_price, (IFNULL(bi.item_count, 0) - IFNULL(blockedNum, 0)) AS br_stock,
        b.br_directDelivery, b.br_courierDelivery, fpod_customerRateHmDel + 0E0 as br_homeDelPrice, fpod_customerRateCouDel + 0E0 as br_courierPrice, fpod_customerRatePikup + 0E0 as br_pickupPrice
        FROM finascop_stock_branch_inventory AS bi INNER JOIN finascop_branch AS b ON b.br_ID=bi.branch_id LEFT JOIN (SELECT item_id, SUM(`count`) AS blockedNum
            FROM finascop_stock_blocked GROUP BY item_id) blocked ON blocked.item_id = bi.stit_id WHERE (IFNULL(bi.item_count, 0) - IFNULL(blockedNum, 0)) > 0  AND b.br_status = \'Active\'
            '. ($storegroupid > 0 ? ' AND b.br_storeGroup = ' . $storegroupid : '') .'  GROUP BY bi.stit_id, b.br_ID ORDER BY distance ASC, bi.fpod_customerRatePikup ASC)bi GROUP BY br_stit_id) br';

        $sql = 'SELECT fs.stit_ID, br_branch_id AS branch_id,  (CASE WHEN distance <=  10 AND br_directDelivery =1 AND directDelivery = 1 THEN 3 WHEN distance >  10 AND br_courierDelivery = 0 AND courierDelivery = 0 THEN 0  ELSE 1 END) AS branch_type_id, br_homeDelPrice, br_courierPrice, br_pickupPrice
FROM finascop_stock_itemmaster fs INNER JOIN ' . $subquery . ' ON fs.stit_ID = br.br_stit_id WHERE fs.stit_status = 1 AND br_selling_price > 0 AND fs.stit_ID = '. $productId . '  limit 1';

        $productBranch = DB::select($sql); //->first();

        if(!isset($productBranch) || count($productBranch) <1)
            return new ErrorResponse('We apologize, but the product is not in stock at the stores close to your current address.');

        $this->savedItem->delete($group_id,$productId,$order_method);
        $this->cart->store([
            'cart_product_id' => $productId,
            'cart_group_id'=>$group_id,
            'cart_order_qty' => 1,
            'cart_branch_id' => $productBranch[0]->branch_id,
            'branch_type_id' => $productBranch[0]->branch_type_id
        ]);
        return new SuccessWithData([
            'product_id' => $productId
        ]);
    }

}
