<?php

$app->contentType("application/json");
$app->setName('DJsMusic');

/*
 * If you are forking this you will need a set of keys.
 * Take a look at keys.sample.php
 * The production keys are ignored for security purposes.
 */
require PROJECT_ROOT . '/keys.production.php';

// Load other routes
require PROJECT_ROOT . '/routes/albums.php';
require PROJECT_ROOT . '/routes/users.php';
require PROJECT_ROOT . '/routes/music.php';

$app->get('/', function () use ($app) {
	return $app->common->sendResponse('DJs Music API v'.$app->config('version'));
});