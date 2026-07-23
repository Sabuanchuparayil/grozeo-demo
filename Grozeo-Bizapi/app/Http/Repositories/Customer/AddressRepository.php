<?php

namespace App\Http\Repositories\Customer;

use App\Helpers\HttpCurlCalls;

use App\Models\{
    State,
    Country,
    District
};
use App\Models\Branch;
use App\Models\Customer;
use App\Models\BrmPincode;
use App\Models\DeliveryInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\MyphaCentralstoreConfig;
use App\Http\Repositories\Cart\CartRepository;

use Illuminate\Database\Eloquent\Collection;


class AddressRepository
{
    protected $deliveryInfo;

    public function __construct(DeliveryInfo $deliveryInfo)
    {
        $this->deliveryInfo = $deliveryInfo;
    }

    public function create($address)
    {
        $user = auth()->user();

        if(is_null(@$user->cust_id))
        {
            return [];
        }
        $addrToNearestBranch = false;
        if(config('app.address_to_nearestbranch') == true)
            $addrToNearestBranch = true;

        $nearestretailers = getNearestAerialBranches($address['deli_latitude'], $address['deli_longitude'],config('app.customer_location_to_branch_distance_circle_max'));
        $address['deli_customer_id'] = $user->cust_id;

        $branch_id= (isset($nearestretailers->first()->br_ID) ? $nearestretailers->first()->br_ID : 0);
        if($branch_id == null)
            $branch_id=0;

        $address["deli_branch_id"]=$branch_id;
        $address['deli_centralstore']=$branch_id;
        $address['deli_status'] = 'active';
        $address['deli_is_primary'] = ($addrToNearestBranch == true ? ($branch_id >0 ? '1' : '0') : '1');
        $addrExist = $this->deliveryInfo->where('deli_customer_id', $user->cust_id)->count();
        if($addrExist == 0)
        {
            $address['deli_is_primary'] = 1;
        }
        if(isset($nearestretailers->first()->br_ID) && $nearestretailers->first()->br_ID >0){
            $address['deli_retailer']=$nearestretailers->first()->br_ID;
        }else{
            $address['deli_retailer']='0';
        }
        if(!isset($address["deli_post"]))
        $address["deli_post"] = 0;
        $address["storegroupId"]=getHeaderStoreGroup();
        
        $this->checkLocationsAvailable($address);

        DB::transaction(function () use ($user) {
            $this->deliveryInfo->where('deli_customer_id', $user->cust_id)->update(['deli_is_primary' => 0]);
        });
        return $this->deliveryInfo->create($address)->refresh();
    }

    public function get()
    {
        $customer_id = auth_user()->cust_id ?? '';
        $delivery = DeliveryInfo::with(['delivery' => function ($q) {
                                $q->select('pincode', 'isActive');
                                }])
                            ->where('deli_customer_id', $customer_id)
                            ->where('deli_status', 'active')
                            ->get();
        return (count($delivery) > 0) ? $this->structDelivery($delivery) : $delivery;
    }

    public function delete($id)
    {
        $this->deliveryInfo
        ->where('deli_customer_id', auth()->user()->cust_id)
        ->where('deli_id',$id)
        ->delete();
           
        return $this->deliveryInfo
            ->where('deli_customer_id', auth()->user()->cust_id)->get();
    }

    public function setPrimary($id)
    {
        $deliveryInfo = $this->deliveryInfo->findOrFail($id);
        
        $nearestretailers = getNearestAerialBranches($deliveryInfo['deli_latitude'], $deliveryInfo['deli_longitude'],config('app.customer_location_to_branch_distance_circle_max'));
        $branch_id= (isset($nearestretailers->first()->br_ID) ? $nearestretailers->first()->br_ID : 0);
        if($branch_id == null || $branch_id <=0 )
            $branch_id= ( isset($deliveryInfo['deli_branch_id']) && $deliveryInfo['deli_branch_id'] > 0 ? $deliveryInfo['deli_branch_id'] : getBranchIdForll());

        DB::transaction(function () use ($deliveryInfo, $branch_id) {
            $this->deliveryInfo
                ->where('deli_customer_id', auth()->user()->cust_id)
                ->update(['deli_is_primary' => 0]);

            $deliveryInfo->update(['deli_is_primary' => 1, 'deli_branch_id' => $branch_id]);
        });

        $data=$this->deliveryInfo
                ->where('deli_customer_id', auth()->user()->cust_id)->get();

        return $data;
    }

