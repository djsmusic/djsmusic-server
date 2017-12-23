<?php

$app->contentType("application/json");
$app->setName('DJsMusic');

// Load other routes
require PROJECT_ROOT . '/routes/albums.php';
require PROJECT_ROOT . '/routes/users.php';
require PROJECT_ROOT . '/routes/music.php';
// require PROJECT_ROOT . '/routes/auth.php';

$app->get('/', function () use ($app) {
	return $app->common->sendResponse('DJs Music API v'.$app->config('version'));
});

// Error handling
$app->error(function (\Exception $e) use ($app) {
	$status = 400;
	$message = $e->getMessage();
	// Customization
	if($e instanceof \League\OAuth2\Server\Exception\MissingAccessTokenException){
    	$status = 401;
	}
	$app->common->sendResponse(array('error' => $message), $status);
});