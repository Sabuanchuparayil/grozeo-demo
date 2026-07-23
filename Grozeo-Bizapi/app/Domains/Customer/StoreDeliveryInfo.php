<?php

namespace App\Domains\Customer;

use App\Models\Branch;
use App\Models\DeliveryInfo;
use App\Helpers\HttpCurlCalls;
use Illuminate\Support\Facades\Log;

class StoreDeliveryInfo
{
    protected $branch;


    public static function store($data)
    {
        return (new static)->storeData($data);
    }

    protected function storeData($data)
    {
        return DeliveryInfo::create(
            $this->prepareData($data)
        );

    }

    /**
     * Prepare data to be stored.
     *
     * @param array $data
     * @return array
     */
    protected function prepareData($data)
    {
        $lat = isset($data['latitude']) ? $data['latitude'] : 0.00;
        $long = isset($data['longitude']) ? $data['longitude'] : 0.00;

        // $datas = Branch::selectRaw("*,(6371.3929 * acos (cos ( radians($lat) ) * cos( radians( br_Lat ) )* cos( radians( br_Lng ) - radians($long) ) + sin ( radians($lat) )* sin( radians( br_Lat ) ))) AS distance")
        // ->having("distance", "<", 10)
        // ->where('br_PyramidLevel',2)
        // ->orderBy("distance","ASC")
        // ->get();
        // $branch_id='';

        // if(count($datas)==0)
        // {

            /*$item_branch=app(Branch::class)->where('br_PyramidLevel',2)->where('br_status',"Active")->first();
            $branch_id=$item_branch['br_ID'];*/
            
             $retailer=0;
        // }
        // else{
        //     $branch_id=$datas[0]['br_ID'];
        // }
        if(isset($data['pincode']))
            $pincode = $data['pincode'];
        else
            $pincode = $this->generatePincode($lat, $long);
        return [
            'deli_name'         => $data['name'],
            'deli_contact_no'   => $data['mobile'],
            'deli_customer_id'  => $data['id'],
            'deli_retailer'     =>$retailer,
            'deli_delivery_pin' => $pincode,
            'deli_branch_id'    => $data['branch_id'],
            'deli_house_no'     => isset($data['house_no']) ? $data['house_no'] : "",
            'deli_house_name'   => $data['house_name'],
            "deli_address"      => isset($data['deli_address1']) ? $data['deli_address1'] : "",
            "deli_address2"     => isset($data['deli_address2']) ? $data['deli_address2'] : "",
            'deli_land_mark'    => (isset($data['land_mark']) ? $data['land_mark'] : ''),
            'deli_post'         => isset($data['post']) ? $data['post'] : "" , 
            'deli_city'         => isset($data['city']) ? $data['city'] : "" , 
            'deli_state'        => isset($data['state']) ? $data['state'] : "" , 
            'deli_status'       => 'active',
            'deli_is_primary'   => 1,
            'deli_latitude'     => isset($data['latitude']) ? $data['latitude'] : NULL,
            'deli_longitude'    => isset($data['longitude']) ? $data['longitude'] : NULL,
            'deli_type'         => isset($data['deli_type']) ? $data['deli_type']: 'home',
        ];
    }
    
    private function generatePincode($lat, $long)
    {
        $pincode = 0;
        $default = config('pincodeapi.default');
        $url = config("pincodeapi.{$default}.url");
        $vars = array(
            '{#lat}'    => $lat,
            '{#long}'   => $long,
            '{#key}'   => config('app.google_api_key'),
        );
        $url = strtr($url, $vars);
        info($url);
        $response = (new HttpCurlCalls)->curlCall($url, [], 'GET', ['Content-Type: application/json']);

        $outs = NULL;
        if(!@empty($response->results))
        {
            $results = reset($response->results)->address_components;

            $outs = array_filter($results, function($el)
                {
                    if(in_array("postal_code", $el->types))
                    {
                        return $el; 
                    }
                }
            );
        }
        if($outs)
        {
            $outs = reset($outs);
            return @$outs->long_name;
        }
        return 0;
    }
}



