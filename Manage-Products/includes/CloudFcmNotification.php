<?php



use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\FcmOptions;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Messaging\AndroidConfig;
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
       // info("data-->".json_encode($data));
        return $this;
    }
    public function setData($data): CloudFcmNotification
    {
        $this->data=$data;

      // info("data inside-->".json_encode($data));
       //Log::debug($this->data);
        return $this;
    }

    public function setTitle(string $title): CloudFcmNotification
    {
        $this->title = $title;
     //   info("title-->".json_encode($title));
        return $this;
    }

    public function setBody(string $body): CloudFcmNotification
    {
        $this->body =  $body;
   //     info("body-->".json_encode($body));
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
        //  info("to-->".json_encode($to));
        return $this;
    }

    public function send()
    {

        $factory = (new Factory)->withServiceAccount(FIREBASE_CREDENTIALS);
        $messaging = $factory->createMessaging();
        $message = CloudMessage::fromArray([
            'token' => $this->to,
            /*'notification' => ['title' => ($this->title =="")?"Retaline":$this->title, 'body' => $this->body],*/
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
            /*'apns' => [],*/
            'fcm_options' => [
                'analytics_label' => $this->analyticallabel,
            ],
        ]);        

        try{     
            $response = $messaging->send($message);           
            return $response;
  
        }catch(Exception $e){
            throw  $e;

        } 

        
    }

        
}
