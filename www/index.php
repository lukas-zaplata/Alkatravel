<?php
	// absolute filesystem path to this web root
	define('WWW_DIR', __DIR__);
	
	// absolute filesystem path to the application root
	define('APP_DIR', WWW_DIR . '/../app');
	
	
	// Uncomment this line if you must temporarily take down your site for maintenance.
	// require '.maintenance.php';
	
	$container = require __DIR__ . '/../app/bootstrap.php';
	
	$container->getService('application')->run();