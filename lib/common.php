<?php
/**
 * Common functions and helpers
 */
class Common{

	public $app;
	
	public function Common($app){
		$this->app = $app;
	}

	// Get a MySQL connection
	public function getConnection() {
		// Requires keys.production.php
		$dbhost = DB_HOST;
		$dbname = DB_NAME;
	    $dbh = new PDO("mysql:host={$dbhost};dbname={$dbname}", DB_USER, DB_PASS);  
	    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    return $dbh;
	}

	// Set the size header for a given string
	public function setSizeHeader($content){
		$size = strlen($content);
		$this->app->response->headers->set('Content-Length', $size);
	}

	public function sendResponse($content, $status = 200){
		$this->app->response->headers->set('Content-Type', 'application/json');
		$this->app->response->setStatus($status);
		$res = json_encode($content);
		$this->setSizeHeader($res);
		$this->app->etag(sha1($res));
		$this->app->response->write($res);
	}
}