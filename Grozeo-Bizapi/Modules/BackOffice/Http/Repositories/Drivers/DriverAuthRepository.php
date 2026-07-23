<?php

namespace BackOffice\Http\Repositories\Drivers;

use App\Http\Responses\{
    SuccessResponse,
    SuccessWithData,
    ErrorResponse
};
use BackOffice\Models\Drivers\QugeoDriver;
use App\Sms\SmsSender;
use Illuminate\Support\Facades\DB;
use Aws\DynamoDb\DynamoDbClient;

class DriverAuthRepository
{

    public function __construct(QugeoDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
    * Driver Authentication check
    *
    * @return string
    */
    public function driverAuthentication($request)
    {
        try
        {
            $driver = $this->driver->where([
                ['d_Ph1', $request['userid']],
                ['imeinumber', $request['password']],
                ['d_Active', 1]
            ])->first();
            if($driver)
            {
                $otpData = $this->generateOtp($request['userid']);
                $minutesToAdd = config('drivers.otp_timeout') ?? 60;
                $driver->update([
                    'd_otp'             => $otpData['otp'],
                    'd_otpvalidtill'    => now()
                ]);
                if ($otpData['sendsms'] == 1)
                {
                    app(SmsSender::class)->fetchContentSendSms($otpData, $request['userid'], 10);
                }
                return new SuccessResponse('Otp send successfully');
            }
            return new ErrorResponse("Driver not found");
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage());
        }
    }
    /**
     * otp verification
     *
     * @return string
     */
    public function driverOtpVerification($request)
    {
        try
        {
            $driverCheck = $this->driver->where([
                ['d_Ph1', $request['userid']],
                ['d_Active', 1]
            ])->first();
            if($driverCheck)
            {
               $token = createJwtToken($driverCheck);
            }
            else
            {
                return new ErrorResponse('Driver not found'); 
            }
            if (!$this->isValidOtp($driverCheck, $request['otp']))
            {
                return new ErrorResponse('Invalid OTP');
            }
            $driverCheck->update([
                'last_access_at'    => now(),
                'd_otp'             => NULL,
                'd_otpvalidtill'    => NULL
            ]);
            $driverCheck->token = $token;
            return new SuccessWithData($driverCheck);
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage());
        }
    }


    /**
    * Generate random otp
    *
    * @return array
    */
    private function generateOtp($mobile)
    {
        $testMobiles = DB::select("SELECT COUNT(1) AS ismobile, mobile, otp FROM test_mobile WHERE mobile = '{$mobile}' LIMIT 1");
        if (@$testMobiles[0]->ismobile > 0)
        {
            $data['otp'] = $testMobiles[0]->otp;
            $data['sendsms'] = 0;
        }
        else
        {
            $data['otp'] = mt_rand(1000, 9999);
            $data['sendsms'] = 1;
        }
        return $data;
    }
    
    /**
     * check if otp is valid
     *
     * @return string
     */
    private function isValidOtp($user, $otp)
    {
        return $user->d_otp == $otp && now()->diffInMinutes($user->d_otpvalidtill) < 60;
    }
}