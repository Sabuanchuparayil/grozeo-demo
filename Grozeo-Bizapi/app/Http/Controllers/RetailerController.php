<?php

namespace App\Http\Controllers;


use App\Models\Branch;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Responses\SuccessWithData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RetailerController extends Controller
{
    protected $branch;

    public function __construct(Branch $branch)
    {

        $this->branch = $branch;

    }

    public function get(Request $request)
    {
/*        $validatedData = $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $oriLat=$request['latitude'];
        $oriLng=$request['longitude'];

        $i = 1;
        $radius = 5;
        do {
            $retailer = app(Branch::class)->where('br_PyramidLevel', 4)->where('br_status',"Active")->selectRaw("*,(6371.3929 * acos (cos ( radians($oriLat) ) * cos( radians( br_Lat ) )* cos( radians( br_Lng ) - radians($oriLng) ) + sin ( radians($oriLat) )* sin( radians( br_Lat ) ))) AS distance")
                ->having("distance", "<", $radius)
                ->orderBy("distance")
                ->get();

            if (count($retailer)) {
                $i = 0;
            }
            $radius = $radius + 5;
        } while ($i == 1);
        $final_data = array();

        foreach ($retailer as $ret) {

            $destLat = $ret->br_Lat;
            $destLng = $ret->br_Lng;

            $result = app(Client::class)->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'query' => [
                    'units' => 'metric',
                    'origins' => "{$oriLat},{$oriLng}",
                    'destinations' => "{$destLat},{$destLng}",
                    'key' => config('app.google_api_key'),
                ],
            ]);
            $data = $result->getBody()->getContents();
            $data = json_decode($data, true);
            array_push($final_data,$ret->br_ID);
        }

        // usort($final_data, function ($a, $b) {
        //     return $a['distance'] <=> $b['distance'];
        // });

          $data = $this->branch->whereIn('br_id',$final_data)
        ->get();

    */
    
    $lat = $request['latitude']; $lng=$request['longitude'];

    if(!isset($request['longitude']) && !isset($request['latitude'])){
        $usr = auth()->user();//Auth::user();
        if(!$usr)
            return null;

        $latlang = DB::select('SELECT deli_latitude, deli_longitude FROM retaline_customer_delivery_info WHERE deli_is_primary=1 AND deli_customer_id='. $usr->cust_id . ' limit 1');

        if(!isset($latlang) || count($latlang) <1)
            return null;

        $lat= $latlang[0]->deli_latitude;
        $lng = $latlang[0]->deli_longitude;
    }

    if(!isset($lat) && !isset($lng))
        return null;


    //if(!isset($request['latitude']))

        $final_data = getNearestAerialBranches($lat, $lng,config('app.customer_location_to_branch_distance_circle_max'));

        if(isset($final_data) ){
            $branches = $final_data->toArray();
            $br_id  = array_column($branches, 'br_ID');    
           
        }else{
            $br_id='0';
        }    
        $data = $this->branch->wherein('br_id',$br_id)
        ->get();

      
        return new SuccessWithData($data);

    }
    public function getNearestStores(Request $request)
    {
        $lat = $request['latitude']; $lng=$request['longitude'];

        if(!isset($request['longitude']) && !isset($request['latitude'])){
            $usr = auth()->user();
            if(!$usr)
                return null;

            $latlang = DB::select('SELECT deli_latitude, deli_longitude FROM retaline_customer_delivery_info WHERE deli_is_primary=1 AND deli_customer_id='. $usr->cust_id . ' limit 1');

            if(!isset($latlang) || count($latlang) <1)
                return null;

            $lat= $latlang[0]->deli_latitude;
            $lng = $latlang[0]->deli_longitude;
        }

        if(!isset($lat) && !isset($lng))
            return null;
        $retailCategory = @$request['retail_category'] ?? 0;

	    $result = DB::table('finascop_branch_group')
			->selectRaw('finascop_branch_group.store_group_id, store_group_name, logoUrl, MIN(calcDistance('. $lat .', ' . $lng . ', br_Lat, br_Lng)) AS distance, bt.business_types')
            ->join('finascop_branch', 'finascop_branch_group.store_group_id', 'finascop_branch.br_storeGroup')
			->leftJoin(DB::raw('(SELECT bt.store_group_id, GROUP_CONCAT(business_type_name ORDER BY is_primary DESC) as business_types, GROUP_CONCAT(t.business_type_id ORDER BY is_primary DESC) as business_typeids FROM finascop_branch_group_business_type bt INNER JOIN finascop_business_type t ON t.business_type_id=bt.business_type_id
            GROUP BY bt.store_group_id)bt'), 'bt.store_group_id', 'finascop_branch_group.store_group_id')
			->where('finascop_branch_group.status', DB::raw(1))
            ->where('finascop_branch_group.isFeatured', DB::raw(1))
            ->groupBy('finascop_branch_group.store_group_id')->orderBy('distance');

        if($retailCategory > 0)
        {
            $result->whereRaw("FIND_IN_SET({$retailCategory}, bt.business_typeids) > 0");
        }
        return new SuccessWithData($result->paginate(10));

    }

    public function getNearestBranches(Request $request){
        $lat = $request['latitude']; $lng=$request['longitude'];
        $storegroupid = getHeaderStoreGroup();
        $defaultbranchID = getHeaderBranch();

        if(!isset($request['longitude']) && !isset($request['latitude'])){
            $usr = auth()->user();//Auth::user();
            if(!$usr)
                return null;

            $latlang = DB::select('SELECT deli_latitude, deli_longitude FROM retaline_customer_delivery_info WHERE deli_is_primary=1 AND deli_customer_id='. $usr->cust_id . ' limit 1');

            if(!isset($latlang) || count($latlang) <1)
                return null;

            $lat= $latlang[0]->deli_latitude;
            $lng = $latlang[0]->deli_longitude;
        }

        if(!isset($lat) && !isset($lng))
            return null;


        $columns = Schema::getColumnListing('finascop_branch'); // all columns in table
        $columns = array_diff($columns, ['location']); // remove column
        $result = Branch::from("finascop_branch as fb")
        ->join('finascop_branch_group as fbg', 'fbg.store_group_id', 'fb.br_storeGroup')
        ->where('fbg.status', DB::raw(1))->where('fbg.store_group_id', $storegroupid)
        ->select($columns)->addSelect(DB::raw("calcDistance({$lat}, {$lng}, br_Lat, br_Lng) AS distance"))
        ->orderBy('distance');
    
        if($defaultbranchID > 0)
            $result->where('fb.br_ID', $defaultbranchID);

        return new SuccessWithData($result->paginate(10));
    }

    public function methodfeild()
    {
        return [
            "br_ID",
            "br_Name",
            "br_City",
            "br_Address",
            "br_Email"

        ];
    }
}
