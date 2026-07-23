<?php

namespace App\Http\Repositories\Item;
use Cache;
use BackOffice\Models\BranchInventory;
use Illuminate\Support\Facades\DB;
//use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection;
use App\Models\StockItemMaster;
use App\Models\Customer;
use BackOffice\Models\BranchGroup;

class ItemMasterCollection
{
    public function __construct() {}

    public function getProducts(Collection $filter = null, $virtualCategoryId = 0, $subcategories=null, $isDetailsView=0, $business_type=-1, $retailtype=-1, $storegroupid = -1)
    {
        if($storegroupid == -1)
        {
            $storegroupid = getHeaderStoreGroup();
        }
        $defaultbranchID = getHeaderBranch();
        $showSponsered = 1;
        $filterBranchId=0;

        if($storegroupid > 0)
        {
            $key = 'showSponsered_'.$storegroupid;
            $showSponsered = Cache::remember($key, 1, function () use ($storegroupid) {
                $data = BranchGroup::find($storegroupid);
                return (($data) ? $data->showSponsered : 1);
            });
        }
        if(($defaultbranchID > 0) && (!@$filter[0]['br_ID']))
        {
            $filter = collect();
            $filterPush['br_branch_id'] = $defaultbranchID;
            $filter->push($filterPush);
            $filterBranchId = $defaultbranchID;
        }
        $mylat=0; $mylng=0;
        $maxDirectDeliveryDistnace =  config('app.customer_location_to_branch_distance_circle_max')??0;
        if($maxDirectDeliveryDistnace == '')
            $maxDirectDeliveryDistnace = 0;

        // Stock 0 should not be filtered out for tenant site. It can be listed as sold out and order at bottom list. 0 stock items are not necessary to list in grozeo site.
        $stockCondition = ($storegroupid >0 || @$filter[0]['stit_id']) ? '' : 'AND (IFNULL(bi.item_count, 0) - IFNULL(blockedNum, 0)) > 0';
        $branchCondition = (@$filter[0]['br_ID']) ? 'AND b.br_ID='.$filter[0]['br_ID'] : '';
        $isGuest = true;
        $userState = 0;
        $usr = auth()->user();

        if($usr)
        {
            $isGuest = false;
            if(isset($usr->primaryAddress))
            {
                $mylat = $usr->primaryAddress->deli_latitude;
                $mylng = $usr->primaryAddress->deli_longitude;
                $userState = @$usr->primaryAddress->state->st_ID ? $usr->primaryAddress->state->st_ID : 0;
            }    
        }
        else
        {
            $getGuestLatLong = getGuestLocationFromHeader();
            $mylat = @$getGuestLatLong["lat"];
            $mylng = @$getGuestLatLong["long"];
        }

        $filterBusinessType='';
        if($storegroupid > 0 || $business_type > 0 || $retailtype > 0)
        {
            $filterBusinessType=' EXISTS (SELECT sc.sub_category_id FROM mypha_productsubcategory sc INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id 
                INNER JOIN mypha_productparent_category pc ON c.parent_category = pc.parent_category_id 
                ' . ($storegroupid > 0 ? ' INNER JOIN finascop_branch_group_business_type bgt on bgt.business_type_id= pc.parent_category_businessType and bgt.store_group_id=' . $storegroupid . ' ' : '') . ' 
                WHERE pc.STATUS=1 ' . ($business_type > 0 ? ' AND pc.parent_category_businessType = ' . $business_type : '') . ' AND sc.sub_category_id = product_category
                ' . ($retailtype > 0 ? ' AND EXISTS(SELECT * FROM retaline_business_category WHERE business_category_id='. $retailtype . ' AND FIND_IN_SET(pc.parent_category_businessType, rbc_business_type) > 0)' : '') . '
                )';
        }

        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $sellingpriceField = ($storegroupid > 0 ? 'CASE WHEN b.br_storeGroup = '. $storegroupid .' THEN selling_price WHEN b.br_storeGroup <> '. $storegroupid .' AND fpod_customerRateHmDel > fpod_customerRateCouDel THEN fpod_customerRateHmDel ELSE fpod_customerRateCouDel END' : 'case when issponsered <> 1 then selling_price when fpod_customerRateHmDel > fpod_customerRateCouDel then fpod_customerRateHmDel else fpod_customerRateCouDel end');

