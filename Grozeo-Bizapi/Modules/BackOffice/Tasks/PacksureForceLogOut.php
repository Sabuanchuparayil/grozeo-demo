<?php

namespace BackOffice\Tasks;


use BackOffice\Models\GodownBoy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Responses\SuccessResponse;
use BackOffice\Traits\CanSendNotificationsToBoy;

class PacksureForceLogOut 
{     
    use CanSendNotificationsToBoy;
    public function __invoke($mobno)
    {
        $boy = $this->getGodownBoy($mobno);
        $this->sendNotificationToBoy(
            "ForceLogOut_".$mobno, 
            $boy->fcm_id,             
            0,
            -1,
            false,
            0,
            1
        );
        $boy->update([
            'has_open_orders' => 0, 
            'fcm_id' => '', 
            'is_offline' => 1,
            'logout_at' => now(),
            'loggedout_by' => 2
        ]);
        return new SuccessResponse("Send logout");
    }

    public function getGodownBoy($mobno)
    {
        $boy = GodownBoy::select('id', 'branch_id', 'fcm_id','name')
            ->where('phone', $mobno)
            ->first();        
        return $boy;
    }

}
