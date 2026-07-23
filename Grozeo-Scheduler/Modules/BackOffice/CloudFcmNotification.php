<?php

namespace BackOffice;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\FcmOptions;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Messaging\AndroidConfig;
use BackOffice\Models\FirebaseLog;
use Illuminate\Support\Facades\Log;
use Exception;
use BackOffice\Models\GodownBoy;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;

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
        $boyData = GodownBoy::where('fcm_id', $this->to)->value('id');
        $boyID = @$boyData ?? 0;
        $messaging = app('firebase.messaging');

      /*  $notification = Notification::fromArray([
            'title' => ($this->title =="")?"Retaline":$this->title,
            'body' => $this->body,
            'sound' => (string)$this->sound,
            'vibrate_timings' => '2s',   
           // 'ttl' => $this->timeToLive.'s'
        ]);
        $config = AndroidConfig::fromArray([
            'ttl' => $this->timeToLive.'s',
            'priority' => 'high',                   
            //'notification' =>$notification,
            'notification' => ['title' => ($this->title =="")?"Retaline":$this->title, 'body' => $this->body ,'sound' => (string)$this->sound,  'vibrate_timings' => ['1s','2s','1s','2s','1s','2s'], 'notification_priority' => 'PRIORITY_MAX']
        ]);
               
      //  $fcmOptions = FcmOptions::create()->withTimeToLive($this->timeToLive);
        $fcmOptions = [
            'analytics_label' => $this->analyticallabel
        ];*/
        $message = CloudMessage::fromArray([
            'token' => $this->to,
            'priority' => 'high',
            'notification' => [
                'title' => "Order Picker", 
                'body' => "You have a notification",
                'sound' => "notfctn2" ],
            'data' => $this->data, // optional
            /*'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'default_vibrate_timings' => false,
                        'default_sound' => true,
                        'vibrate_timings' => ['1s','2s','1s','2s','1s','2s'],
                        'color' => '#200e57',
                        'notification_priority' => 'PRIORITY_HIGH' // PRIORITY_LOW , PRIORITY_DEFAULT , PRIORITY_HIGH , PRIORITY_MAX
                    ],
                ],*/
            /*'apns' => [
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => '$GOOG up 1.43% on the day',
                            'body' => '$GOOG gained 11.80 points to close at 835.67, up 1.43% on the day.',
                        ],
                        'sound' => 'default',
                        'badge' => 42,
                    ],
                ],
            ],*/
            'fcm_options' => [
                'analytics_label' => $this->analyticallabel,
            ],
        ]);      
        /*$config = AndroidConfig::fromArray([
            'ttl' => '60s',
            'priority' => 'normal',           
            'notification' => [
                'title' => ($this->title =="")?"Retaline":$this->title,
                'body' =>   $this->data,
                'icon' => 'stock_ticker_update',
                'color' => '#f45342',
                'sound' => 'default',
            ],
        ]) ; */
        //$message = CloudMessage::withTarget('token', $this->to);
        //$message = $message->withData($this->data)
        // ->withFcmOptions($fcmOptions)->withAndroidConfig($config);
        try{     
            //$message = CloudMessage::new();
            //$response = $message->withAndroidConfig($config);
            $response = $messaging->send($message);
            FirebaseLog::create([
                "boy_id"        => $boyID,
                "rfir_StatusId" => 1,
                "rfir_token"    => $this->to,
                "rfir_Status"   => "success",
                "rfir_payload"  => json_encode($message),
                "rfir_date"     => now()
            ]);
            
            return $response;
  
        }catch(Exception $e){
            //$message["error"]=$e->getMessage();
           // $message["error"]="";
            FirebaseLog::create([
                "boy_id"        => $boyID,
                "rfir_StatusId" => 0,
                "rfir_token"    => $this->to,
                "rfir_Status"   => "error",
                "rfir_payload"  => $e->getMessage() ."-->" . json_encode( $message),
                "rfir_date"     => now()
            ]);
         

        } 

        
    }

        
}
