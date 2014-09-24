<?php
class ClientModel implements \League\OAuth2\Server\Storage\ClientInterface {

	private $db;

    public function __construct($app){

        $this->db = $app->common->getConnection();
    }

	/**
     * Validate a client
     *
     * Response:
     *
     * <code>
     * Array
     * (
     *     [client_id] => (string) The client ID
     *     [client secret] => (string) The client secret
     *     [redirect_uri] => (string) The redirect URI used in this request
     *     [name] => (string) The name of the client
     *     [auto_approve] => (bool) Whether the client should auto approve
     * )
     * </code>
     *
     * @param  string     $clientId     The client's ID
     * @param  string     $clientSecret The client's secret (default = "null")
     * @param  string     $redirectUri  The client's redirect URI (default = "null")
     * @param  string     $grantType    The grant type used in the request (default = "null")
     * @return bool|array               Returns false if the validation fails, array on success
     */
    public function getClient($clientId, $clientSecret = null, $redirectUri = null, $grantType = null){
    	if(!is_null($clientSecret) && !is_null($redirectUri)){
    		$statement = $this->db->prepare('SELECT oauth_clients.id, oauth_clients.secret, oauth_client_endpoints.redirect_uri, oauth_clients.name,
				oauth_clients.auto_approve FROM oauth_clients LEFT JOIN oauth_client_endpoints 
				ON oauth_client_endpoints.client_id = oauth_clients.id
				WHERE oauth_clients.id = :clientId AND oauth_clients.secret = :clientSecret AND
				oauth_client_endpoints.redirect_uri = :redirectUri');

    		$statement->setFetchMode(PDO::FETCH_OBJ);

    		$statement->execute(array(
        		':clientId' => $clientId,
        		':clientSecret' => $clientSecret,
        		':redirectUri' => $redirectUri
        	));
    	}else if(!is_null($clientSecret)){
			$statement = $this->db->prepare('SELECT oauth_clients.id, oauth_clients.secret, oauth_clients.name, oauth_clients.auto_approve FROM oauth_clients 
				WHERE oauth_clients.id = :clientId AND oauth_clients.secret = :clientSecret');

			$statement->setFetchMode(PDO::FETCH_OBJ);

    		$statement->execute(array(
        		':clientId' => $clientId,
        		':clientSecret' => $clientSecret
        	));
    	}else if(!is_null($redirectUri)){
			$statement = $this->db->prepare('SELECT oauth_clients.id, oauth_clients.secret, oauth_client_endpoints.redirect_uri, oauth_clients.name,
				oauth_clients.auto_approve
				FROM oauth_clients LEFT JOIN oauth_client_endpoints ON oauth_client_endpoints.client_id = oauth_clients.id
				WHERE oauth_clients.id = :clientId AND oauth_client_endpoints.redirect_uri = :redirectUri');

			$statement->setFetchMode(PDO::FETCH_OBJ);

    		$statement->execute(array(
        		':clientId' => $clientId,
        		':redirectUri' => $redirectUri
        	));
    	}else{
    		return false;
    	}

    	$row = $statement->fetchAll(PDO::FETCH_ASSOC);

    	if(count($row)<1) return false;

    	$row = $row[0];

    	return array(
			'client_id' => $row['id'],
			'client secret' => $row['secret'],
			'redirect_uri' => $row['redirect_uri'],
			'name' => $row['name']
		);
    }

}