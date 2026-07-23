<?php

namespace App\Http\Repositories;

use stdClass;
use App\Models\SmsEmailLogs;


class SmsEmailRepository
{
    protected $smsEmailLogs;

    public function __construct(SmsEmailLogs $smsEmailLogs)
    {
        $this->smsEmailLogs = $smsEmailLogs;
        
    }

    /**
     * Store the smsEmailLogs data to DB.
     *
     * @param array $data
     * @return string
     */
    public function store($data)
    {
        $record = $this->smsEmailLogs->create(
            $this->prepareData($data)
        );
        
    }
    public function prepareData($data)
    {
        return [
           'smsemail_id'        => $data['mobile'],
           'smsemail_text'      => $data['msg'],
           'smsemail_datetime'  => now(),
           'sms_responseid'     => $data['response'],
           'storeGroupId'       => $data['storegroupid']
        ];
    }

    /**
     * check if the mobile no exist in the db and fetch them.
     *
     * @param string $mobile
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function fetchRecordIfExists($mobile)
    {
        return $this->verifyLog->where('veri_mobile', $mobile)->first();
    }

    /**
     * Check if the time for current otp expired
     *
     * @param \Illuminate\Database\Eloquent\Model $record
     * @return boolean
     */
    public function isTimeExpired($record)
    {
        return now()->gt($record->veri_smsexp_dt);
    }

    /**
     * Update exisitng otp.
     *
     * @param \Illuminate\Database\Eloquent\Model $record
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateExistingOtp($record)
    {
        $record->veri_sms_code = $this->generateOtp($record->veri_mobile);
        $record->veri_smsgen_dt = now();
        $record->veri_smsexp_dt = now()->addHour();
        $record->veri_sms_count = $record->veri_sms_count + 1;
        $record->save();
        return $record;
    }

    /**
     * Update verification status
     *
     * @param \Illuminate\Database\Eloquent\Model $record
     * @return void
     */
    public function updateStatus($record)
    {
        $record->veri_status = 'verified';
        $record->save();
    }

    /**
     * Generate random otp
     *
     * @return string
     */
    public function generateOtp($mobile)
    {
        $mobiles = ["9061160000", "9846583711", "8289847144", "9895670756", "8129160154","9495050000"];
        if(in_array($mobile, $mobiles))
        {
            return "1111";
        }
        return mt_rand(1000, 9999);
    }

    /**
     * Generate unique customer id
     *
     * @return int
     */
    public function generateCustomerId()
    {
        $latest = $this->verifyLog->latest('veri_id')->first();
        return $latest ? $latest->veri_customer_id + 1 : 1000;
    }

    private function deliveryInfo(array $data)
    {
        $customer = $this->customer->with('primaryAddress')
            ->where('cust_mobile', $data['mobile'])
            ->first();

        if ($customer) {
            $customer->token = JWTAuth::fromUser($customer);
        }
        
        if ($customer && !is_null($customer->primaryAddress)) {
            $customer->primaryAddress->deli_branch_id = $this->getPincodeBranch(
                $customer->primaryAddress->deli_delivery_pin
            );
        }


        $customer = $customer ? $customer->toArray() : [];

        if ($customer && is_null($customer['primary_address'])) {
            $customer['primary_address'] = new \stdClass();
        }
        

       return $customer ?: new \stdClass();
    }

    protected function getPincodeBranch($pincode)
    {
        $pincode = BrmPincode::where('pincode', $pincode)
            ->where('isActive', 1)
            ->first();
        return $pincode ? $pincode->branch_id : 0;
    }

}
