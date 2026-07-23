<?php

namespace BackOffice\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;

class SchedulerController
{
    public function testScheduler()
    {
        return response()->json([
            'status' => 'ok'
        ]);
    } 
}