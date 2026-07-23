<?php

namespace App\Http\Repositories\Item;
use Cache;
use BackOffice\Models\BranchInventory;
use Illuminate\Support\Facades\DB;
//use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection;
use App\Models\StockUniqueItem;
use BackOffice\Models\BranchGroup;

class ItemMasterCollection {

    protected $uniqueItem;

    public function __construct( StockUniqueItem $uniqueItem)
    {        
        $this->uniqueItem = $uniqueItem;
    }

    public function getProducts(Collection $filter = null, $virtualCategoryId = 0, $subcategories=null, $isDetailsView=0, $business_type=-1, $retailtype=-1){

        $storegroupid = getHeaderStoreGroup();
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

        // $stockCondition = (@$filter[0]['stit_id']) ? '' : 'AND ( bi.issponsered != 1 or (IFNULL(bi.item_count, 0) - IFNULL(blockedNum, 0)) > 0)';
        $stockCondition = (@$filter[0]['stit_id']) ? '' : 'AND ( bi.issponsered != 1 or (IFNULL(bi.item_count, 0) - IFNULL(blockedNum, 0)) > 0)';
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

        $filterBusinessType='';
        if($storegroupid > 0 || $business_type > 0 || $retailtype > 0){
            $filterBusinessType=' EXISTS (SELECT c.category_id from mypha_productcategory c INNER JOIN mypha_productparent_category pc ON c.parent_category = pc.parent_category_id 
                ' . ($storegroupid > 0 ? ' INNER JOIN finascop_branch_group_business_type bgt on bgt.business_type_id= pc.parent_category_businessType and bgt.store_group_id=' . $storegroupid . ' ' : '') . ' 
                WHERE pc.STATUS=1 ' . ($business_type > 0 ? ' AND pc.parent_category_businessType = ' . $business_type : '') . ' AND c.category_id = sc.main_category 
                ' . ($retailtype > 0 ? ' AND EXISTS(SELECT * FROM retaline_business_category WHERE business_category_id='. $retailtype . ' AND FIND_IN_SET(pc.parent_category_businessType, rbc_business_type) > 0)' : '') . '
                )';
        }

        //else if($retailtype > 0){
        //    $filterBusinessType=' EXISTS (SELECT sc.sub_category_id FROM mypha_productsubcategory sc INNER JOIN mypha_productcategory c ON sc.main_category=c.category_id 
        //        INNER JOIN mypha_productparent_category pc ON c.parent_category = pc.parent_category_id WHERE pc.STATUS=1 AND sc.sub_category_id = product_category AND
        //        EXISTS(SELECT * FROM retaline_business_category WHERE FIND_IN_SET(pc.parent_category_businessType, rbc_business_type) > 0)
        //        )';
        //}

        //  DB::enableQueryLog();
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        //$sellingpriceField = ($storegroupid > 0 ? 'case when issponsered = 1 and br_type = 1 then fpod_customerRateCouDel when issponsered = 1 and br_type = 3 then fpod_customerRateHmDel else selling_price end' : 'case when br_type = 1 then fpod_customerRateCouDel else fpod_customerRateHmDel end');
        $sellingpriceField = ($storegroupid > 0 ? 'CASE WHEN b.br_storeGroup = '. $storegroupid .' THEN selling_price WHEN b.br_storeGroup <> '. $storegroupid .' AND fpod_customerRateHmDel > fpod_customerRateCouDel THEN fpod_customerRateHmDel ELSE fpod_customerRateCouDel END' : 'case when issponsered <> 1 then selling_price when fpod_customerRateHmDel > fpod_customerRateCouDel then fpod_customerRateHmDel else fpod_customerRateCouDel end');

        $storeGroupFilter = '';
        if($storegroupid <= 0)
            $storeGroupFilter = ' INNER JOIN finascop_branch_group bg ON bg.store_group_id = b.br_Storegroup AND bg.isFeatured=1 ';

        $subqueryFields='bi.stit_id AS br_stit_id, b.br_ID AS br_branch_id, b.br_Name AS branch_name, br_SalesOnline, b.br_storeGroup, bi.mrp AS br_mrp, (' . $sellingpriceField . ') AS br_selling_price, (CASE WHEN b.br_storeGroup = '. $storegroupid .' THEN 0 ELSE 1 END) AS otherStore, (IFNULL(bi.item_count, 0) - IFNULL(blockedNum, 0)) AS br_stock,
        b.br_directDelivery, b.br_courierDelivery, fpod_customerRateHmDel + 0E0 as br_homeDelPrice, fpod_customerRateCouDel + 0E0 as br_courierPrice, fpod_customerRatePikup + 0E0 as br_pickupPrice, bi.issponsered, bi.hasSpotReturn as stit_custInitiate, bi.returnTime as stit_itemReturnTime, b.max_delivery_distance as b_max_delivery_distance,variantGroupId ';