        $storeGroupFilter = '';
        if($storegroupid <= 0)
        {
            $storeGroupFilter = ' INNER JOIN finascop_branch_group bg ON bg.store_group_id = b.br_Storegroup AND bg.isFeatured=1 ';
        }

        $subqueryFields='bi.stit_id AS br_stit_id, b.br_ID AS br_branch_id, b.br_Name AS branch_name, br_SalesOnline, b.br_storeGroup, bi.mrp AS br_mrp, (' . $sellingpriceField . ') AS br_selling_price, (CASE WHEN b.br_storeGroup = '. $storegroupid .' THEN 0 ELSE 1 END) AS otherStore, (IFNULL(bi.item_count, 0) - IFNULL(blockedNum, 0)) AS br_stock, b.br_directDelivery, b.br_courierDelivery, fpod_customerRateHmDel + 0E0 as br_homeDelPrice, fpod_customerRateCouDel + 0E0 as br_courierPrice, fpod_customerRatePikup + 0E0 as br_pickupPrice, bi.issponsered, bi.hasSpotReturn as stit_custInitiate, bi.returnTime as stit_itemReturnTime, b.max_delivery_distance as b_max_delivery_distance, variantGroupId ';

        $subquery = '(SELECT * FROM (SELECT ' . ($isGuest && $mylat <= 0 ? '1' : 'calcDistance('.$mylat.', '.$mylng.', br_Lat, br_Lng)') . ' AS distance, '.$subqueryFields.' FROM finascop_stock_branch_inventory AS bi INNER JOIN finascop_branch AS b ON (b.br_ID=bi.branch_id OR (br_type = 1 AND b.br_typeParent = bi.branch_id)) ' . ($userState > 0 ? ' and (b.tradeRestriction=0 OR b.br_State='.$userState.')' : '') . ' and br_PyramidLevel=4 ' . ($filterBranchId > 0 ? '  AND b.br_ID= ' . $filterBranchId : '') . ' ' . $storeGroupFilter . ' LEFT JOIN (SELECT item_id, SUM(`count`) AS blockedNum FROM finascop_stock_blocked GROUP BY item_id) blocked ON blocked.item_id = bi.stit_id
        ' . ( $virtualCategoryId > 0 ? ' INNER JOIN retaline_vc_items AS vc ON vc.stit_id = bi.stit_ID AND vc.vc_id=' . $virtualCategoryId : '' ) . ' WHERE (bi.isAvailable = 1) '.$stockCondition.' '.$branchCondition.' AND b.br_status = \'Active\' '. ($storegroupid > 0 ? ' AND (b.br_storeGroup = ' . $storegroupid .($showSponsered == 0 ? '' : ' or bi.issponsered = 1'). ')' : '') .' GROUP BY bi.stit_id, b.br_ID ORDER BY otherStore, br_SalesOnline desc, distance ASC, bi.fpod_customerRatePikup ASC)bi GROUP BY br_stit_id) br';

