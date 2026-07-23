<?php

namespace App\Location;

use App\Models\Branch;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class RetailerLocation
{
    protected $branch;

    public function __construct()
    {
        $this->branch = new Branch;
    }

    public static function fetchRetailer($customer)
    {
        // $lat = $customer->deli_latitude ?? 0;
        // $long = $customer->deli_longitude ?? 0;
        // $i = 1;
        // $radius = 5;
        // do {
        //     $retailer = app(Branch::class)->where('br_PyramidLevel', 4)->selectRaw("*,(6371.3929 * acos (cos ( radians($lat) ) * cos( radians( br_Lat ) )* cos( radians( br_Lng ) - radians($long) ) + sin ( radians($lat) )* sin( radians( br_Lat ) ))) AS distance")
        //         ->having("distance", "<", $radius)
        //         ->orderBy("distance")
        //         ->get();

        //     if (count($retailer)) {
        //         $i = 0;
        //     }
        //     $radius = $radius + 5;
        // } while ($i == 1);

        // return $retailer[0]['br_ID'] ?? 0;


        /*$oriLat = $customer->deli_latitude ?? 0;
        $oriLng = $customer->deli_longitude ?? 0;
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
            array_push($final_data, array('br_id' => $ret->br_ID, 'distance' => (int) (isset($data['rows'][0]['elements'][0]['distance']['value']))?($data['rows'][0]['elements'][0]['distance']['value'] / 1000):""));
        }

        usort($final_data, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

         return $final_data[0]['br_id'] ?? 0;

        */
        $final_data = getBranchIdForll();

        return $final_data;
       
    }
}
