<?php
use Tymon\JWTAuth\Facades\JWTAuth;
//use Tymon\JWTAuth\Facades\JWTFactory
use Carbon\carbon;

use Illuminate\Support\Facades\DB;

use App\Models\Branch;
use App\Helpers\HttpCurlCalls;
use BackOffice\Models\BranchInventory;

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

function getHeaderBranch()
{
    $branchid = 0;
    $headers = collect(request()->headers->all())->transform(function ($item) {
        return $item[0];
    });
    if(isset($headers['defaultbranchid']) && intval($headers['defaultbranchid']) > 0 )
    {
        $branchid=$headers["defaultbranchid"];
    }
    return $branchid;
}

 function getDegreeMatrix($mylon, $mylat, $dist) {
    $kmtomile = $dist * 0.623;
    $lon1 = $mylon - $kmtomile / abs(cos(deg2rad($mylat)) * 69);
    $lon2 = $mylon + $kmtomile / abs(cos(deg2rad($mylat)) * 69);
    $lat1 = $mylat - ($kmtomile / 69);
    $lat2 = $mylat + ($kmtomile / 69);
    return array("kmtomile" => $kmtomile, "lon1" => $lon1, "lon2" => $lon2, "lat1" => $lat1, "lat2" => $lat2);
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
    	return null;
        
//    	return BranchInventory::whereIn('stit_id', $productIds)
//		->where('item_count','>',0)
//		->groupBy('stit_id')
//		->select('branch_id', 'item_count','stit_id', 'mrp', DB::raw('MIN(fpod_customerRatePikup) as selling_price'))
//		->get();

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
	->where('item_count','>',0)->where('br_directDelivery','=',1)
	->groupBy('stit_id')
	->select('branch_id', 'item_count','stit_id', 'mrp', 'fpod_customerRatePikup as selling_price', DB::raw('MAX(distance) as mindistance'));
	//->get();

    $storegroupid = getHeaderStoreGroup();
    if($storegroupid > 0)
	    $branchInventory->whereRaw(' (br_storeGroup = ' . $storegroupid . ' OR issponsered = 1) ');

    return $branchInventory->get();
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


// GUEST TOKEN
function getGuestTokenFromHeader()
{
    try
    {
        $headerData = request()->header("R-Guest-Data");
        $token = "";
        if((!auth()->check()) && ($headerData))
        {
            $headerData = json_decode($headerData);
            $checkToken = preg_match('/^[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+$/', @$headerData->token);
            if($checkToken)
            {
                $token = @$headerData->FrontEndToken;
            }
        }
        return $token;
    }
    catch (Exception $e)
    {
        return "";
    }
}

// GET GUEST LOCATION
function getGuestLocationFromHeader()
{
    try
    {
        $headerData = request()->header("R-Guest-Data");
        $outs = [
            "lat"   => 0,
            "long"  => 0
        ];
        if((!auth()->check()) && ($headerData))
        {
            $headerData = json_decode($headerData);
            $outs = [
                "lat"   => @$headerData->GuestLatitude ?? 0,
                "long"  => @$headerData->GuestLongitude ?? 0
            ];
        }
        return $outs;

    }
    catch (Exception $e)
    {
        return [
            "lat"   => 0,
            "long"  => 0
        ];
    }
}

//get location details using google api
function getLocationDetails($lat, $long)
{
    $pincode = 0;
    $url = config("app.google_map_api");
    $vars = array(
        '{#lat}'    => $lat,
        '{#long}'   => $long,
        '{#key}'   => config('app.google_api_key'),
    );
    $url = strtr($url, $vars);
    $response = (new HttpCurlCalls)->curlCall($url, [], 'GET', ['Content-Type: application/json']);
    $outs = [
        "state"     => "",
        "country"   => "",
        "district"  => "",
        "pincode"   => ""
    ];
    if(!@empty($response->results))
    {
        $results = reset($response->results)->address_components;
        $pincode = array_filter($results, function($el) {
            if(in_array("postal_code", $el->types))
            {
                return $el; 
            }
        });
        $pincode = reset($pincode);
        $outs['pincode'] = @$pincode->long_name;
        $country = array_filter($results, function($el) {
            if(in_array("country", $el->types))
            {
                return $el; 
            }
        });
        $country = reset($country);
        $outs['country'] = @$country->long_name;
        $state = array_filter($results, function($el) {
            if(in_array("administrative_area_level_1", $el->types))
            {
                return $el; 
            }
        });
        $state = reset($state);
        $outs['state'] = @$state->long_name;
        
        $district = array_filter($results, function($el) {
            if(in_array("administrative_area_level_2", $el->types) || in_array("administrative_area_level_3", $el->types))
            {
                return $el; 
            }
        });
        $district = reset($district);
        $outs['district'] = @$district->long_name;
    }
    return $outs;
}