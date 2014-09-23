<?php
/**
 * Authentication Endpoint
 */

$app->get('/auth', 'getAuth');
$app->get('/user', 'getAuth');
$app->post('/auth/login', 'login');

/**
 *  Check user Auth status based on cookie
 */
function getAuth(){
	return sendResponse('Not ready yet', 501);
}

function login(){
	// Login data is coming as a payload in the body
	$data = json_decode(file_get_contents('php://input'));
	if(!isset($data->email) || !isset($data->pass)){
		return sendResponse(Array('error'=>'You must indicate user and password'), 400);
	}
	// Check in the database
	$con = getConnection();
	$stmt = $con->prepare('SELECT id FROM users WHERE email = :email AND pass = :pass LIMIT 1');
	
	$stmt->bindValue(':email',$data->email);
	$stmt->bindValue(':pass',sha1($data->pass));
	
	$stmt->execute();
	
	$user = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if(count($user)<1){
		return sendResponse(Array('error'=>'Invalid email or password','errorType'=>1), 401);
	}else{
		return sendResponse(Array('user'=>$user));
	}
}