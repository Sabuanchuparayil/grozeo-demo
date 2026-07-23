<?php

namespace BackOffice\Http\Controllers;

use BackOffice\Models\User;
use App\Events\OtpGenerated;
use App\Exceptions\MsgException;
use App\Http\Responses\SuccessResponse;
use BackOffice\Http\Requests\OtpRequest;
use Illuminate\Support\Facades\DB;
use App\Sms\SmsSender;

class OtpController {

    protected $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function __invoke(OtpRequest $request, SmsSender $smssender) {
        $user = $this->user->where('phone', $request->phone)->first();
        if (!$user) {
            throw new MsgException("We couldn't find the mobile number given in database. Please contact the system administrator");
        }
        $otpData = $this->generateOtp($request->phone);
        $user->update([
            'otp' => $otpData['otp'],
            'otp_generated_at' => now(),
        ]);
        //$this->sendOtp($otp, $request->phone);
        if ($otpData['sendsms'] == 1) {
            $smssender->fetchContentSendSms($otpData, $request->phone, 6);
        }

        return new SuccessResponse('Otp send successfully');
    }

    /**
     * Generate random otp
     *
     * @return string
     */
    public function generateOtp($mobile) {
//        $mobiles = ["9061160000", "9539050000","9846583711", "8289847144", "9895670756", "8129160154","9495050000"];
//        if(in_array($mobile, $mobiles))
//        {
//            return "1111";
//        }
//        return mt_rand(1000, 9999);
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
     * Generate an event to send otp to user's mobile
     *
     * @param int $otp
     * @return void
     */
    private function sendOtp($otp, $mobile) {

        $msg = "Welcome to " . config('siteinfo.app_client_project_name') . " PackSure. " . $otp . " is your OTP to complete the registration process. Thank you for using PackSure "; //1607100000000004826
        return event(new OtpGenerated($mobile, $msg));
    }

}
