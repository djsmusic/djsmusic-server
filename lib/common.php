<?php
/**
 * Common functions and helpers
 */

// Get a MySQL connection
function getConnection() {
	// Requires keys.production.php
	$dbhost = DB_HOST;
	$dbname = DB_NAME;
    $dbh = new PDO("mysql:host={$dbhost};dbname={$dbname}", DB_USER, DB_PASS);  
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}

// Set the size header for a given string
function setSizeHeader($content){
	$size = strlen($content);
	global $app;
	$app->response->headers->set('Content-Length', $size);
}

function sendResponse($content, $status = 200){
	global $app;
	$app->response->headers->set('Content-Type', 'application/json');
	$app->response->setStatus($status);
	$res = json_encode($content);
	setSizeHeader($res);
	$app->etag(sha1($res));
	$app->response->setBody($res);
}