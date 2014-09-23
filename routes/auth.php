<?php
/**
 * Authentication Endpoint
 */

/**
 *  Check user Auth status based on cookie
 */
$app->get('/auth', function () use ($app) {
	return $app->common->sendResponse('Not ready yet', 501);
});
$app->get('/user', function () use ($app) {
	return $app->common->sendResponse('Not ready yet', 501);
});

$app->post('/auth/login', function () use ($app) {
	// Login data is coming as a payload in the body
	$data = json_decode(file_get_contents('php://input'));
	if(!isset($data->email) || !isset($data->pass)){
		return sendResponse(Array('error'=>'You must indicate user and password'), 400);
	}
	// Check in the database
	$con = $app->common->getConnection();
	$stmt = $con->prepare('SELECT id FROM users WHERE email = :email AND pass = :pass LIMIT 1');
	
	$stmt->bindValue(':email',$data->email);
	$stmt->bindValue(':pass',sha1($data->pass));
	
	$stmt->execute();
	
	$user = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if(count($user)<1){
		return $app->common->sendResponse(Array('error'=>'Invalid email or password','errorType'=>1), 401);
	}else{
		return $app->common->sendResponse(Array('user'=>$user));
	}
});