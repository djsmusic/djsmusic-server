<?php
/**
 * Users Endpoint
 */

$app->get('/users', function () use ($app) {
	return $app->common->sendResponse('Not ready yet', 501);
});

// Sample
$app->get('/users/:param', function ($id) use ($app) {
	$con = $app->common->getConnection();
	
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
	}else{
		return $app->common->sendResponse(array('artist'=>''), 404);
	}



	if(!array_key_exists ('photo', $info)) $info['photo'] = 'img/albums/no_album.gif';
	
	$info['photo'] = 'http://static.djs-music.com/'.$info['photo'];
	$info['name'] = ucwords($info['name']);
	$info['description'] = ucfirst(iconv("ISO-8859-1","UTF-8",$info['description']));
	
	$return = Array(
		'artist' => $info
	);
	
	return $app->common->sendResponse($return);
});