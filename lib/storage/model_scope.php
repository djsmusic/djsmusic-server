<?php
/**
 * ScopeModel Storage for the OAuth Scope data
 */
class ScopeModel implements \League\OAuth2\Server\Storage\ScopeInterface {

	private $db;

    public function __construct($app){

        $this->db = $app->common->getConnection();
    }

    public function getScope($scope, $clientId = null, $grantType = null){

		$statement = $this->db->prepare('SELECT * FROM oauth_scopes WHERE scope = :scope');
		$statement->setFetchMode(PDO::FETCH_OBJ);

        $statement->execute(array(
        	':scope' => $scope
        ));

		$row = $statement->fetch();

		if ($row) {
			return array(
				'id'	=>	$row->id,
				'scope'	=>	$row->scope,
				'name'	=>	$row->name,
				'description'	=>	$row->description
			);
		} else {
			return false;
		}

	}

}