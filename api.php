<?php
header('Access-Control-Allow-Origin: *');  
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();

// DJs Music API configuration
$app = new \Slim\Slim(array(
	'mode' => 'development', 	// production/development
	'debug' => true,			// true/false
));

$app->contentType("application/json");

$app->setName('DJsMusic');


// API endpoints
$app->get('/', 'getInfo');
$app->get('/songs/:param', 'songs');

$app->run();

// Functions
function getInfo(){
	echo json_encode('DJs Music API v1.0');
}

function songs($param){
	switch($param){
		case 'latest':
			$songs = Array(
				Array('title'=> 'Latest Song 1', 'id'=>1, 'artist'=> 'DJ Name 1', 'thumb'=> 'img/logo.jpg', 'duration'=> 124, 'downloads' => 34, 'rating' => 3),
				Array('title'=> 'Latest Song 2', 'id'=>2, 'artist'=> 'DJ Name 2', 'thumb'=> 'img/logo.jpg', 'duration'=> 324, 'downloads' => 74,'rating' => 5)
			);
			break;
		case 'top':
			$songs = Array(
				Array('title'=> 'Top Song 1', 'id'=>1, 'artist'=> 'DJ Name 1', 'thumb'=> 'img/logo.jpg', 'duration'=> 124, 'downloads' => 34, 'rating' => 3),
				Array('title'=> 'Top Song 2', 'id'=>2, 'artist'=> 'DJ Name 2', 'thumb'=> 'img/logo.jpg', 'duration'=> 324, 'downloads' => 74,'rating' => 5)
			);
			break;
	}
	
	
	echo json_encode($songs);
}

function getConnection() {
    $dbhost="localhost";
    $dbuser="root";
    $dbpass="";
    $dbname="directory";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);  
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}