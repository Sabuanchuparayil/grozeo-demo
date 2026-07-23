<?php

return [

    'default' => 'razorpay',
    'web_redirect_url' =>  env('WEB_REDIRECT_URL','http://localhost:4200/#/orderComplete-payment/success'),

    'paytm' => [

        /*
    |--------------------------------------------------------------------------
    | Payment Gateway Callback Url
    |--------------------------------------------------------------------------
    | Here you can set the callback url which is used by the payment gateway 
    | when transaction completes.
    |
    */
        'callback_url' => env('PAYTM_CALLBACK_URL', 'https://securegw-stage.paytm.in/theia/paytmCallback'),

        /*
    |--------------------------------------------------------------------------
    | Merchant Key
    |--------------------------------------------------------------------------
    |
    | Here you may specify the merchant key used to authenticate the payment gateway
    |
    */

        'merchant_key' => env('PAYTM_MERCHANT_KEY', 'QaBcxPK%8G3IeTOi'),

        /*
    |--------------------------------------------------------------------------
    | Merchant MID
    |--------------------------------------------------------------------------
    |
    | Here you may specify the merchant mid for the payment gateway
    |
    */
        'merchant_mid' => env('PAYTM_MERCHANT_ID', 'IAMPSD49521324279700'),

        /**
         *  Class associated with the paytm payment Gateway
         */
        'class' => \App\PaymentGateways\Paytm::class,

    ],

    'instamojo' => [

        'api_key' => env('IM_API_KEY', '8a68dea2c5298b11c0264ce411d83316'),

        'auth_token' => env('IM_AUTH_TOKEN', '9428a8401d68f0c4e38a95fc8ffa0b5a'),

        'url' => env('IM_URL', 'https://test.instamojo.com/api/1.1/'),

        'class' => \App\PaymentGateways\InstamojoPayment::class,
    ],

    'easypay' => [

        'cid' => env('EASYPAY_CID', '6123'),

        'typ' => env('EASYPAY_TYP', 'TEST'),

        'ver' => env('EASYPAY_VER', '1.0'),

        'cny' => env('EASYPAY_CNY', 'INR'),

        're1' => env('EASYPAY_RE1', 'MN'),

        'paymenturl' => env('EASYPAY_PAYMENT_URL', 'https://uat-etendering.axisbank.co.in/easypay2.0/frontend/api/payment'),

        'tokenurl' => env('EASYPAY_TOKEN_URL', 'https://uat-etendering.axisbank.co.in/easypay2.0/frontend/api/generatetoken'),

        'enquiryurl' => env('EASYPAY_ENQUIRY_URL', 'https://uat-etendering.axisbank.co.in/easypay2.0/frontend/index.php/api/enquiry'),

        'checksumkey' => env('EASYPAY_CHECKSUM_KEY', 'axis'),       
        
        'encryptionkey' => env('EASYPAY_ENCRYPTION_KEY', 'axisbank12345678'),  

        'class' => \App\PaymentGateways\EasypayPayment::class,
    ],
    

    'atom' => [

        'login' => env('EASYPAY_CID', '6123'),

        'pass' => env('EASYPAY_TYP', 'TEST'),

        'ttype' => env('EASYPAY_VER', '1.0'),

        'txncurr' => env('EASYPAY_CNY', 'INR'),

        'clientcode' => env('EASYPAY_RE1', 'MN'),
        
        'custacc' => env('EASYPAY_RE1', 'MN'),

        'reqhashkey' => env('EASYPAY_RE1', 'MN'),

        'resphashkey' => env('EASYPAY_RE1', 'MN'),

        'aesreqhashkey' => env('EASYPAY_RE1', 'MN'),

        'aesreqhashkeysalt' => env('EASYPAY_RE1', 'MN'),

        'aesresphashkey' => env('EASYPAY_RE1', 'MN'),

        'aesresphashkeysalt' => env('EASYPAY_RE1', 'MN'),
       

        'paymenturl' => env('EASYPAY_PAYMENT_URL', 'https://uat-etendering.axisbank.co.in/easypay2.0/frontend/api/payment'),

        'tokenurl' => env('EASYPAY_TOKEN_URL', 'https://uat-etendering.axisbank.co.in/easypay2.0/frontend/api/generatetoken'),

        'enquiryurl' => env('EASYPAY_ENQUIRY_URL', 'https://uat-etendering.axisbank.co.in/easypay2.0/frontend/index.php/api/enquiry'),

        'checksumkey' => env('EASYPAY_CHECKSUM_KEY', 'axis'),       
        
        'encryptionkey' => env('EASYPAY_ENCRYPTION_KEY', 'axisbank12345678'),  

        'class' => \App\PaymentGateways\AtomPayment::class,
    ],

    'razorpay' => [

        'key_id' => env('RP_API_KEY_ID', 'rzp_test_ecNNffO9iUr2bV'),

        'key_secret' => env('RP_API_KEY', 'SHZNyRkHPFPEVsgAp04lIT65'),

        'cny' => env('RP_CNY', 'INR'),

        'url' => env('IM_URL', ''),

        'class' => \App\PaymentGateways\RazorPayment::class,
    ],
];
