<?php
use Tymon\JWTAuth\Facades\JWTAuth;
//use Tymon\JWTAuth\Facades\JWTFactory
use Carbon\carbon;

use Illuminate\Support\Facades\DB;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Helpers\HttpCurlCalls;

if (!function_exists('auth_user')) {
    function auth_user()
    {
        return request()->get('authUser');
    }
}

if (!function_exists('get_es_index')) {
    function get_es_index()
    {
        return config('app.app_env') === 'uat' ?
            'products_uat' :
            'products';
    }
}
function getUserAgentType(){
    $userAgent=request()->server('HTTP_USER_AGENT');
    $userAgentType="web";
    if (strpos(strtolower( $userAgent ), 'android' ) !== false){
        $userAgentType="android";
    }
    if(strpos(strtolower( $userAgent ), 'iphone' ) !== false){
        $userAgentType="iphone";
    }

    return $userAgentType;    
    
}
function createJwtToken($data){
    $type=getUserAgentType();
    $customClaims=["aud"=>$type];
    if(isset($data["dummy"]) && $data["dummy"]==true){
        $customClaims["dummy"]=1;
    }
    if($type=="web"){
//        $customClaims["exp"]=Carbon::now()->addDays(15)->timestamp;
    }
    $token =JWTAuth::customClaims($customClaims)
        ->fromUser($data);

    return $token;
}
function getBranchIdForll(){

/*
     $result=Branch::select( array('finascop_branch.br_ID'))
                ->join('finascop_branch as dis', 'dis.br_id', 'finascop_branch.br_cpd')
                ->join('finascop_branch as cs', 'cs.br_id', 'dis.br_cpd')
                ->where('cs.br_csdefault',1)
                ->where('cs.br_PyramidLevel',2)
                ->where('dis.br_csdefault',1)
                ->where('dis.br_PyramidLevel',3)
                ->where('finascop_branch.br_PyramidLevel',4)
                ->where('finascop_branch.br_csdefault','=',1)->first();
           
    return $result->br_ID;
*/
return 0;
}

function getHeaderStoreGroup(){
    $storegroupid = 0;
    $headers = collect(request()->headers->all())->transform(function ($item) {
        return $item[0];
    });
    if(isset($headers['defaultstoregroupid']) && intval($headers['defaultstoregroupid']) >0 ){
        $storegroupid=$headers["defaultstoregroupid"];
    }

    return $storegroupid;
}

 function getDegreeMatrix($mylon, $mylat, $dist) {
    $kmtomile = $dist * 0.623;
    $lon1 = $mylon - $kmtomile / abs(cos(deg2rad($mylat)) * 69);
    $lon2 = $mylon + $kmtomile / abs(cos(deg2rad($mylat)) * 69);
    $lat1 = $mylat - ($kmtomile / 69);
    $lat2 = $mylat + ($kmtomile / 69);
    return array("kmtomile" => $kmtomile, "lon1" => $lon1, "lon2" => $lon2, "lat1" => $lat1, "lat2" => $lat2);
}

function GetDrivingDistance($lat1, $lat2, $long1, $long2)
{
    $apiKey = config("drivers.gmap_dist_api_key") ?? env('GOOGLE_MAPS_DISTANCE_API_KEY', '');
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?key={$apiKey}&origins={$lat1},{$long1}&destinations={$lat2},{$long2}";
    $data = (new HttpCurlCalls)->curlCall($url, [], 'GET', []);
    $distance = 0;
    if(@$data->rows)
    {
        $rows = reset($data->rows);
        $elements = @reset($rows->elements);
        $distance = $elements->distance->value;
    }
    return ($distance > 0) ? round($distance / 1000, 2) : 0;
}

function sortedData($arrList, $key)
{
    $sorter = array();
    $returns = array();
    // reset($array);
    foreach ($arrList as $ii => $va)
    {
        if(in_array($key, array_keys($va)))
        {
            $sorter[$ii] = $va[$key];
        }
    }
    asort($sorter);
    foreach ($sorter as $ii => $va)
    {
        array_push($returns, $sorter[$ii]);
    }
    return $returns;
}

 function getNearestAerialBranches($mylat, $mylon,  $dist) {
    $storegroupid = getHeaderStoreGroup();
    
    $degMat = getDegreeMatrix($mylon, $mylat, $dist);
    $kmtomile = $degMat['kmtomile'];
    $lon1 = $degMat['lon1'];
    $lon2 = $degMat['lon2'];
    $lat1 = $degMat['lat1'];
    $lat2 = $degMat['lat2'];
    //DB::enableQueryLog();  
    $result = DB::table('finascop_branch')
    ->selectraw('finascop_branch.*,(3956 * 2 * ASIN(SQRT( POWER(SIN((ABS('. $mylat .') - ABS(br_Lat)) * PI()/180 / 2), 2) + COS(ABS('.$mylat . ') * PI()/180) * COS(ABS(br_Lat) * PI()/180) * POWER(SIN((' . $mylon .'-br_Lng) * PI()/180 / 2), 2) )))/0.623 AS distance ')
    ->whereRaw("br_Lng BETWEEN $lon1 AND $lon2 AND br_Lat BETWEEN $lat1 AND $lat2 AND br_PyramidLevel = 4  AND br_status = 'Active'")
    //->where('br_storeGroup', DB::raw($storegroupid))
    ->orderBy('distance', 'asc');

    if($storegroupid > 0)
    	$result->where('br_storeGroup', DB::raw($storegroupid));

    $result = $result->get();
    return $result;
}

