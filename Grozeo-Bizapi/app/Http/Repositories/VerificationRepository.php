<?php

namespace App\Http\Repositories;

use stdClass;
use App\Models\Customer;
use App\Models\VerifyLog;
use App\Models\EmailVerifyLog;
use App\Models\SmsEmailLogs;
//use App\Models\KalyeraIncomingLog;
use App\Models\BrmPincode;
use App\Events\OtpGenerated;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessWithData;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Sms\SmsSender;
use Aws\DynamoDb\DynamoDbClient;
use BackOffice\Models\BranchGroup;
use App\Helpers\HttpCurlCalls;
use App\Helpers\EmailHelper;
use App\Http\Repositories\Cart\CartRepository;

class VerificationRepository {

    protected $verifyLog;
    protected $EmailVerifyLog;
    protected $customer;

//    protected $kaleraIncLog;

    public function __construct(VerifyLog $verifyLog, EmailVerifyLog $emailVerifyLog, Customer $customer, SmsSender $smssender, CartRepository $cartRepo) {
        $this->verifyLog = $verifyLog;
        $this->emailVerifyLog = $emailVerifyLog;
        $this->customer = $customer;
        $this->smssender = $smssender;
        $this->cartRepo = $cartRepo;
//        $this->kaleraIncLog = $kaleraIncLog;
    }

    public function impuser($request) {
        try
        {
            if (auth()->user()->defaultRole != 'impersonate') {
                return null;
            }
            $type = (@$request['userID'] > 0) ? 3 : 0;
            $user = $this->deliveryInfo($request, $type);
            if (!($user instanceof Customer)) {
                return null;
            }
            $user->token = createJwtToken($user);
            return new SuccessWithData([
                'is_verified'   => true,
                'is_registered' => $user ? true : false,
                'user'          => $user ?? new \stdClass()
            ]);
        }
        catch (\Exception $e)
        {
            return new ErrorResponse("Operation failed."); 
        }
    }

