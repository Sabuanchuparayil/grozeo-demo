<?php

namespace App\Sms;

interface SmsProviderInterFace
{
    public function sendSms($mobile, $msg);
}