    protected function getServicablePincode($pincode)
    {
        return BrmPincode::where('pincode', $pincode)
            ->where('isActive', 1)
            ->first();
    }

    private function getBranchPincode($pincode)
    {
        $pin = BrmPincode::where('pincode', $pincode)
                    ->where('isActive', 1)
                    ->first();
        return $pin->branch_id ?? 0;
    }
    public static function checkPincode($pincode){
        $isExitPincode=0;
         $brmPincode = BrmPincode::where('pincode', $pincode)
            ->where('isActive', 1)
            ->first();
        if($brmPincode){  
            $myphaCentralstoreConfig = MyphaCentralstoreConfig::where('mcsc_district', $brmPincode['dst_id'])
                    ->first();  
            if($myphaCentralstoreConfig){        
                $branch_id=$myphaCentralstoreConfig["mcsc_centralStore"];              
                $isExitPincode=$branch_id;
                
            }
        }
        return $isExitPincode;
    }

    private function structDelivery(Collection $delivery)
    {
        $delivery = $delivery->toArray();     
        foreach($delivery as $key => $delivr)
        {
            $deliveryBranch = $delivr['deli_branch_id'];
            
            $delivery[$key]['deli_branch_id'] = $deliveryBranch ? $deliveryBranch : 0;
            if( intval($deliveryBranch)>0){
               $br_name =  Branch::where('br_ID',  $deliveryBranch)            
                ->first(['br_name']);
            }
            $brname = '';
            try{
                if(isset($br_name))
                    $brname = $br_name->br_name;
            }
            catch (\Exception $e){
                $brname = '';
            }

            $delivery[$key]['deli_branch_name'] = $deliveryBranch ? $brname : '';
        }
        return $delivery;
    }
    private function checkLocationsAvailable(&$address)
    {
        $stateCheck = @$address["deli_state"];
        $distCheck = @$address["deli_district"];
        $pinCheck = @$address['deli_delivery_pin'];
        $locationData = [];
        if((config('app.is_international') == 1) || ($pinCheck == NULL))
        {
            $locationData = getLocationDetails($address['deli_latitude'], $address['deli_longitude']);
        }
        if(config('app.is_international') == 1)
        {
            $address["deli_state"] = $this->checkState($stateCheck, @$locationData['state'], @$locationData['country']);
            $address["deli_district"] = $this->checkDistrict($distCheck, @$locationData['district'], @$locationData['state'], @$locationData['country']);
        }
        $address['deli_delivery_pin'] = ($address['deli_delivery_pin'] == "") ? @$locationData['pincode'] : $address['deli_delivery_pin'];
    }
    private function checkState($stateCheck, $state = NULL, $country = NULL)
    {
        $stateData = State::where('st_name', $stateCheck)->first();
        if($stateData)
        {
            return $stateCheck;
        }
        else
        {
            $this->addState($state, $country);
            return $state;
        }
    }
    private function checkDistrict($checkDistrict, $district = NULL, $state = NULL, $country = NULL)
    {
        $distData = District::where('dst_Name', $checkDistrict)->first();
        if($distData)
        {
            return $checkDistrict;
        }
        else
        {
            return $this->addDistrict($district, $state, $country);
        }
    }
    private function addDistrict($district = NULL, $state = NULL, $country = NULL)
    {
        $distData = District::where('dst_Name', $district)->first();
        if(!$distData)
        {
            $distAdd = ['dst_Name'  => $district];
            $stateData = State::where('st_name', $state)->first();
            if($stateData)
            {
                $distAdd['st_Id'] = $stateData->st_ID;
                $distAdd['cnt_ID'] = $stateData->cnt_ID;
            }
            else
            {
                $addState = $this->addState($state, $country);
                $distAdd['st_Id'] = $addState->st_ID;
                $distAdd['cnt_ID'] = $addState->cnt_ID;
            }
            $districtAdd = District::create($distAdd);
        }
        return $district;
    }
    private function addState($state = NULL, $country = NULL)
    {
        $getState = State::where('st_name', $state)->first();
        if($getState)
        {
            return $getState;
        }
        else
        {
            $stateAdd = State::create([
                'st_name'   => $state,
                'cnt_ID'    => $this->addCountry($country)
            ]);
            return @$stateAdd;
        }
    }
    private function addCountry($country = NULL)
    {
        $getCountry = Country::where('country_name', $country)->first();
        if($getCountry)
        {
            return $getCountry->country_id;
        }
        else
        {
            $countryAdd = Country::create(['country_name'  => $country]);
            return @$countryAdd->country_id;
        }
    }

}
