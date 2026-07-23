<?php

namespace BackOffice\Http\Controllers\Boy;

use DB;
use BackOffice\Status\BoyOrderStatus;
use App\Http\Responses\SuccessResponse;

class LogoutController
{
    public function __invoke()
    {
        DB::transaction(function() {
            $boy = auth_user();

            $boy->orders()
                ->where('status', BoyOrderStatus::ACCEPTED)
                ->orWhere('status', BoyOrderStatus::SCANNING_STARTED)
                ->update(['status' => BoyOrderStatus::REVOKED]);

            $boy->update([
                'has_open_orders' => 0, 
                'fcm_id' => '', 
                'is_offline' => 1,
                'logout_at' => now(),
                'loggedout_by' => 1,
            ]);

        });

        return new SuccessResponse('Successfully looged out');
    }
}
