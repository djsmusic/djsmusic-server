<?php
/**
 * Music Endpoint
 */

$app->get('/music', 'getSongs');
$app->get('/music/:param', 'getSong');

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
		return sendResponse(
			Array(
				'track'=> '',
				'artist'=> '',
				'album' => ''
			)
		, 404);
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
	
	return sendResponse(Array(
		'track'=> $track,
		'artist'=> $user,
		'album' => $album
	));
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
	
	return sendResponse($return);
}