<?php
header('Access-Control-Allow-Origin: *');
header('Content-Encoding: indentity');
// Disable GZIP compression for the API
ini_set('zlib.output_compression', 'Off');
ini_set('output_buffering', 'Off');
ini_set('output_handler', '');

// Start SLIM App
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
// Auth
$app->get('/auth', 'getAuth');
$app->get('/user', 'getAuth');
$app->post('/auth/login', 'login');

$app->run();

// Functions
function getInfo(){
	$return = json_encode('DJs Music API v1.0');
	setSizeHeader($return);
	echo $return;
	return;
}

/**
 *  Check user Auth status based on cookie
 */
function getAuth(){
	$return = json_encode(Array('error' => 'Client has no valid login cookies'));
	setSizeHeader($return);
	echo $return;
	return;
}

function login(){
	// Login data is coming as a payload in the body
	$data = json_decode(file_get_contents('php://input'));
	if(!isset($data->email) || !isset($data->pass)){
		echo json_encode(Array('error'=>'You must indicate user and password'));
		return;
	}
	// Check in the database
	$con = getConnection();
	$stmt = $con->prepare('SELECT id FROM users WHERE email = :email AND pass = :pass LIMIT 1');
	
	$stmt->bindValue(':email',$data->email);
	$stmt->bindValue(':pass',sha1($data->pass));
	
	$stmt->execute();
	
	$user = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if(count($user)<1){
		$return = json_encode(Array('error'=>'Invalid email or password','errorType'=>1));
		setSizeHeader($return);
		echo $return;
		return;
	}else{
		$return = json_encode(Array('user'=>$user));
		setSizeHeader($return);
		echo $return;
		return;
	}
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
			(SELECT src FROM pics WHERE id = albums.picid) AS albumPhoto,
			(SELECT COUNT(*) FROM music WHERE usid = users.id) AS trackNum
		FROM
			music LEFT OUTER JOIN albums ON music.albumid = albums.id,
			users
		WHERE
			music.usid = users.id AND music.id = ?
		LIMIT 1');
	$stmt->execute(Array($id));
	
	$track = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	if(count($track)>0){
		$track = $track[0];
	}else{
		echo json_encode(Array());
		return;
	}
	
	// Fix results
	$track['name'] = ucwords($track['name']);
	$track['user'] = ucwords($track['user']);
	$track['url'] = 'http://songs.djs-music.com/'.$track['url'];
	$track['released'] = date("j M Y",$track['released']);
	$track['tags'] = explode(',',$track['tags']);
	
	$user = Array(
		'id' => $track['artistId'],
		'name' => $track['user'],
		'photo' => 'http://static.djs-music.com/'.$track['userPhoto'],
		'tracks'=> $track['trackNum']
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
	unset($track['trackNum']);
	
	$result = Array(
		'track'=> $track,
		'artist'=> $user,
		'album' => $album
	);
	
	$return = json_encode($result);
	setSizeHeader($return);
	echo $return;
	return;
}

/**
 * Get a list of tracks with optional filters applied
 * Search:
 * 	- Title
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
	$data = Array();
	$filters = '';
	
	// Detect filters
	if(isset($_GET['user']) && $_GET['user']>0 && is_numeric($_GET['user'])){
		$filters .= ' AND users.id = :usid';
		$data[':usid'][] = $_GET['user'];
		$data[':usid'][] = PDO::PARAM_INT;
	}
	
	if(isset($_GET['album']) && $_GET['album']>0 && is_numeric($_GET['album'])){
		$filters .= ' AND music.albumid = :albumid';
		$data[':albumid'][] = $_GET['album'];
		$data[':albumid'][] = PDO::PARAM_INT;
	}
	
	// Title
	if(isset($_GET['title']) && strlen($_GET['title'])>0){
		$filters .= ' AND music.title LIKE :title';
		$data[':title'][] = '%'.$_GET['title'].'%';
		$data[':title'][] = PDO::PARAM_STR;
	}

	// Offset
	if(isset($_GET['items']) && $_GET['items']>5 && is_numeric($_GET['items']) && $_GET['items'] < 50){
		$data[':limit'][] = intval($_GET['items']);
		$data[':limit'][] = PDO::PARAM_INT;
		$limit = $_GET['items'];
		
	}else{
		$data[':limit'][] = 10;
		$data[':limit'][] = PDO::PARAM_INT;
		$limit = 10;
	}
	
	// Offset
	if(isset($_GET['page']) && $_GET['page']>0 && is_numeric($_GET['page'])){
		$data[':offset'][] = $_GET['page']*$limit;
		$data[':offset'][] = PDO::PARAM_INT;
	}else{
		$data[':offset'][] = 0;
		$data[':offset'][] = PDO::PARAM_INT;
	}
	
	// Ordering
	$orderby = 'music.timestamp DESC';
	
	if(isset($_GET['orderby'])){
		switch($_GET['orderby']){
			case 'best':
				$orderby = '((6*(music.r_total/music.r_users)) + 0.7*(music.r_total/music.r_users)*(music.downloads/(1.4*music.listens)) + 2.3*(music.r_total/music.r_users)*(music.r_users/music.listens)) + 0.05*music.listens + 0.1*music.downloads + 0.0001*music.timestamp DESC';
				break;
			case 'downloads':
				$orderby = 'music.downloads DESC';
				break;
		}
	}

	// Prepared statement
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
		LIMIT :offset,:limit');
	
	// Bind values
	while(list($k, $v) = each($data)){
		$stmt->bindValue($k,$v[0],$v[1]);
	}

	//$stmt->bindValue(':orderby', $orderby);
	
	// Execute query
	$stmt->execute();
	
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
	
	$return = json_encode($return);
	setSizeHeader($return);
	echo $return;
	return;
}

// Sample
function getAlbums(){
	$con = getConnection();
	
	// Filter data
	$data = Array();
	$filters = '';
	
	// Detect filters
	if(isset($_GET['user']) && $_GET['user']>0 && is_numeric($_GET['user'])){
		$filters .= ' AND users.id = :usid';
		$data[':usid'][] = $_GET['user'];
		$data[':usid'][] = PDO::PARAM_INT;
	}
	
	if(isset($_GET['album']) && $_GET['album']>0 && is_numeric($_GET['album'])){
		$filters .= ' AND music.albumid = :albumid';
		$data[':albumid'][] = $_GET['album'];
		$data[':albumid'][] = PDO::PARAM_INT;
	}
	
	// Title
	if(isset($_GET['name']) && strlen($_GET['name'])>0){
		$filters .= ' AND album.name LIKE :name';
		$data[':name'][] = '%'.$_GET['name'].'%';
		$data[':name'][] = PDO::PARAM_STR;
	}

	// Offset
	if(isset($_GET['items']) && $_GET['items']>0 && is_numeric($_GET['items']) && $_GET['items'] < 50){
		$data[':limit'][] = $_GET['items'];
		$data[':limit'][] = PDO::PARAM_INT;
		$limit = $_GET['items'];
		
	}else{
		$data[':limit'][] = 10;
		$data[':limit'][] = PDO::PARAM_INT;
		$limit = 10;
	}
	
	// Offset
	if(isset($_GET['page']) && $_GET['page']>0 && is_numeric($_GET['page'])){
		$data[':offset'][] = $_GET['page']*$limit;
		$data[':offset'][] = PDO::PARAM_INT;
	}else{
		$data[':offset'][] = 0;
		$data[':offset'][] = PDO::PARAM_INT;
	}
	
	// Ordering
	$orderby = 'albums.timestamp DESC';
	
	if(isset($_GET['orderby'])){
		switch($_GET['orderby']){
			case 'plays':
				$orderby = 'plays DESC';
				break;
			case 'downloads':
				$orderby = 'downloads DESC';
				break;
		}
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
		ORDER BY
			'.$orderby.'
		LIMIT :offset,:limit');
		
	// Bind values
	while(list($k, $v) = each($data)){
		$stmt->bindValue($k,$v[0],$v[1]);
	}
	
	$stmt->execute();
	
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
	
	$return = json_encode($return);
	setSizeHeader($return);
	echo $return;
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
		
	$return = json_encode($return);
	setSizeHeader($return);
	echo $return;
	return;
}

// Sample
function getUsers(){
	$return = json_encode('Not ready yet');
	setSizeHeader($return);
	echo $return;
	return;
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
	
	$return = json_encode($return);
	setSizeHeader($return);
	echo $return;
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

// Set the size header for a given string
function setSizeHeader($content){
	$size = strlen($content);
	header("Content-Length: $size");
}
