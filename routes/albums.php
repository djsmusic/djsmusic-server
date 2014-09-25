<?php
/**
 * Albums Endpoint
 */

$app->get('/albums', function () use ($app) {
	$con = $app->common->getConnection();
	
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

	return $app->common->sendResponse($return);
});

// Sample
$app->get('/albums/:param', function ($id) use ($app) {
	$con = $app->common->getConnection();
	
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
	}else{
		return $app->common->sendResponse(Array(
			'album' => '',
			'artist'=> ''
		), 404);
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
		
	return $app->common->sendResponse($return);
});