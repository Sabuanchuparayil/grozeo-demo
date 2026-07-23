<?php

namespace BackOffice\Http\Controllers\RelationOfficer;

use BackOffice\Models\RelationOfficer\ROUser;
use BackOffice\Models\RelationOfficer\AreaEntries;
use App\Events\OtpGenerated;
use App\Exceptions\MsgException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Responses\SuccessResponse;
use BackOffice\Http\Requests\OtpRequest;
use App\Http\Controllers\VersionController;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\ErrorResponse;
use BackOffice\Http\Requests\OtpVerificationRequest;
use Illuminate\Support\Facades\DB;
use App\Sms\SmsSender;

class ROLoginController
{

    protected $user;

    public function __construct(ROUser $user)
    {
        $this->user = $user;
    }

    /**
     * create otp
     *
     * @return string
     */
    public function createOtp(OtpRequest $request, SmsSender $smssender)
    {
        $user = $this->user->where('roMobile', $request->phone)->first();
        if (!$user)
        {
            return new ErrorResponse('User not found');
        }
        $otpData = $this->generateOtp($request->phone);
        $user->update([
            'otp' => $otpData['otp'],
            'otp_generated_at' => now(),
        ]);
        if ($otpData['sendsms'] == 1)
        {
            $smssender->fetchContentSendSms($otpData, $request->phone, 23);
        }
        return new SuccessResponse('Otp send successfully');
    }
    
    /**
     * otp verification
     *
     * @return string
     */
    public function otpVerification(OtpVerificationRequest $request)
    {
        $storegroupid = getHeaderStoreGroup();
        $user = ROUser::select('id', 'otp', 'otp_generated_at', 'roName as name', 'roMobile as phone', 'roArea')
            ->where('roMobile', $request->phone)
            ->first(); 
       
          
        if($user)
        {
           $token= createJwtToken($user);
        }
        else
        {
            return new ErrorResponse('User not found'); 
        }    
        if (!$this->isValidOtp($user, $request->otp))
        {
            return new ErrorResponse('Invalid OTP');
        }
        $user->token= $token;
        $user->areaEntries = AreaEntries::select('id', 'areaName', 'areaLocation')->where('id', $user->roArea)->first();

        ROUser::where('id', $user->id)
        ->update([
            'last_access_at'    => now(),
            'is_offline'        => 0,
            'logout_at'         => NULL,
            'otp'               => NULL,
            'otp_generated_at'  => NULL,
            'loggedout_by'      => NULL
        ]);
        return new SuccessWithData(
            $user
        );
    }
    
    /**
     * RO Log out
     *
     * @return string
     */
    public function logout()
    {
        DB::transaction(function() {
            $roUSer = auth_user();

            $roUSer->update([
                'is_offline' => 1,
                'logout_at' => now(),
                'loggedout_by' => 1,
            ]);

        });
        return new SuccessResponse('Successfully logged out');
    }


    /**
     * Generate random otp
     *
     * @return string
     */
    private function generateOtp($mobile)
    {
        $testMobiles = DB::select("SELECT COUNT(1) AS ismobile,mobile,otp FROM test_mobile  WHERE mobile = '" . $mobile . "' LIMIT 1");

        if ($testMobiles[0]->ismobile > 0)
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
        return $user->otp == $otp && now()->diffInMinutes($user->otp_generated_at) < 60;
    }
}
