<?php

namespace App\Http\Repositories\Customer;

use stdClass;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\VerifyLog;
use App\Models\DeliveryInfo;
use Illuminate\Support\Arr;
use App\Helpers\EmailHelper;
use BackOffice\Models\BranchGroup;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Domains\Customer\StoreDeliveryInfo;
use App\Domains\Customer\GenerateReferralCode;
use App\Http\Repositories\Customer\AddressRepository;
use Illuminate\Support\Str;
use Exception;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\Cart\CartRepository;

class RegistrationRepository
{
    protected $customer;
    protected $deliverinfo;
    public function __construct(Customer $customer,DeliveryInfo $deliverinfo,Branch $branch, CartRepository $cartRepo)
    {

        $this->customer = $customer;
        $this->deliverinfo=$deliverinfo;
        $this->branch=$branch;
        $this->cartRepo = $cartRepo;
    }

    /**
     * Register a new customer
     *
     * @param array $data
     * @return void
     */
    public function store($data)
    {
       //$branch_id= AddressRepository::checkPincode($data['pincode']);
       $branch_id=10; 
       if($branch_id > 0){
            $refCode = @$data['refCode'];
            if((Hash::check($data['mobile'], $refCode)) || (Hash::check($data['email'], $refCode)))
            {
                $verified = NULL;
                if(Hash::check($data['mobile'], $refCode))
                {
                    $verified = $this->fetchMobileVerifiedRecord($data['mobile']);
                }

                $data['phone_verified'] = (Hash::check($data['mobile'], $refCode)) ? 1 : 0;
                $data['email_verified'] = (Hash::check($data['email'], $refCode)) ? 1 : 0;

                $customer = $this->customer->create(
                    $this->prepareData($data, @$verified->veri_customer_id)
                );


                $deliveryData = Arr::except($data, ['email']);
                $deliveryData["branch_id"] = $branch_id;
                $deliveryData["storegroupId"] = getHeaderStoreGroup();
               
                
                /*StoreDeliveryInfo::store(
                    Arr::add($deliveryData, 'id', $customer->cust_id)
                );*/

                if ($customer)
                {
                    $customer->token = createJwtToken($customer);
                    $this->cartRepo->migrateGuestCart($customer->cust_id);
                }

                $data=$this->deliverinfo->where('deli_customer_id',$customer->cust_id)->first();

                $storename = ($customer->storegroup_id == 0) ? 'Grozeo' : BranchGroup::find($customer->storegroup_id)->store_group_name;
                $sendEmail = (new EmailHelper)->sendEmail('welcomeCustomer', [
                    'fullname'  => $customer->cust_customer_name,
                    'email'     => $customer->cust_email,
                    'storename' => @$storename
                ]);

                $std=new stdClass();

                $std->cust_branch_id=$customer->cust_branch_id;
                $std->cust_customer_id=$customer->cust_customer_id;
                $std->cust_mobile=$customer->cust_mobile;
                $std->cust_email=$customer->cust_email;
                $std->cust_customer_name=$customer->cust_customer_name;
                $std->cust_ref_code=$customer->cust_ref_code;
                $std->cust_status=$customer->cust_status;
                $std->cust_id=$customer->cust_id;
                $std->token=$customer->token;
                $std->phone_verified = $customer->phone_verified;
                $std->email_verified = $customer->email_verified;
                $std->delivery_info=$data;

                return new SuccessWithData($std);
            }
            return new ErrorResponse("Invalid reference code");
        }else{
            throw new Exception('Pincode is not Available');
        }
    }

    /**
     * Fetch record from the verification table
     *
     * @param string $mobile
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function fetchMobileVerifiedRecord($mobile)
    {
        $verified = VerifyLog::where('veri_mobile', $mobile)->orderBy('veri_id', 'desc')->first();
        if (!$verified || $verified->veri_status !== 'verified') {
            throw new \Exception("Mobile number not verified");
        }

        return $verified;
    }

    /**
     * Prepare the customer data to be stored
     *
     * @param array $data
     * @param int $customerId
     * @return array
     */
    public function prepareData($data, $customerId)
    {
        $lat=isset($data['latitude'])?$data['latitude']:0.00;
        $long=isset($data['longitude'])?$data['longitude']:0.00;

        // $datas = Branch::selectRaw("*,(6371.3929 * acos (cos ( radians($lat) ) * cos( radians( br_Lat ) )* cos( radians( br_Lng ) - radians($long) ) + sin ( radians($lat) )* sin( radians( br_Lat ) ))) AS distance")
        // ->having("distance", "<", 10)
        // ->where('br_PyramidLevel',2)
        // ->orderBy("distance","ASC")
        // ->get();


        // $branch_id='';

        // if(count($datas)==0)
        // {

            $item_branch=$this->branch->where('br_PyramidLevel',2)->where('br_status',"Active")->first();
            $branch_id=$item_branch['br_ID'];
            if(!isset($branch_id))
              $branch_id = 2;

            $retailer=0;
        // }
        // else{
        //     $branch_id=$datas[0]['br_ID'];
        // }

        $storegroupid = getHeaderStoreGroup();
        $password = (@$data['password'] != "") ? Hash::make($data['password']) : "";
        return [
            'cust_branch_id'        => $branch_id,
            'cust_customer_id'      => $customerId ?? 0,
            'cust_mobile'           => $data['mobile'],
            'cust_email'            => $data['email'],
            'cust_customer_name'    => $data['name'],
            'cust_ref_code'         => Str::uuid()->toString(),
            'cust_status'           => 'registered',
            'storegroup_id'         => $storegroupid,
            'cust_password'         => $password,
            'phone_verified'        => $data['phone_verified'],
            'email_verified'        => $data['email_verified'],
        ];
    
    }

    private function createCustomerByProcedure($customerDetails)
    {
        $query = 'CALL createNewCustomer('.$customerDetails['cust_branch_id'].', '.$customerDetails['cust_customer_id'].', "'.$customerDetails['cust_mobile'].'", "'.$customerDetails['cust_email'].'", "'.$customerDetails['cust_customer_name'].'", "'.$customerDetails['cust_status'].'", '.$customerDetails['storegroup_id'].', "'.now().'", "'.now().'")';

        return DB::select($query);
    }
}
