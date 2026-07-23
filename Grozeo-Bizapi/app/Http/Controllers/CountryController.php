<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Responses\SuccessWithData;
use Illuminate\Support\Facades\DB;

class CountryController extends Controller {

    protected $country;

    public function __construct(Country $country) {

        $this->country = $country;
    }

    public function get(Request $request) {
	    $code = "IN";
        $type = "1"; // 1 - Get Country details, 2- Get all active countries.
        if(isset($request['reqtype']))
            $type = $request['reqtype'];

        if(isset($request['reqip'])){
            try {
                // $url = "https://ipgeolocation.abstractapi.com/v1/?api_key=759749683b9e4b9792dbb068e90c8a72&ip_address=". $request['reqip'];
		        $url = "https://ipinfo.io/" . $request['reqip'] . "?token=5cc9fff8aa3047";

                    $json = json_decode(file_get_contents($url), true);
                    //if(isset($json["country_code"]))
                    //    $code = $json["country_code"];
                    if(isset($json["country"]))
                        $code = $json["country"];

                } 
                catch (\Exception $e) {
                    $code = "IN";
                    // info("ipgeolocation api error:");
                    // info($e->getMessage());
                }
        }
        else if(isset($request['code'])){
	        $code = $request['code'];
        }

        if($type == "2")
            $version = Country::where('status', '1')->where('partner_site_url', '<>', DB::raw("''"))->select([DB::raw("'" . $code . "' as source"), 'country_code', 'status', 'is_default', 'partner_site_url', 'public_site_url','country_name', 'domain', 'phone_code', 'currency'])->get();
        else
            $version = Country::where('country_code', $code)->first(['country_code', 'status', 'partner_site_url', 'public_site_url','country_name', 'domain', 'phone_code', 'currency']);

        return new SuccessWithData(
                $version
        );

    }


}