        // Satallie branch will be added with the join: OR (br_type = 1 AND b.br_typeParent = bi.branch_id). stock and other data from bi table will be for the parent branch.
        // Only branch id will be satellite branch when joined with parent id for satellite branch.
        // b.br_ID can be the id of satellite or original/parent branch. bi.branch_id is the actual stock branch. In the select b.br_ID will be used because of getting the satalite branch id if it is nearby.
        $subquery = '(SELECT * FROM(SELECT calcDistance('.$mylat.', '.$mylng.', br_Lat, br_Lng) AS distance, '.$subqueryFields.'
        FROM finascop_stock_branch_inventory AS bi INNER JOIN finascop_branch AS b ON (b.br_ID=bi.branch_id OR (br_type = 1 AND b.br_typeParent = bi.branch_id)) ' . ($isGuest ? '' : ' and (b.tradeRestriction=0 OR b.br_State='.$userState.')') . ' and br_PyramidLevel=4 ' . ($filterBranchId > 0 ? '  AND b.br_ID= ' . $filterBranchId : '') . ' 
        ' . $storeGroupFilter . ' LEFT JOIN (SELECT item_id, SUM(`count`) AS blockedNum FROM finascop_stock_blocked GROUP BY item_id) blocked ON blocked.item_id = bi.stit_id
        ' . ( $virtualCategoryId > 0 ? ' INNER JOIN retaline_vc_items AS vc ON vc.stit_id = bi.stit_ID AND vc.vc_id=' . $virtualCategoryId : '' ) . ' 
        WHERE (bi.isAvailable = 1) '.$stockCondition.' '.$branchCondition.' AND b.br_status = \'Active\' '. ($storegroupid > 0 ? ' AND (b.br_storeGroup = ' . $storegroupid .($showSponsered == 0 ? '' : ' or bi.issponsered = 1'). ')' : '') .'
        GROUP BY bi.stit_id, b.br_ID ORDER BY otherStore, distance ASC, bi.fpod_customerRatePikup ASC)bi GROUP BY br_stit_id) br';

        $item = $this->uniqueItem
             ->join('finascop_stock_itemmaster as fs', 'fs.stit_fsiuid', 'finascop_stock_uniqueitem.fsi_uid')
             ->join('mypha_productsubcategory as sc', 'sc.sub_category_id', 'fs.product_category')
                ->join(DB::raw($subquery), function($join)
                        {
                           $join->on('br.br_stit_id', '=', 'fs.stit_ID');
                        })
             ->leftJoin('finascop_stock_itemmastername as imname', function($join) { 
                  $join->on('imname.itemname_id', '=', 'fs.stit_itemId');
                  $join->on('fs.isMedicine', '=', DB::raw("0"));
                })
               ->where('finascop_stock_uniqueitem.isMedicine', 0)->where('stit_HasChildItem', '<', 1)
             ->with(['itemMaster' => function ($query) use($domain, $subquery, $filter, $maxDirectDeliveryDistnace, $isDetailsView, $filterBusinessType, $isGuest) {
                    $query->join('mypha_productsubcategory as sc', 'sc.sub_category_id', 'finascop_stock_itemmaster.product_category')
                    ->join(DB::raw($subquery), 'br.br_stit_id', '=', 'finascop_stock_itemmaster.stit_ID')
                    ->selectRaw("stit_ID, stit_SKU, stit_category_name, stit_brand_name, stit_quantity as quantity,stit_fsiuid,br_stock + 0E0  as stock_available, br_selling_price + 0E0  as selling_prize, br_selling_price + 0E0  as selling_price, finascop_stock_itemmaster.isMedicine,cos_nos, br_mrp + 0E0  as mrp, CEILING((case when br_mrp > 0 and br_selling_price > 0 then ((br_mrp - br_selling_price) / NULLIF(br_mrp,0) * 100) else 0 end))  + 0E0 as percentage, stit_displaylabel as displaylabel, stit_foodtype,
                    br_branch_id as branch_id, br_storeGroup, (CASE WHEN br_SalesOnline =0 THEN 0 WHEN distance <=  b_max_delivery_distance and br_directDelivery =1 and directDelivery = 1 THEN 3 WHEN br_courierDelivery = 1 and courierDelivery = 1 THEN 1  ELSE 0 END) as branch_type_id, otherStore,branch_name,
                    br_homeDelPrice, br_courierPrice, br_pickupPrice, stgp_groupID, variantGroupId, stit_unit, stit_product_variant, stit_itemName, sc.hasRestaurantService, sc.hasAgeVerification, sc.isNonGstRetailer AS nonVATRestricted, sc.isPerishable" . ($isDetailsView == 1 ? ", stit_Description as short_description, stit_long_description as long_description,(SELECT country_name FROM finascop_country WHERE country_id = stit_orgin_country) as countryorgin,stit_orgin_country,stit_nutritionlabel,stit_preparation_use,stit_allergens,stit_ingredients,stit_foodtype,br.stit_custInitiate ,br.stit_itemReturnTime ":""))
                        ->with(['mainImage' => function ($qry) use($domain) {
                            $qry->where('image_type', 1)
                            ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));
                        }])
            ->with(['additionalImage' => function ($qry) use ($domain, $isGuest) {
                        $qry->where('image_type', 0)
                            ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                    }])
            ->with('quantityUnit:unit_id,unit_name');
                    $query->where('stit_status',1)->where('stit_HasChildItem', '<', 1)->where('br_selling_price', '>', 0); //->having('branch_type_id', '>', 0);

                    /* if(!$isGuest)
                        $query->having('branch_type_id', '>', 0); */

                    if($filterBusinessType != '')
                        $query->whereRaw($filterBusinessType);

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
                                    $query->whereRaw(' product_category IN ( 
                                        SELECT DISTINCT subCategoryId FROM attributeValue av 
                                        INNER JOIN attributeSubcategoryMap asm ON asm.attributeId=av.attributeId 
                                        WHERE id IN ('.$strVal.'))
                                    ');
                                }
                                else
                                {
                                    $query->where($strKey, $strVal);
                                }
                            }
                        }
                    }

             }])
             ->selectRaw($this->getItemFields() . ', imname.itemDisplayName as groupDisplayName, imname.IsItemGroup, imname.iteamGroupImage, stit_itemId as groupId, CEILING((case when br_mrp > 0 and br_selling_price > 0 then ((br_mrp - br_selling_price) / NULLIF(br_mrp,0) * 100) else 0 end))  + 0E0 as percentage');
             if($virtualCategoryId > 0){
                $item->join('retaline_vc_items as vc', 'vc.stit_id', 'fs.stit_ID')
                    ->where('vc.vc_id', DB::raw($virtualCategoryId));
            }
            if($subcategories != null)
                $item->whereIn('product_category', $subcategories);

            if($storegroupid > 0)
                $item = $item->whereRaw('(br.br_storeGroup = '.$storegroupid.($showSponsered == 0 ? '' : ' or br.issponsered = 1').')');
                //$item = $item->where('br.br_storeGroup', '=', DB::raw($storegroupid));

            if(!$isGuest && $isDetailsView != 1){
                $item->where(function($query) use ($maxDirectDeliveryDistnace, $storegroupid){
                    /* $query->where([
                        ['distance', '<=', 'b_max_delivery_distance'],
                        ['br.br_directDelivery', '=', DB::raw(1)],
                        ['fs.directDelivery', '=', DB::raw(1)]
                    ])->orWhere([
                        // ['distance', '>', DB::raw($maxDirectDeliveryDistnace)],
                        ['br.br_courierDelivery', '=', DB::raw(1)],
                        ['fs.courierDelivery', '=', DB::raw(1)]
                    ]); */
                    $query->whereRaw('((br.br_storeGroup = '.$storegroupid . ') or (distance <= b_max_delivery_distance AND br.br_directDelivery = 1 AND fs.directDelivery = 1) OR (br.br_courierDelivery = 1 AND fs.courierDelivery = 1))');

                });
            }
            if($filterBusinessType != '')
                $item->whereRaw($filterBusinessType);

             $item=$item->where("fs.stit_status", 1)
             ->groupBy('fsi_uid');


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
                            $filterSql = ' product_category IN ( 
                                SELECT DISTINCT subCategoryId FROM attributeValue av 
                                INNER JOIN attributeSubcategoryMap asm ON asm.attributeId=av.attributeId 
                                WHERE id IN ('. $strVal .'))
                            ';
                            $item->whereRaw($filterSql);
                        }
                        else
                        {
                            $item->where($strKey, $strVal);
                        }
                    }
                }
            }
            //$item =$item->get(); //take(9)->get();
           
        return $item->orderBy('br.otherStore')->orderBy('percentage', 'desc');
    }

    private function getItemFields()
    {
         return "fsi_uid,fsi_uid as item_group_id,fsi_item_name as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,finascop_stock_uniqueitem.isMedicine, courierDelivery, directDelivery, br_branch_id as branch_id, br_storeGroup, br_directDelivery, br_courierDelivery";

    }

}
