<?php

namespace BackOffice;

use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Response\DownstreamResponse;
use LaravelFCM\Message\PayloadNotificationBuilder;

class FcmNotification
{
    protected $to;
        
    protected $dataBuilder;

    protected $notificationBuilder;

    protected $optionBuilder;

    public function __construct(PayloadDataBuilder $dataBuilder, PayloadNotificationBuilder $notificationBuilder, OptionsBuilder $optionBuilder)
    {
        $this->dataBuilder = $dataBuilder;
        $this->notificationBuilder = $notificationBuilder;
        $this->optionBuilder = $optionBuilder;
    }

    public function setData($data): FcmNotification
    {
        $this->dataBuilder->addData($data);
        return $this;
    }

    public function setTitle(string $title): FcmNotification
    {
        $this->notificationBuilder->setTitle($title);
        return $this;
    }

    public function setBody(string $body): FcmNotification
    {
        $this->notificationBuilder->setBody($body);
        return $this;
    }

    public function setSound(string $sound): FcmNotification
    {
        $this->notificationBuilder->setSound($sound);
        return $this;
    }

    public function setTimeToLive(int $seconds): FcmNotification
    {
        $this->optionBuilder->setTimeToLive($seconds);
        return $this;
    }

    public function to($to): FcmNotification
    {
        $this->to = $to;
        return $this;
    }

    public function send(): DownstreamResponse
    {
        return FCM::sendTo(
            $this->to, 
            $this->optionBuilder->build(), 
            null, 
            $this->dataBuilder->build()
        );
    }
}
