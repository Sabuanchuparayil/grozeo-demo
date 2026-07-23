<?php

namespace App;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\FcmOptions;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Messaging\AndroidConfig;
use App\Models\{
    FirebaseLog,
    Drivers\DriveFirebaseLog
};
use Illuminate\Support\Facades\Log;
use Exception; 
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Factory;

class CloudFcmNotification
{
    protected $messaging;
    protected $to; 
    protected $data;
    protected $title;
    protected $body;
    protected $sound;
    protected $timeToLive;
    protected $analyticallabel;
     /** @var MessagingApiExceptionConverter */
    private $errorHandler;
        
   public function __construct()
    {
        $this->errorHandler = new MessagingApiExceptionConverter();
    }
    public function setAnalyticalLabel($analyticallabel): CloudFcmNotification
    {
        $this->analyticallabel=$analyticallabel;
        return $this;
    }
    public function setData($data): CloudFcmNotification
    {
        $this->data=$data;
        return $this;
    }

    public function setTitle(string $title): CloudFcmNotification
    {
        $this->title = $title;
        return $this;
    }

    public function setBody(string $body): CloudFcmNotification
    {
        $this->body =  $body;
        return $this;
    }

    public function setSound(string $sound): CloudFcmNotification
    {
        $this->sound=$sound;
        return $this;
    }

    public function setTimeToLive(int $seconds): CloudFcmNotification
    {
        $this->timeToLive= $seconds;
        return $this;
    }

    public function to($to): CloudFcmNotification
    {
        $this->to = $to;
        return $this;
    }

    public function send()
    {
        $messaging = app('firebase.messaging');

        $message = CloudMessage::fromArray([
            'token'         => $this->to,
            'priority'      => 'high',
            'notification'  => [
                'title'         => $this->title ?? "Order Picker",
                'body'          => "You have a notification"
            ],
            'data'          => $this->data ?? [],
            'android'       => [
                    'priority'      => 'high',
                    'notification'  => [
                        "sound" => "notfctn2"
                    ],
                ],
            'apns'          => [
                'headers'           => [
                    'apns-priority' => '10',
                ],
                'payload'           => [
                    'aps'   => [
                        'alert' => [
                            'title' => $this->title ?? "Grozeo",
                            'body'  => "You have a notification",
                        ],
                        'sound'     => "notfctn2.caf"
                    ],
                ],
            ],
            'fcm_options'   => [
                'analytics_label'   => $this->analyticallabel ?? null,
            ],
        ]);
        try
        {
            $response = $messaging->send($message);
            FirebaseLog::create([
                "rfir_StatusId" =>1,
                "rfir_token"    =>$this->to,
                "rfir_Status"   =>"success",
                "rfir_payload"  =>json_encode($message),
                "rfir_date"     =>now()
            ]);
            return $response;
  
        }
        catch(Exception $e)
        {
            FirebaseLog::create([
                "rfir_StatusId" => 0,
                "rfir_token"    => $this->to,
                "rfir_Status"   => "error",
                "rfir_payload"  => $e->getMessage()."-->".json_encode( $message),
                "rfir_date"     => now()
            ]);
        }
    }

    public function sendDrive()
    {
        $firebaseService = (new Factory)->withServiceAccount(config("firebase.drive"));
        $messaging = $firebaseService->createMessaging();

        $message = [
            'token'         => $this->to,
            'priority'      => 'high',
            'notification'  => [
                'title'         => $this->title,
                'body'          => "You have a notification"
            ],
            'data'          => $this->data ?? [],
            'android'       => [
                    'priority'      => 'high',
                    'notification'  => [
                        "sound" => "notfctn2"
                    ],
                ],
            'apns'          => [
                'headers'           => [
                    'apns-priority' => '10',
                ],
                'payload'           => [
                    'aps'   => [
                        'alert' => [
                            'title' => $this->title,
                            'body'  => "You have a notification",
                        ],
                        'sound'     => "notfctn2.caf"
                    ],
                ],
            ],
            'fcm_options'   => [
                'analytics_label'   => $this->analyticallabel,
            ],
        ];
        try
        {
            $response = $messaging->send($message);
            DriveFirebaseLog::create([
                "rfir_StatusId" => 1,
                "rfir_token"    => $this->to,
                "rfir_Status"   => "success",
                "rfir_payload"  => json_encode($message),
                "rfir_date"     => now()
            ]);
            return $response;
  
        }
        catch(Exception $e)
        {
            DriveFirebaseLog::create([
                "rfir_StatusId" => 0,
                "rfir_token"    => $this->to,
                "rfir_Status"   => "error",
                "rfir_payload"  => $e->getMessage()." --> ".json_encode( $message),
                "rfir_date"     => now()
            ]);
        }
    }
}