/// Get the nearest third party retailer inventories within 10 kilometers.
///$productIds is an aray of out of stock product ids, to check within the nearest retailers.
 function getNearestRetailerInventories($productIds) {
    
    $usr = Auth::user();

    // If guest user then show the minimal price from any branch to avoid out of stock for guest.
    if(!$usr){
    	//return null;
    	return BranchInventory::whereIn('stit_id', $productIds)
		->where('item_count','>',0)
		->groupBy('stit_id')
		->select('branch_id', 'item_count','stit_id', 'mrp', DB::raw('MIN(fpod_customerRatePikup) as selling_price'))
		->get();
    }

    // if address_to_nearestbranch is true then no thirdparty retailer stock.
        if(config('app.address_to_nearestbranch') == true){
            return null;
        }

    $latlang = DB::select('SELECT deli_latitude, deli_longitude FROM retaline_customer_delivery_info WHERE deli_is_primary=1 AND deli_customer_id='. $usr->cust_id . ' limit 1');
    if(!$latlang)
	return null;

    $degMat = getDegreeMatrix($latlang[0]->deli_longitude, $latlang[0]->deli_latitude, config('app.customer_location_to_branch_distance_circle_max'));
    $kmtomile = $degMat['kmtomile'];
    $lon1 = $degMat['lon1'];
    $lon2 = $degMat['lon2'];
    $lat1 = $degMat['lat1'];
    $lat2 = $degMat['lat2'];
    $result = DB::table('finascop_branch')
    ->selectraw('finascop_branch.*,(3956 * 2 * ASIN(SQRT( POWER(SIN((ABS('. $latlang[0]->deli_latitude .') - ABS(br_Lat)) * PI()/180 / 2), 2) + COS(ABS('.$latlang[0]->deli_latitude . ') * PI()/180) * COS(ABS(br_Lat) * PI()/180) * POWER(SIN((' . $latlang[0]->deli_longitude .'-br_Lng) * PI()/180 / 2), 2) )))/0.623 AS distance ')
    ->whereRaw("br_Lng BETWEEN $lon1 AND $lon2 AND br_Lat BETWEEN $lat1 AND $lat2 AND br_PyramidLevel = 4  AND br_status = 'Active'");

    $branchInventory = BranchInventory::joinSub($result, 'latest_posts', function ($join) {
	    $join->on('branch_id', '=', 'latest_posts.br_ID');
        })
	->whereIn('stit_id', $productIds)
	->where('item_count','>',0)
	->groupBy('stit_id')
	->select('branch_id', 'item_count','stit_id', 'mrp', 'fpod_customerRatePikup as selling_price', DB::raw('MAX(distance) as mindistance'))
	->get();

    return $branchInventory;
}

function getCurrentUserBranch(){
    $branch_id= 0;
    try{
        if(isset(auth()->user()->primaryAddress))
            $branch_id = auth()->user()->primaryAddress->deli_branch_id;
    } catch (\Exception $e) {
        $branch_id= 0;
    }
    return $branch_id;
}

/// Common function, used for all products listings, to add thirdparty retailer stock also if it is not available in default branch or rate contract.
///$outOfStock is an array of item ids. 
///$item is the original master data going to send back by the controller after filter. 
///This object will be returned back, after attaching the third party retailer stock also if available.
function SetRetailerStock($outOfStock_ids, $item){

    if(count($outOfStock_ids) > 0){
        $retailerProducts = getNearestRetailerInventories($outOfStock_ids);
        if($retailerProducts){
            foreach ($item as $key => $itm) {
                $count = count($item[$key]['item_master']);
                for ($i = 0; $i < $count; $i++) {                        
                    $stitId = $item[$key]['item_master'][$i]['stit_ID'];
                    if (!$retailerProducts->contains('stit_id', $stitId))
                        continue;

                    $retailerItem = $retailerProducts->where('stit_id', $stitId)->first();
                    if($retailerItem){
                        // if retailer item is available, then replace the stock, mrp and selling price with the retailer values.
                        $selling_price = $retailerItem->selling_price;
                        $mrp = $retailerItem->mrp;
                        $percentage=($mrp>0)?((($mrp - $selling_price)*100) /$mrp):0 ;

                        $item[$key]['item_master'][$i]['stock_available'] = $retailerItem->item_count;
                        $item[$key]['item_master'][$i]['mrp'] = round($mrp,2);
                        $item[$key]['item_master'][$i]['selling_prize'] = round($selling_price,2);
                        $item[$key]['item_master'][$i]['selling_price'] = round($selling_price,2);
                        $item[$key]['item_master'][$i]['percentage'] = round($percentage, 2);
                        $item[$key]['item_master'][$i]['branch_id'] = $retailerItem->branch_id;
                        $item[$key]['item_master'][$i]['branch_type_id'] = 3;
                        // branch type id = 3 represents that the price is from third party retailer. Default value is 1-> default store. 2 represents RC (rate contract)
                    }
                }
            }
        }
    }
	return $item;
}

