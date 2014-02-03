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
$app->get('/song', 'getSong');
$app->get('/songs/:param', 'songs');

$app->run();

// Functions
function getInfo(){
	echo json_encode('DJs Music API v1.0');
}

function getSong(){
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

function songs($param){
	switch($param){
		case 'latest':
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
			break;
		case 'top':
			$songs = Array(
				Array(
					'track'=> Array(
						'id' => 1,
						'name' => 'Top Song 1',
						'duration'=> 124,
						'downloads' => 34,
						'plays' => 247,
						'rating' => 5,
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
						'name' => 'Top Song 2',
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