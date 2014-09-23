<?php
header('Access-Control-Allow-Origin: *');
header('Content-Encoding: indentity');

// Disable GZIP compression for the API
ini_set('zlib.output_compression', 'Off');
ini_set('output_buffering', 'Off');
ini_set('output_handler', '');

// Start SLIM App
require 'vendor/autoload.php';

// Load helper functions
require 'lib/common.php';

/*
 * If you are forking this you will need a set of keys.
 * Take a look at keys.sample.php
 * The production keys are ignored for security purposes.
 */
require 'keys.production.php';

// DJs Music API configuration
	$app = new \Slim\Slim(array(
		'mode' => 'development', 	// production/development
		'debug' => true,			// true/false
	));
	$app->contentType("application/json");
	$app->setName('DJsMusic');

// API endpoints
	$app->get('/', 'getInfo');

// Load other routes
	require 'routes/albums.php';
	require 'routes/users.php';
	require 'routes/music.php';

$app->run();

// Functions
function getInfo(){
	return sendResponse('DJs Music API v1.1');
}