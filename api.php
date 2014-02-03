<?php
header('Access-Control-Allow-Origin: *');  
require 'Slim/Slim.php';
/*
 * If you are forking this you will need a set of keys.
 * Take a look at keys.sample.php
 * The production keys are ignored for security purposes.
 */
require 'keys.production.php';

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
// Music endpoint
$app->get('/music', 'getSongs');
$app->get('/music/:param', 'getSong');
// Albums endpoint
$app->get('/albums', 'getAlbums');
$app->get('/albums/:param', 'getAlbum');
// Users endpoint
$app->get('/users', 'getUsers');
$app->get('/users/:param', 'getUser');

$app->run();

// Functions
function getInfo(){
	echo json_encode('DJs Music API v1.0');
}

// Sample
function getSong($param){
	$song = Array(
		'track'=> Array(
			'id' => 1,
			'name' => 'Latest Song 1',
			'duration'=> 124,
			'downloads' => 34,
			'plays' => 247,
			'rating' => 3,
			'released' => '8 Aug 2013',
			'description' => 'Track description...',
			'size' => '8,57',
			'bitrate' => 128,
			'url'=> 'http://songs.djs-music.com/26-19-C5sJG6f8em.mp3',
			'tags' => Array('Tag 1', 'Tag 2')
		),
		'artist' => Array(
			'id' => 1,
			'name' => 'DJ Name 1',
			'photo'=> 'http://static.djs-music.com/img/djs/eDyR54sg2c.jpg',
		),
		'album' => Array(
			'id' => 1,
			'name' => 'Album name 1',
			'photo'=> 'img/logo.jpg'
		)
	);
	
	echo json_encode($song);
}

// Sample
function getSongs(){
	$songs = Array(
		Array(
			'track'=> Array(
				'id' => 1,
				'name' => 'Latest Song 1',
				'duration'=> 124,
				'downloads' => 34,
				'plays' => 247,
				'rating' => 3,
				'released' => '8 Aug 2013',
				'description' => 'Track description...',
				'size' => '8,57',
				'bitrate' => 128,
				'url'=> 'http://songs.djs-music.com/26-19-C5sJG6f8em.mp3',
				'tags' => Array('Tag 1', 'Tag 2')
			),
			'artist' => Array(
				'id' => 1,
				'name' => 'DJ Name 1',
				'photo'=> 'http://static.djs-music.com/img/djs/eDyR54sg2c.jpg',
			),
			'album' => Array(
				'id' => 1,
				'name' => 'Album name 1',
				'photo'=> 'img/logo.jpg'
			)
		),
		Array(
			'track'=> Array(
				'id' => 1,
				'name' => 'Latest Song 2',
				'duration'=> 124,
				'downloads' => 34,
				'plays' => 247,
				'rating' => 3,
				'released' => '8 Aug 2013',
				'description' => 'Track description...',
				'size' => '8,57',
				'bitrate' => 128,
				'url'=> 'http://songs.djs-music.com/26-19-C5sJG6f8em.mp3',
				'tags' => Array('Tag 1', 'Tag 2')
			),
			'artist' => Array(
				'id' => 1,
				'name' => 'DJ Name 2',
				'photo'=> 'http://static.djs-music.com/img/djs/eDyR54sg2c.jpg',
			),
			'album' => Array(
				'id' => 1,
				'name' => 'Album name 1',
				'photo'=> 'img/logo.jpg'
			)
		),
	);
	
	
	echo json_encode($songs);
}

// Sample
function getAlbums(){
	echo json_encode('Albums data');
}

// Sample
function getAlbum(){
	$album = Array(
		'artist' => Array(
			'id' => 1,
			'name' => 'DJ Name 1',
			'photo'=> 'http://static.djs-music.com/img/djs/eDyR54sg2c.jpg',
		),
		'album' => Array(
			'id' => 1,
			'name' => 'Album name 1',
			'photo'=> 'img/logo.jpg',
			'description' => 'Album description',
			'plays' => 1314,
			'downloads' => 4523
		)
	);
	
	echo json_encode($album);
}

// Sample
function getUsers(){
	echo json_encode('Users data');
}

// Sample
function getUser(){
	$album = Array(
		'artist' => Array(
			'id' => 1,
			'name' => 'DJ Name 1',
			'photo'=> 'http://static.djs-music.com/img/djs/eDyR54sg2c.jpg',
			'description' => 'DJ Description / Short Bio'
		),
		'album' => Array(
			'id' => 1,
			'name' => 'Album name 1',
			'photo'=> 'img/logo.jpg',
			'description' => 'Album description',
			'plays' => 1314,
			'downloads' => 4523
		)
	);
	
	echo json_encode($album);
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