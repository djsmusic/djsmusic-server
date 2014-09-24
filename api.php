<?php
define('PROJECT_ROOT', realpath(__DIR__));

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Content-Encoding: indentity');

// Disable GZIP compression for the API
ini_set('zlib.output_compression', 'Off');
ini_set('output_buffering', 'Off');
ini_set('output_handler', '');

// Start SLIM App
require PROJECT_ROOT . '/vendor/autoload.php';

// Load OAuth models
require PROJECT_ROOT . '/lib/storage/model_client.php';
require PROJECT_ROOT . '/lib/storage/model_scope.php';
require PROJECT_ROOT . '/lib/storage/model_session.php';

/**
 * Setup the OAuth Server
 */
$OAuthServer = new \League\OAuth2\Server\Authorization(new ClientModel($app), new SessionModel($app), new ScopeModel($app));
// Enable support for the authorization code grant
$OAuthServer->addGrantType(new \League\OAuth2\Server\Grant\AuthCode());

/*
 * If you are forking this you will need a set of keys.
 * Take a look at keys.sample.php
 * The production keys are ignored for security purposes.
 */
require PROJECT_ROOT . '/keys.production.php';

// Load helper functions
require_once PROJECT_ROOT . '/lib/common.php';

$composer = json_decode(file_get_contents(__DIR__ . '/composer.json'));

// DJs Music API configuration
$app = new \Slim\Slim(array(
	'version' => $composer->version,	// API version, from Composer settings
	'mode' => 'development', 			// production/development/testing
	'debug' => false,					// true/false
));

$app->container->singleton('common', function(){
	return new Common(\Slim\Slim::getInstance());
});

require PROJECT_ROOT . '/app.php';

$app->run();