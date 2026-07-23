<?php

namespace BackOffice\Http\Controllers;

use BackOffice\Models\User;
use BackOffice\Models\Branch;
use App\Exceptions\MsgException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessWithData;
use App\Http\Controllers\VersionController;
use BackOffice\Http\Requests\OtpVerificationRequest;


class OtpVerificationController
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function __invoke(OtpVerificationRequest $request)
    {
        $storegroupid = getHeaderStoreGroup();
        $user = $this->user
            ->select('id', 'otp', 'otp_generated_at', 'is_offline','name','phone','branch_id','allowStoreClose','allowInventoryControl','status')
            ->where('phone', $request->phone)
            ->where('status', 1)
            ->first(); 
       
          
        if($user) {
           $token= createJwtToken($user);
        }else{
            throw new MsgException("We couldn't find the mobile number given in database. Please contact the system administrator");   
        }    
        if (!$this->isValidOtp($user, $request->otp)) {
            
            return new ErrorResponse('Invalid OTP');
        }

        if (!is_null($request->fcm_id)) {
            $this->updateFcmToken($user, $request->fcm_id);
        }
        $app_os = "CUSTOMER_ANDROID";
        $minversion = VersionController::getVersion($app_os);
        $user->token= $token;
        $user->MinVersion = $minversion;
        $branch = Branch::where('br_id', $user->branch_id)
        ->select('br_name', 'br_storeGroup')
        ->first();  
        $user->branch=$branch->br_name;
        $user->branch_group_id = $branch->br_storeGroup; 
	    $user->store_group_id= $storegroupid;

        User::where('id', $user->id)
        ->update([
            'last_access_at' => now()
        ]);
        return new SuccessWithData(
            $user
        );
        
        //return $user;

    }

    /**
     * Check if the otp is valid.
     *
     * @param \Illuminate\Database\Eloquent\Model $user
     * @param int $otp
     * @return boolean
     */
    protected function isValidOtp($user, $otp)
    {
        //$otp=$user->otp;
        return $user->otp == $otp && now()->diffInMinutes($user->otp_generated_at) < 60;
    }

    /**
     * Update the fcm device token of the user.
     *
     * @param \BackOffice\Models\User $user
     * @param string $fcmId
     * @return boolean
     */
    protected function updateFcmToken($user, $fcmId)
    {
        $this->user->where('fcm_id', $fcmId)
        ->update(['fcm_id' => '']);
 
        if($user->is_offline == '0'){
            $user->update(['logout_at' => now(), 'is_offline' => '1',  'loggedout_by' => 4]);
        }

        return $user->update(['fcm_id' => $fcmId, 'login_at' => now(), 'has_open_orders' => '0', 'is_offline' => '0', 'latlng_updated_at' => now(),  'loggedout_by' => 0]);
    }
}
