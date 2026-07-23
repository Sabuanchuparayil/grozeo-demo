<?php

return [

    'default' => "instamojo",

    'instamojo' => [

        'api_key' => env('IM_API_KEY', '8a68dea2c5298b11c0264ce411d83316'),

        'auth_token' => env('IM_AUTH_TOKEN', '9428a8401d68f0c4e38a95fc8ffa0b5a'),

        'url' => env('IM_URL', 'https://test.instamojo.com/api/1.1/'),

    ],
];