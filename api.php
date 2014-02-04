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

/**
 * Get a Song Object for the specified $id
 */
function getSong($id){
	$con = getConnection();
	
	$stmt = $con->prepare('
		SELECT
			music.id, music.title AS name, music.extra AS description, ROUND(music.size/1000000,2) AS size, music.duration, music.downloads, music.listens AS plays, FLOOR(music.r_total/music.r_users) AS rating, music.timestamp AS released, CEIL(music.bitrate/1000) AS bitrate, music.src AS url, music.genres AS tags,
			users.id AS artistId, users.user,
			(SELECT src FROM pics WHERE id = users.picid) AS userPhoto,
			albums.id AS albumId, albums.name AS albumName,
			(SELECT src FROM pics WHERE id = albums.picid) AS albumPhoto
		FROM
			music LEFT OUTER JOIN albums ON music.albumid = albums.id,
			users
		WHERE
			music.usid = users.id AND music.id = ?
		LIMIT 1');
	$stmt->execute(Array($id));
	
	$track = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	$track = $track[0];
	// Fix results
	$track['name'] = ucwords($track['name']);
	$track['user'] = ucwords($track['user']);
	$track['url'] = 'http://songs.djs-music.com/'.$track['url'];
	$track['released'] = date("j M Y",$track['released']);
	$track['tags'] = explode(',',$track['tags']);
	
	$user = Array(
		'id' => $track['artistId'],
		'name' => $track['user'],
		'photo' => 'http://static.djs-music.com/'.$track['userPhoto']
	);
	
	$album = Array(
		'id' => $track['albumId'],
		'name' => $track['albumName'],
		'photo' => 'http://static.djs-music.com/'.$track['albumPhoto']
	);
	
	// Delete those from the result
	unset($track['artistId']);
	unset($track['user']);
	unset($track['userPhoto']);
	unset($track['albumId']);
	unset($track['albumName']);
	unset($track['albumPhoto']);
	
	$result = Array(
		'track'=> $track,
		'artist'=> $user,
		'album' => $album
	);
	
	echo json_encode($result);
	return;
}

/**
 * Get a list of tracks with optional filters applied
 * Possible filters:
 * 	- User
 * 	- Album
 * Order results:
 * 	- Downloads
 * 	- Release date
 *  - Best tracks
 * Paging:
 * 	- Per page
 * 	- Page
 */
function getSongs(){
	$con = getConnection();
	
	// Filter data
	$filters = '';
	$data = Array();
	
	// Detect filters
	if(isset($_GET['user']) && $_GET['user']>0 && is_numeric($_GET['user'])){
		$filters .= ' AND users.id = ?';
		$data[] = $_GET['user'];
	}
	
	if(isset($_GET['album']) && $_GET['album']>0 && is_numeric($_GET['album'])){
		$filters .= ' AND music.albumid = ?';
		$data[] = $_GET['album'];
	}
	
	if(isset($_GET['orderby'])){
		switch($_GET['orderby']){
			case 'best':
				$orderby = '((6*(music.r_total/music.r_users)) + 0.7*(music.r_total/music.r_users)*(music.downloads/(1.4*music.listens)) + 2.3*(music.r_total/music.r_users)*(music.r_users/music.listens)) + 0.05*music.listens DESC';
				break;
			case 'downloads':
				$orderby = 'music.downloads DESC';
				break;
			default:
			case 'release':
				$orderby = 'music.timestamp DESC';
				break;
		}
	}
	
	$stmt = $con->prepare('
		SELECT
			music.id, music.title AS name, music.extra AS description, ROUND(music.size/1000000,2) AS size, music.duration, music.downloads, music.listens AS plays, FLOOR(music.r_total/music.r_users) AS rating, music.timestamp AS released, CEIL(music.bitrate/1000) AS bitrate, music.src AS url, music.genres AS tags,
			users.id AS artistId, users.user,
			(SELECT src FROM pics WHERE id = users.picid) AS userPhoto,
			albums.id AS albumId, albums.name AS albumName,
			(SELECT src FROM pics WHERE id = albums.picid) AS albumPhoto
		FROM
			music LEFT OUTER JOIN albums ON music.albumid = albums.id,
			users
		WHERE
			music.usid = users.id
			'.$filters.'
		ORDER BY
			'.$orderby.'
		LIMIT 0,10');
	$stmt->execute($data);
	
	$tracks = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$total = count($tracks);
	
	$return = Array();
	
	for($i=0;$i<$total;$i++){
		$track = $tracks[$i];
		
		// Fix results
		$track['name'] = ucwords($track['name']);
		$track['user'] = ucwords($track['user']);
		$track['url'] = 'http://songs.djs-music.com/'.$track['url'];
		$track['released'] = date("j M Y",$track['released']);
		$track['tags'] = explode(',',$track['tags']);
		
		if($track['albumPhoto'] == 'img/albums/no_album.gif'){
			$track['albumPhoto'] = $track['userPhoto'];
		}
		
		$user = Array(
			'id' => $track['artistId'],
			'name' => $track['user'],
			'photo' => 'http://static.djs-music.com/'.$track['userPhoto']
		);
		
		$album = Array(
			'id' => $track['albumId'],
			'name' => $track['albumName'],
			'photo' => 'http://static.djs-music.com/'.$track['albumPhoto']
		);
		
		// Delete those from the result
		unset($track['artistId']);
		unset($track['user']);
		unset($track['userPhoto']);
		unset($track['albumId']);
		unset($track['albumName']);
		unset($track['albumPhoto']);
		
		$return[] = Array(
			'track'=> $track,
			'artist'=> $user,
			'album' => $album
		);
	}
	
	echo json_encode($return);
	return;
}

// Sample
function getAlbums(){
	$con = getConnection();
	
	// Filter data
	$filters = '';
	$data = Array();
	
	// Detect filters
	if(isset($_GET['user']) && $_GET['user']>0 && is_numeric($_GET['user'])){
		$filters .= ' AND users.id = ?';
		$data[] = $_GET['user'];
	}
	
	if(isset($_GET['album']) && $_GET['album']>0 && is_numeric($_GET['album'])){
		$filters .= ' AND music.albumid = ?';
		$data[] = $_GET['album'];
	}
	
	$stmt = $con->prepare('
		SELECT
			users.id AS userId, users.user AS userName,
			(SELECT src FROM pics WHERE id = users.picid) AS userPhoto,
			albums.id AS id, albums.name, albums.description,
			(SELECT src FROM pics WHERE id = albums.picid) AS photo,
			(SELECT COUNT(*) FROM music WHERE music.albumid = albums.id) AS tracks,
			(SELECT SUM(listens) FROM music WHERE music.albumid = albums.id) AS plays,
			(SELECT SUM(downloads) FROM music WHERE music.albumid = albums.id) AS downloads
		FROM
			albums, users
		WHERE
			albums.usid = users.id
			'.$filters.'
		LIMIT 10');
	$stmt->execute($data);
	
	$albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	$total = count($albums);
	
	$return = Array();
	
	for($i=0;$i<$total;$i++){
		$info = $albums[$i];
		
		// Fix data
		$info['photo'] = 'http://static.djs-music.com/'.$info['photo'];
		
		$user = Array(
			'id' => $info['userId'],
			'name' => ucwords($info['userName']),
			'photo' => 'http://static.djs-music.com/'.$info['userPhoto']
		);
		
		// Delete those from the result
		unset($info['userId']);
		unset($info['userName']);
		unset($info['userPhoto']);
		
		$return[] = Array(
			'album' => $info,
			'artist' => $user
		);
	}
	
	echo json_encode($return);
	return;
}

// Sample
function getAlbum($id){
	$con = getConnection();
	
	$stmt = $con->prepare('
		SELECT
			users.id AS userId, users.user AS userName,
			(SELECT src FROM pics WHERE id = users.picid) AS userPhoto,
			albums.id AS id, albums.name, albums.description,
			(SELECT src FROM pics WHERE id = albums.picid) AS photo,
			(SELECT COUNT(*) FROM music WHERE music.albumid = albums.id) AS tracks,
			(SELECT SUM(listens) FROM music WHERE music.albumid = albums.id) AS plays,
			(SELECT SUM(downloads) FROM music WHERE music.albumid = albums.id) AS downloads
		FROM
			albums, users
		WHERE
			albums.usid = users.id AND albums.id = ?
		LIMIT 1');
	$stmt->execute(Array($id));
	
	$info = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	if(count($info)==1){
		$info = $info[0];
	}
	
	// Fix data
	$info['photo'] = 'http://static.djs-music.com/'.$info['photo'];
	
	$user = Array(
		'id' => $info['userId'],
		'name' => ucwords($info['userName']),
		'photo' => 'http://static.djs-music.com/'.$info['userPhoto']
	);
	
	// Delete those from the result
	unset($info['userId']);
	unset($info['userName']);
	unset($info['userPhoto']);
	
	$return = Array(
		'album' => $info,
		'artist'=> $user
	);
		
	echo json_encode($return);
	return;
}

// Sample
function getUsers(){
	$con = getConnection();
	
	$stmt = $con->prepare('
		SELECT
			users.id, users.user AS name, users.city, users.country, users.web, users.interests AS description,
			(SELECT src FROM pics WHERE id = users.picid) AS photo,
			(SELECT SUM(listens) FROM music WHERE music.usid = users.id) AS plays,
			(SELECT SUM(downloads) FROM music WHERE music.usid = users.id) AS downloads
		FROM
			users
		WHERE
			users.id = ?
		LIMIT 1');
	$stmt->execute(Array($id));
	
	$info = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode('Users data');
}

// Sample
function getUser($id){
	$con = getConnection();
	
	$stmt = $con->prepare('
		SELECT
			users.id, users.user AS name, users.city, users.country, users.web, users.interests AS description,
			(SELECT src FROM pics WHERE id = users.picid) AS photo,
			(SELECT SUM(listens) FROM music WHERE music.usid = users.id) AS plays,
			(SELECT SUM(downloads) FROM music WHERE music.usid = users.id) AS downloads
		FROM
			users
		WHERE
			users.id = ?
		LIMIT 1');
	$stmt->execute(Array($id));
	
	$info = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	if(count($info)==1){
		$info = $info[0];
	}
	
	$info['photo'] = 'http://static.djs-music.com/'.$info['photo'];
	$info['name'] = ucwords($info['name']);
	$info['description'] = ucfirst(iconv("ISO-8859-1","UTF-8",$info['description']));
	
	$return = Array(
		'artist' => $info
	);
	
	echo json_encode($return);
	return;
}

// Get a MySQL connection
function getConnection() {
	// Requires keys.production.php
	$dbhost = DB_HOST;
	$dbname = DB_NAME;
    $dbh = new PDO("mysql:host={$dbhost};dbname={$dbname}", DB_USER, DB_PASS);  
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}