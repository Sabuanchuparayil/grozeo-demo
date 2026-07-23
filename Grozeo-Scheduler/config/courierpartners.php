<?php

return [
    'default' => '',

    'shipyaari' => [
        'creator'               => '5934',
        'avnkey'                => '5934@5181',
        'username'              => 'prevo',
        'search_partners'       => 'https://seller.shipyaari.com/logistic/webservice/SearchAvailability_new.php',
        'create_consignment'    => 'https://seller.shipyaari.com/logistic/webservice/create_consignment_api.php',
        'cancel_consignment'    => 'https://seller.shipyaari.com/avn_ci/siteadmin/cancel_consignment/',
        'track_current_status'  => 'https://seller.shipyaari.com/avn_ci/siteadmin/track/trackstatus',
        'track_complete_status' => 'https://seller.shipyaari.com/avn_ci/siteadmin/track/trackdetails'
    ],
    'worldoptions' => [
        'Key'               => 'Harry1',
        'MeterNumber'       => 'f5ebaa8937af439088fab3468b1b3ad1',
        'Password'          => 'LondonsPride8',
        'rateService'       => 'http://service.worldoptions.co.uk/RateService.svc?wsdl',
        'createShipment'    => 'http://service.worldoptions.co.uk/ShipmentService.svc?wsdl',
        'voidShipment'      => 'http://service.worldoptions.co.uk/VoidService.svc?wsdl',
        'tracking'          => 'http://service.worldoptions.co.uk/TrackingPOD.svc?wsdl'
    ]
];
