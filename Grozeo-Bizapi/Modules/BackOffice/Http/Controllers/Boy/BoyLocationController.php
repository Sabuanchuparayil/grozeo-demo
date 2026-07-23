<?php

namespace BackOffice\Http\Controllers\Boy;

use Illuminate\Support\Facades\Log;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\UnauthenticatedResponse;
use BackOffice\Http\Requests\BoyLocationRequest;

class BoyLocationController
{
    public function __invoke(BoyLocationRequest $request)
    {
        if(auth_user()->fcm_id != $request->fcm_id){
            return new UnauthenticatedResponse('FCM ID Mismatch');
        }
        auth_user()->update([
            'is_offline' => 0,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'latlng_updated_at' => now(),
            'loggedout_by' => 0,
        ]);

        return new SuccessResponse('Location stored successfully');
    }
}
