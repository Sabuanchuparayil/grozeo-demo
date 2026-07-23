<?php
namespace App;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Messaging;
use App\Models\Drivers\FirebaseLog;
use Exception;

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

    public function __construct()
    {
        /* $firebase = (new Factory)
            ->withServiceAccount(config('fcm.credentials.file'));
        $this->messaging = $firebase->createMessaging(); */
    }

    public function setAnalyticalLabel($analyticallabel): CloudFcmNotification
    {
        $this->analyticallabel = $analyticallabel;
        return $this;
    }

    public function setData($data): CloudFcmNotification
    {
        $this->data = $data;
        return $this;
    }

    public function setTitle(string $title): CloudFcmNotification
    {
        $this->title = $title;
        return $this;
    }

    public function setBody(string $body): CloudFcmNotification
    {
        $this->body = $body;
        return $this;
    }

    public function setSound(string $sound): CloudFcmNotification
    {
        $this->sound = $sound;
        return $this;
    }

    public function setTimeToLive(int $seconds): CloudFcmNotification
    {
        $this->timeToLive = $seconds;
        return $this;
    }

    public function to($to): CloudFcmNotification
    {
        $this->to = $to;
        return $this;
    }

    public function send()
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
            FirebaseLog::create([
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
            FirebaseLog::create([
                "rfir_StatusId" => 0,
                "rfir_token"    => $this->to,
                "rfir_Status"   => "error",
                "rfir_payload"  => $e->getMessage()." --> ".json_encode( $message),
                "rfir_date"     => now()
            ]);
        }
    }
}