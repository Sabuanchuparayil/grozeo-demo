<?php
$mapping = array(
	'Controllers\RequestAbstract' => __DIR__ . '/Controllers/RequestAbstract.php',
    'Controllers\RequestHandler' => __DIR__ . '/Controllers/RequestHandler.php',
	'Models\Auth' => __DIR__ . '/Models/Auth.php',
	'Models\User' => __DIR__ . '/Models/User.php',
	'Models\Customer' => __DIR__ . '/Models/Customer.php',
	'Models\ModelAbstract' => __DIR__ . '/Models/ModelAbstract.php',	
	'Models\PackingType' => __DIR__ . '/Models/PackingType.php',	
	'Models\ContentType' => __DIR__ . '/Models/ContentType.php',	
	'Models\Pincode' => __DIR__ . '/Models/Pincode.php',	
	'Models\Charges' => __DIR__ . '/Models/Charges.php',	
	'Models\Consignment' => __DIR__ . '/Models/Consignment.php',	
	'Models\Carting' => __DIR__ . '/Models/Carting.php',
	'Models\Dashboard' => __DIR__ . '/Models/Dashboard.php',
	'Models\QugeoRegistration' => __DIR__ . '/Models/QugeoRegistration.php',
	'Models\QugeoScheduler' => __DIR__ . '/Models/QugeoScheduler.php',
	'Models\QugeoOrderPoller' => __DIR__ . '/Models/QugeoOrderPoller.php',
	'Models\QugeoOrderHandler' => __DIR__ . '/Models/QugeoOrderHandler.php',
	'Models\CrossBooking' => __DIR__ . '/Models/CrossBooking.php',
	'Models\Utils' => __DIR__ . '/Models/Utils.php',
	'Views\Show' => __DIR__ . '/Views/Show.php'	
);
spl_autoload_register(function ($class) use ($mapping) {
    if (isset($mapping[$class])) {
        require $mapping[$class];
    }
}, true);