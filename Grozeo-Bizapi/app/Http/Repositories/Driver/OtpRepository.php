<?php

namespace App\Http\Repositories\Driver;

use App\Models\Drivers\QugeoDriver;
use App\Models\Drivers\TestMobile;
use App\Http\Responses\{
    SuccessResponse,
    SuccessWithData,
    ErrorResponse
};
use App\Sms\SmsSender;
use App\Traits\Driver\{
    LocationTrait,
    APISessionsTrait,
    LiveVehicleTrait
};
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;

class OtpRepository
{
    use LocationTrait, LiveVehicleTrait, APISessionsTrait;
    public function __construct()
    {
        $this->dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
    }
    /**
     * Get driver details by mobile number
     *
     * @param  string  $mobileNumber
     * @return array|ErrorResponse
     */
    public function sendOTP($mobileNumber)
    {
        try {
            $driver = QugeoDriver::select('d_ID','d_Name','l_Name','d_Add1','d_Add2','d_Add3','d_Ph1','d_Active')
                ->where('d_Ph1', $mobileNumber)
                ->where('d_Active', 1)
                ->first();

            if ($driver) {
                $otpData = $this->generateOtp($mobileNumber);


                $minutesToAdd = config('drivers.otp_timeout') ?? 60;

                // Update driver model with OTP and validity
                $driver->update([
                    'd_otp'             => $otpData['otp'],
                    'd_otpvalidtill'    => now()->addMinutes($minutesToAdd)
                ]);


                // Send SMS if required
                if ($otpData['sendsms'] == 1) {
                    app(SmsSender::class)->fetchContentSendSms($otpData, $mobileNumber, 10);
                }
                return $otpData;
            }
            
            return new ErrorResponse("Driver not found");
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage());
        }
    }
    public function verifyOTP($request)
    {
        try
        {
            $driverCheck = QugeoDriver::select('d_ID','d_Name','l_Name','d_Add1','d_Add2','d_Add3','d_Ph1','d_Active')
            ->where([
                ['d_Ph1', $request['mobile_number']],
                ['d_Active', 1]
            ])
            ->first();
    
            if (!$driverCheck)
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
                'd_otpvalidtill'    => NULL,
                'gcmregstid' => $request['fcm_token']
            ]);
           
            return new SuccessResponse( "Your OTP verification is successful");
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage());
        }

    }

    /**
     * Send OTP API Repo
     * recieves request (mobile_number)
     * returns status
    */
    public function otpSend($request)
    {
        try
        {
            $driver = QugeoDriver::where([
                ['d_Ph1', $request->mobile_number],
                ['d_Active', 1]
            ])->first();
            if (!$driver)
            {
                return new ErrorResponse('Driver not registered.'); 
            }
            $otpData = $this->generateOtp($request->mobile_number);
            $minutesToAdd = config('drivers.otp_timeout') ?? 60;

            // Update driver model with OTP and validity
            $driver->update([
                'd_otp'             => $otpData['otp'],
                'd_otpvalidtill'    => now()->addMinutes($minutesToAdd)
            ]);
            // Send SMS if required
            if ($otpData['sendsms'] == 1)
            {
                app(SmsSender::class)->fetchContentSendSms($otpData, $request->mobile_number, 10);
            }
            return new SuccessResponse("OTP send successfully");
        }
        catch (\Exception $e)
        {
            info("OtpRepository otpSend() Error");info($e);
            return new ErrorResponse("Operation failed");
        }
    }
    private function generateOtp($mobile)
    {
        $testMobile = TestMobile::where('mobile', $mobile)->first();
        if ($testMobile) {
            $data['otp'] = $testMobile->otp;
            $data['sendsms'] = 0;
        } else {
            $data['otp'] = mt_rand(1000, 9999);
            $data['sendsms'] = 1;
        }
        return $data;
    }
    /**
     * Verify OTP API Repo
     * recieves request (mobile_number, otp, fcm_token, lat and long)
     * returns token
    */
    public function otpVerify($request)
    {
        try
        {
            // Get driver by phone number
            $driverCheck = QugeoDriver::where([
                ['d_Ph1', $request->mobile_number],
                ['d_Active', 1]
            ])->with('primaryVehicle')->first();

            if (!$driverCheck)
            {
                return new ErrorResponse('Driver not found'); 
            }
            // check if otp is valid
            if (!$this->checkValidOTP($driverCheck, $request->otp))
            {
                return new ErrorResponse('Invalid OTP');
            }
            // generate jwt token
            $token = createJwtToken($driverCheck);
            // update driver columns
            $driverCheck->update([
                'last_access_at'    => now(),
                'd_otp'             => NULL,
                'd_otpvalidtill'    => NULL,
                'is_online'         => '1',
                'gcmregstid'        => $request->fcm_token,
                'd_apikey'          => $token
            ]);
            $this->addAPISessions($driverCheck, $request->geocoords);
            $this->updateLocation($request->geocoords, ["event" => "loggedin"], $driverCheck);
            $this->updateLiveVehicleData($driverCheck, $request->geocoords);
            return new SuccessWithData(["token" => $token]);
        }
        catch (\Exception $e)
        {
            info("OtpRepository otpVerify() Error");info($e);
            return new ErrorResponse("Operation failed");
        }
    }

    /**
     * Validate OTP
     * returns boolean
     * Checks if request otp and saved otp are the same and check if otp validity is less than 60 mins
    */
    private function checkValidOTP($user, $otp)
    {
        $checkOtp = ($user->d_otp == $otp) && (now()->diffInMinutes($user->d_otpvalidtill) < 60);
        return $checkOtp;
    }

    // Old function to be removed
    private function isValidOtp($user, $otp)
    {
        info($user->d_otp);info($user->d_otpvalidtill);info(now()->diffInMinutes($user->d_otpvalidtill));
        return $user->d_otp == $otp || now()->diffInMinutes($user->d_otpvalidtill) < 60;
    }
}