        $items = StockItemMaster::selectRaw($this->selectFIelds($isDetailsView))
        ->leftJoin('finascop_stock_itemmastername as imname', function($join) { 
            $join->on('imname.itemname_id', '=', 'finascop_stock_itemmaster.stit_itemId');
            $join->on('finascop_stock_itemmaster.isMedicine', '=', DB::raw("0"));
          })
        ->join('mypha_productsubcategory as sc', 'sc.sub_category_id', 'finascop_stock_itemmaster.product_category')
        ->join(DB::raw($subquery), 'br.br_stit_id', '=', 'finascop_stock_itemmaster.stit_ID')
        ->with(['mainImage' => function ($qry) use($domain) {
            $qry->where('image_type', 1)
            ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));
        }])
        ->with(['additionalImage' => function ($qry) use ($domain, $isGuest) {
            $qry->where('image_type', 0)
                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
        }])
        ->with('quantityUnit:unit_id,unit_name')
        ->where([
            ['stit_status', 1],
            ['stit_HasChildItem', '<', 1],
            //['br_selling_price', '>', 0]
        ]);
        if($virtualCategoryId > 0){
            $items->join('retaline_vc_items as vc', 'vc.stit_id', 'finascop_stock_itemmaster.stit_ID')->where('vc.vc_id', DB::raw($virtualCategoryId));
        }
        if($subcategories != null)
        {
            $items->whereIn('product_category', $subcategories);
        }
        if($filterBusinessType != '')
        {
            $items->whereRaw($filterBusinessType);
        }
        if($filter!= null)
        {
            foreach($filter as $key => $val)
            {
                if(isset($val) && is_array($val))
                {
                    $strKey = array_keys($val)[0];
                    $strVal = array_values($val)[0];
                    if($strKey == 'attr')
                    {
                        $items->whereRaw('stit_id IN (SELECT DISTINCT stitid FROM `attributeProductMap` WHERE FIND_IN_SET(attributeValueId, \''.$strVal.'\') > 0)');
                    }
                    else
                    {
                        $items->where($strKey, $strVal);
                    }
                }
            }
        }
        if(!(@$filter[0]['variantGroupId'] > 0))
            $items->groupBy(DB::raw('(CASE WHEN variantGroupId <> 0 THEN variantGroupId ELSE finascop_stock_itemmaster.stit_ID * -1 END)'));

        return $items->orderBy('br.otherStore')->orderBy('branch_type_id', 'desc')->orderBy('percentage', 'desc');

    }

    

    private function selectFIelds($isDetailsView)
    {
        $select = "
            finascop_stock_itemmaster.stit_ID,
            stit_SKU,
            sc.sub_category_id,
            stit_category_name,
            stit_brand_name,
            stit_quantity as quantity,stit_fsiuid,br_stock + 0E0  as stock_available,
            br_selling_price + 0E0  as selling_prize,
            br_selling_price + 0E0  as selling_price,
            finascop_stock_itemmaster.isMedicine,cos_nos,
            br_mrp + 0E0  as mrp,
            imname.itemDisplayName as groupDisplayName,
            imname.IsItemGroup,
            imname.iteamGroupImage,
            CEILING(
                (
                    CASE 
                        WHEN br_mrp > 0 and br_selling_price > 0 THEN ((br_mrp - br_selling_price) / IFNULL(br_mrp, 0) * 100) 
                        ELSE 0
                    END
                )
            )  + 0E0 as percentage,
            stit_displaylabel as displaylabel,
            stit_foodtype,
            br_branch_id as branch_id,
            br_storeGroup,
            (
                CASE 
                    WHEN br_SalesOnline = 0 or br_stock <= 0 or br_selling_price <= 0 THEN 0 
                    WHEN distance <=  b_max_delivery_distance and br_directDelivery =1 and directDelivery = 1 THEN 3 
                    WHEN br_courierDelivery = 1 and courierDelivery = 1 THEN 1 
                    ELSE 0
                END
            ) as branch_type_id,
            otherStore,
            branch_name,
            br_homeDelPrice,
            br_courierPrice,
            br_pickupPrice,
            stgp_groupID,
            variantGroupId,
            stit_unit,
            stit_product_variant,
            stit_itemName,
            sc.hasRestaurantService,
            sc.hasAgeVerification,
            sc.isNonGstRetailer AS nonVATRestricted,
            sc.isPerishable, br_SalesOnline, 
            (CASE WHEN distance <= b_max_delivery_distance and br_directDelivery = 1 and directDelivery = 1 THEN 1 ELSE 0 END ) as is_express,
            (CASE WHEN br_courierDelivery = 1 and courierDelivery = 1 THEN 1 ELSE 0 END) as is_courier 
        ";
        if($isDetailsView == 1)
        {
            $select .= ", stit_Description as short_description,
            stit_long_description as long_description,
            (
                SELECT country_name FROM finascop_country WHERE country_id = stit_orgin_country
            ) as countryorgin,
            stit_orgin_country,
            stit_nutritionlabel,
            stit_preparation_use,
            stit_allergens,
            stit_ingredients,
            stit_foodtype,
            br.stit_custInitiate,
            br.stit_itemReturnTime";
        }
        return $select;
    }

    private function getItemFields()
    {
         return "fsi_uid,fsi_uid as item_group_id,fsi_item_name as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,finascop_stock_uniqueitem.isMedicine, courierDelivery, directDelivery, br_branch_id as branch_id, br_storeGroup, br_directDelivery, br_courierDelivery";

    }

}
