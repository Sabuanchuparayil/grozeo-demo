<?php

namespace App\Listeners;

use App\Events\OtpGenerated;
use App\Sms\SmsProviderInterFace;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOtp
{
    private $createSms;
     /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(SmsProviderInterFace $createSms)
    {
        $this->createSms = $createSms;
    }

    /**
     * Handle the event.
     *
     * @param  OtpGenerated  $event
     * @return void
     */
    public function handle(OtpGenerated $event)
    {
        $response = $this->createSms->sendSms($event->mobile, $event->msg);
        // print_r($response);exit;
    }


}