    /**
     * Verify otp.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function verify($data) {
        $verifIdentifier = isset($data['identifier']) ? strtolower($data['identifier']) : "customer";
        if ($exisitingRecord = $this->fetchRecordIfExists($data['mobile'], $verifIdentifier)) {

            if (!$this->isTimeExpired($exisitingRecord)) {
                if ($exisitingRecord->veri_sms_code === $data['otp']) {

                    $this->updateStatus($exisitingRecord);

                    $user = ($this->checkPincode($data) || config('app.address_to_nearestbranch') == true) ? $this->deliveryInfo($data) :
                            $this->getCustomer($data);

                    if ($user) {
                        //      $user->token = JWTAuth::fromUser($user);
                        if($verifIdentifier == 'customer')
                        {
                            Customer::where('cust_id', $user->cust_id)->update(['phone_verified' => 1]);
                        }
                        $user->token = createJwtToken($user);
                        if (config('app.address_to_nearestbranch') != true)
                        {
                            $user->cust_branch_id = getBranchIdForll();
                        }
                        $this->cartRepo->migrateGuestCart($user->cust_id);
                    }


                    return new SuccessWithData([
                    'is_verified'   => true,
                    'is_registered' => $user ? true : false,
                    'refCode'       => $user ? '' : Hash::make($data['mobile']),
                    'user'          => $user ?? new \stdClass()
                    ]);
                } else {
                    return new ErrorResponse('invalid otp');
                }
            } else {
                return new ErrorResponse('otp timed out');
            }
        }
        return new ErrorResponse('number not found');
    }

    /**
     * Verify email otp.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function verifyEmail($data)
    {
        $verifIdentifier = isset($data['identifier']) ? strtolower($data['identifier']) : "customer";
        if ($exisitingRecord = $this->fetchEmailRecordIfExists($data['email'], $verifIdentifier))
        {
            if (!$this->isEmailTimeExpired($exisitingRecord))
            {
                if ($exisitingRecord->otp === $data['otp'])
                {
                    $exisitingRecord->verify_status = 1;
                    $exisitingRecord->save();
                    $dat['userid'] = $data['email'];
                    $user = ($this->checkPincode($dat, 2) || config('app.address_to_nearestbranch') == true) ? $this->deliveryInfo($dat, 2) :
                            $this->getCustomer($dat, 2);

                    if ($user) 
                    {
                        if($verifIdentifier == 'customer')
                        {
                            Customer::where('cust_id', $user->cust_id)->update(['email_verified' => 1]);
                        }
                        $user->token = createJwtToken($user);
                        if (config('app.address_to_nearestbranch') != true)
                        {
                            $user->cust_branch_id = getBranchIdForll();
                        }
                        $this->cartRepo->migrateGuestCart($user->cust_id);
                    }


                    return new SuccessWithData([
                    'is_verified'   => true,
                    'is_registered' => $user ? true : false,
                    'refCode'       => $user ? '' : Hash::make($data['email']),
                    'user'          => $user ?? new \stdClass()
                    ]);
                } else {
                    return new ErrorResponse('invalid otp');
                }
            } else {
                return new ErrorResponse('otp timed out');
            }
        }
        return new ErrorResponse('number not found');
    }
    
    /**
     * User verification with password.
     *
     * @param array $request
     * @return string
     */
    public function verifyPassword($request)
    {
        try
        {
            $where = [];
            if(@$request['type'] == 1)
            {
                $where['cust_mobile'] = $request['userid'];
            }
            if(@$request['type'] == 2)
            {
                $where['cust_email'] = $request['userid'];
            }
            if(!empty($where))
            {
                $checkCustomer = Customer::where($where)->first();
                if($checkCustomer)
                {
                    if(Hash::check($request['password'], $checkCustomer->cust_password))
                    {
                        
                        $user = ($this->checkPincode($request, $request['type']) || config('app.address_to_nearestbranch') == true) ? $this->deliveryInfo($request, $request['type']) : $this->getCustomer($request, $request['type']);

                        if ($user)
                        {
                            $user->token = createJwtToken($user);
                            if (config('app.address_to_nearestbranch') != true)
                            {
                                $user->cust_branch_id = getBranchIdForll();
                            }
                            $this->cartRepo->migrateGuestCart($user->cust_id);
                        }
                        return new SuccessWithData([
                            'is_verified'   => true,
                            'is_registered' => $user ? true : false,
                            'user'          => $user ?? new \stdClass()
                        ]);
                    }
                    return new ErrorResponse("Invalid password");
                }
            }
            return new ErrorResponse("Customer not found");
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }

    /**
     * Store the verification data to DB.
     *
     * @param array $data
     * @return string
     */
    public function store($data)
    { 
        $templateTypeId = (isset($data['template_type'])) ? $data['template_type'] : 15;
        $verifIdentifier = isset($data['identifier']) ? strtolower($data['identifier']) : "customer";
        $usePassword = (@$data['use_password'] == 1) ? 1 : 0;
        
        //check if customer exists for the provided email/mobile
        if($usePassword == 1)
        {
            $checkCustomer = Customer::where('cust_mobile', $data['mobile'])->whereNotNull('cust_password')->first();
            if($checkCustomer)
            {
                return 'verified successfully for password';
            }
        }
        if ($exisitingRecord = $this->fetchRecordIfExists($data['mobile'], $verifIdentifier)){

            if ($this->isTimeExpired($exisitingRecord)) {

                $record = $this->updateExistingOtp($exisitingRecord);
                //$this->sendOtp($record->veri_sms_code, $record->veri_mobile);
                if ($record->veri_issend_sms == 1) {
                    $templatedata = $data;
                    $templatedata['otp'] = $record->veri_sms_code;
                    $this->smssender->fetchContentSendSms($templatedata, $record->veri_mobile, $templateTypeId);
                }
                return 'otp sent successfully';
            } else {

                //$this->sendOtp($exisitingRecord->veri_sms_code, $exisitingRecord->veri_mobile);
                if ($exisitingRecord->veri_issend_sms == 1) {
                    $templatedata = $data;
                    $templatedata['otp'] = $exisitingRecord->veri_sms_code;
                    $this->smssender->fetchContentSendSms($templatedata, $exisitingRecord->veri_mobile, $templateTypeId);
                }
                return 'otp sent successfully';
            }
        } else {
            $record = $this->verifyLog->create(
                    $this->prepareData($data)
            );
            if ($record->veri_issend_sms == 1) {
                $templatedata = $data;
                $templatedata['otp'] = $record->veri_sms_code;
                $this->smssender->fetchContentSendSms($templatedata, $record->veri_mobile, $templateTypeId);
            }
            return 'otp sent successfully';
        }
    }
    

    /**
     * Store the email verification data to DB.
     *
     * @param array $data
     * @return string
     */
    public function storeEmail($data)
    {
        $templateTypeId = (isset($data['template_type'])) ? $data['template_type'] : 15;
        $usePassword = (@$data['use_password'] == 1) ? 1 : 0;
        $verifIdentifier = isset($data['identifier']) ? strtolower($data['identifier']) : "customer";
        //check if customer exists for the provided email
        if($usePassword == 1)
        {
            $checkCustomer = Customer::where('cust_email', $data['email'])->whereNotNull('cust_password')->first();
            if($checkCustomer)
            {
                return 'verified successfully for password';
            }
        }

        if ($exisitingRecord = $this->fetchEmailRecordIfExists($data['email'], $verifIdentifier))
        {

            if ($this->isEmailTimeExpired($exisitingRecord))
            {
                $record = $this->updateExistingEmailOtp($exisitingRecord);
                $this->sendEmailOtp($record->otp, $record->email_address, $templateTypeId);
                return 'otp sent successfully';
            }
            else
            {
                $this->sendEmailOtp($exisitingRecord->otp, $exisitingRecord->email_address, $templateTypeId);
                return 'otp sent successfully';
            }
        }
        else
        {
            $generateOTP = $this->generateOtp($data['email']);
            $record = $this->emailVerifyLog->create([
                'email_address' => $data['email'],
                'otp'           => $generateOTP['otp'],
                'identifier'    => $verifIdentifier,
                'email_count'   => 1,
                'verify_status' => 0,
                'valid_till'    => now()->addHour(),
            ]);
            $this->sendEmailOtp($record->otp, $record->email_address, $templateTypeId);
            return 'otp sent successfully';
        }
    }

    /**
     * Send email using SES
     *
     * @param int $otp
     * @return void
     */
    public function sendEmailOtp($otp, $email, $templateID)
    {
        SmsEmailLogs::create([
            'smsemail_id'       => $email,
            'smsemail_datetime' => now(),
            'smsemail_text'     => $otp,
            'issms'             => 0
        ]);
        $sendEmail = (new EmailHelper)->sendEmail('EmailOTP', [
            'email' => @$email,
            'Otp'   => @$otp
        ]);
    }



    /**
     * Generate an event to send otp to user's mobile
     *
     * @param int $otp
     * @return void
     */
    public function sendOtp($otp, $mobile) {
        $msg = "Welcome! Please use {$otp} as OTP (One Time Password) to sign in to " . config('siteinfo.app_client_project_name') . ". " . config('siteinfo.app_client_project_name') . " is your fastest delivering super market with hyper local delivery.";
        //$msg = "Welcome! Please use {$otp} as OTP (One Time Password) to sign in to ".config('siteinfo.app_client_project_name').". ".config('siteinfo.app_client_project_name')." is your fastest delivering super market with hyper local delivery.";
        //$msg = "Welcome to GoGoMeds! Please use {$otp} as OTP (One Time Password) to sign in to ".config('siteinfo.app_client_project_name').". ".config('siteinfo.app_client_project_name')." is your fastest delivering health store with hyperlocal delivery - VirtualShowKaze";
        return event(new OtpGenerated($mobile, $msg));
    }

    /**
     * Prepare data to be stored.
     *
     * @param array $data
     * @return array
     */
    public function prepareData($data) {
        $generatedOtpDet = $this->generateOtp($data['mobile']);
        return [
            'veri_customer_id' => $this->generateCustomerId(),
            'veri_company_id' => 1,
            'veri_mobile' => $data['mobile'],
            'veri_identifier' => isset($data['identifier']) ? strtolower($data['identifier']) : "customer",
            'veri_sms_code' => $generatedOtpDet['otp'],
            'veri_smsgen_dt' => now(),
            'veri_smsexp_dt' => now()->addHour(),
            'veri_sms_status' => 'sent',
            'veri_sms_count' => 1,
            'veri_status' => 'sms sent',
            'veri_issend_sms' => $generatedOtpDet['sendsms']
        ];
    }

    /**
     * check if the mobile no exist in the db and fetch them.
     *
     * @param string $mobile
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function fetchRecordIfExists($mobile, $identifier) {
        return $this->verifyLog->where([
            ['veri_mobile', $mobile],
            ['veri_identifier', $identifier],
        ])->first();
    }

    /**
     * check if the email exists in email verification table
     *
     * @param string $mobile
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private function fetchEmailRecordIfExists($email, $identifier)
    {
        return $this->emailVerifyLog->where([
            ['email_address', $email],
            ['identifier', $identifier],
        ])->first();
    }

    /**
     * Check if the time for current otp expired
     *
     * @param \Illuminate\Database\Eloquent\Model $record
     * @return boolean
     */
    public function isTimeExpired($record) {
        return now()->gt($record->veri_smsexp_dt);
    }

    /**
     * Check if the time for current email otp expired
     *
     * @param \Illuminate\Database\Eloquent\Model $record
     * @return boolean
     */
    public function isEmailTimeExpired($record) {
        return now()->gt($record->valid_till);
    }

    /**
     * Update exisitng otp.
     *
     * @param \Illuminate\Database\Eloquent\Model $record
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateExistingOtp($record) {
        $generatedOtpDet = $this->generateOtp($record->veri_mobile);
        $record->veri_sms_code = $generatedOtpDet['otp'];
        $record->veri_smsgen_dt = now();
        $record->veri_smsexp_dt = now()->addHour();
        $record->veri_sms_count = $record->veri_sms_count + 1;
        $record->veri_issend_sms = $generatedOtpDet['sendsms'];
        $record->save();
        return $record;
    }

    /**
     * Update exisitng email otp.
     *
     * @param \Illuminate\Database\Eloquent\Model $record
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateExistingEmailOtp($record)
    {
        $generatedOtpDet = $this->generateOtp($record->email_address);
        $record->otp = $generatedOtpDet['otp'];
        $record->valid_till = now()->addHour();
        $record->email_count = $record->email_count + 1;
        $record->save();
        return $record;
    }

    /**
     * Update verification status
     *
     * @param \Illuminate\Database\Eloquent\Model $record
     * @return void
     */
    public function updateStatus($record) {
        $record->veri_status = 'verified';
        $record->save();
    }

    /**
     * Generate random otp
     *
     * @return string
     */
    public function generateOtp($mobile = '0') {
        $testMobiles = DB::select("SELECT COUNT(1) AS ismobile,mobile,otp FROM test_mobile  WHERE mobile = '" . $mobile . "' LIMIT 1");

        if ($testMobiles[0]->ismobile > 0) {
            $data['otp'] = $testMobiles[0]->otp;
            $data['sendsms'] = 0;
        } else {
            $data['otp'] = mt_rand(1000, 9999);
            $data['sendsms'] = 1;
        }
        return $data;
    }

    /**
     * Generate unique customer id
     *
     * @return int
     */
    public function generateCustomerId() {
        $latest = $this->verifyLog->latest('veri_id')->first();
        return $latest ? $latest->veri_customer_id + 1 : 1000;
    }

    private function deliveryInfo(array $data, $type = 0) {
        $where = [];
        if($type == 0)
        {
            $where['cust_mobile'] = $data['mobile'];
        }
        if($type == 1)
        {
            $where['cust_mobile'] = $data['userid'];
        }
        if($type == 2)
        {
            $where['cust_email'] = $data['userid'];
        }
        if($type == 3)
        {
            $where['cust_id'] = $data['userID'];
        }
        $customer = $this->customer->with(['deliveryInfo' => function($q) {
                        $q->where('deli_is_primary', 1);
                    }])
                ->where($where)
                ->first();

        if (config('app.address_to_nearestbranch') == true) {
            if ($customer && !is_null($customer->deliveryInfo)) {
                $customer->cust_branch_id = $customer->deliveryInfo->deli_branch_id;
            }
        } else {
            if (!isset($customer["delivery_info"]) || $customer["delivery_info"] == null) {
                $customer["delivery_info"] = (object) array();
            } else {
                $customer->delivery_info["deli_branch_id"] = getBranchIdForll();
            }
        }

        return $customer;
    }

    private function checkPincode(array $data, $type = 0) {
        $where = [];
        if($type == 0)
        {
            $where['cust_mobile'] = $data['mobile'];
        }
        if($type == 1)
        {
            $where['cust_mobile'] = $data['userid'];
        }
        if($type == 2)
        {
            $where['cust_email'] = $data['userid'];
        }
        $customer = $this->deliveryInfo($data, $type);

        return $this->customer->with('deliveryInfo')
                        ->where($where)
                        ->exists();

        $pincode = $customer->deliveryInfo['deli_delivery_pin'] ?? 0;
        // return BrmPincode::where('pincode', $pincode)
        //     ->where('isActive', 1)
        //     ->exists();
    }

    private function getCustomer(array $data, $type = 0)
    {
        $where = [];
        if($type == 0)
        {
            $where['cust_mobile'] = $data['mobile'];
        }
        if($type == 1)
        {
            $where['cust_mobile'] = $data['userid'];
        }
        if($type == 2)
        {
            $where['cust_email'] = $data['userid'];
        }
        $customer = $this->customer->where($where)->first();
        if ($customer) {
            $customer->delivery_info = new \stdClass();
        }
        return $customer;
    }

    public function kalyeraIncomingCalls(array $data) {

        $record = $this->kaleraIncLog->create(
                $this->prepareDataKaleyra($data)
        );
        return true;
    }

    public function prepareDataKaleyra($data) {
        return [
            'rkil_mobile' => $data['mobile'],
            'rkil_callTime' => $data['callTime'],
            'rkil_callType' => 'Incoming',
            'rkil_createdOn' => now(),
            'rkil_callStartTime' => $data['callStartTime'],
            'rkil_callEndTime' => $data['callEndTime'],
            'rkil_callDuration' => $data['callDuration'],
            'rkil_callStatus' => $data['callStatus'],
        ];
    }

}
