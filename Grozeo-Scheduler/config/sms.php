<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Url for sms gateway
    |--------------------------------------------------------------------------
    | Here you can set the API url which is used to send sms across the
    | application.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Here you may specify the api key used to authenticate the sms gateway
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Sender ID
    |--------------------------------------------------------------------------
    |
    | Here you may specify the sender id for the sms API
    |
    */
    'default' => 'text_local',

    'soft_sms' => [
        'api_url' => env('SMS_API_URL', 'http://softsms.in/app/smsapi/index.php'),
        'api_key' => env('SMS_API_KEY', '5c382fe22944d'),
        'sender_id' => env('SMS_SENDER_ID', 'PKTKRT'),
        'class' => \App\Sms\SoftSms::class,
    ],
    'text_local' => [
        'api_url' => env('SMS_API_URL', 'https://api.textlocal.in/send/'),
        'api_key' => env('SMS_API_KEY', 'jK5qpBYq6PQ-Q4K5113bMlrkKDECHuFPz9RpwSg5L1REMOVE'),
        'sender_id' => env('SMS_SENDER_ID', 'GOGOMD'),
        'class' => \App\Sms\TextLocalSms::class,
    ],
	'kalyera' => [
        'api_url' => env('KSMS_API_URL', 'https://api.kaleyra.io/'),
        'api_key' => env('KSMS_API_KEY', 'Ab0e8bcda314b2a81d2542d2a89b6b4cb'),
        'sender_id' => env('KSMS_SENDER_ID', 'HXAP1659953387IN'),
        'sender_num' => env('KSMS_SENDER_NUM', 'SNUNTD'),
    ],
    'text_local_sms_username' => env('TEXT_LOCAL_SMS_USERNAME', 'Aa10-velosit'),
    'text_local_sms_password' => env('TEXT_LOCAL_SMS_PASSWORD', 'velosit1'),
    'text_local_sms_source' => env('TEXT_LOCAL_SMS_SOURCE', 'VELOSI'),
    'pocketkart_sms_provider' => env('POCKETKART_SMS_PROVIDER', 'FALSE'),
    'airtel_sms_provider' => env('AIRTEL_SMS_PROVIDER', 'true'),


];
