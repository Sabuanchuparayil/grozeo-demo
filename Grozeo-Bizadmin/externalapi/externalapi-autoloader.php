<?php
	$mapping = array(
	'Controllers\RequestAbstract' => __DIR__ . '/Controllers/RequestAbstract.php',
    'Controllers\RequestHandler' => __DIR__ . '/Controllers/RequestHandler.php',
	'Models\Auth' => __DIR__ . '/Models/Auth.php',
	'Models\User' => __DIR__ . '/Models/User.php',
	'Models\CallLog' => __DIR__ . '/Models/CallLog.php',
	'Models\ContactEntry' => __DIR__ . '/Models/ContactEntry.php',
	'Models\ModelAbstract' => __DIR__ . '/Models/ModelAbstract.php',	
	'Models\Utils' => __DIR__ . '/Models/Utils.php',
	'Views\Show' => __DIR__ . '/Views/Show.php'	
	);
	spl_autoload_register(function ($class) use ($mapping) {
		if (isset($mapping[$class])) {
			require $mapping[$class];
		}
	}, true);	