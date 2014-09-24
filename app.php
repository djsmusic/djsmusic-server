<?php

$app->contentType("application/json");
$app->setName('DJsMusic');

// Load other routes
require PROJECT_ROOT . '/routes/albums.php';
require PROJECT_ROOT . '/routes/users.php';
require PROJECT_ROOT . '/routes/music.php';

$app->get('/', function () use ($app) {
	return $app->common->sendResponse('DJs Music API v'.$app->config('version'));
});