<?php
/**
 * SessionModel Storage for the OAuth Session data
 */
class SessionModel implements \League\OAuth2\Server\Storage\SessionInterface {

    private $db;

    public function __construct($app){

        $this->db = $app->common->getConnection();
    }

    /**
     * Create a new session
     *
     * @param  string $clientId  The client ID
     * @param  string $ownerType The type of the session owner (e.g. "user")
     * @param  string $ownerId   The ID of the session owner (e.g. "123")
     * @return int               The session ID
     */
    public function createSession($clientId, $ownerType, $ownerId){
        $stmt = $this->db->prepare('INSERT INTO oauth_sessions (client_id, owner_type,  owner_id) VALUES (:clientId, :ownerType, :ownerId)');
        $stmt->execute(array(
            'clientId' => $clientId,
            'ownerType' => $ownerType,
            'ownerId' => $ownerId
        ));

        return (int) $this->db->lastInsertId();
    }

    /**
     * Delete a session
     *
     * @param  string $clientId  The client ID
     * @param  string $ownerType The type of the session owner (e.g. "user")
     * @param  string $ownerId   The ID of the session owner (e.g. "123")
     * @return void
     */
    public function deleteSession($clientId, $ownerType, $ownerId){
        $stmt = $this->db->prepare('DELETE FROM oauth_sessions WHERE client_id = :clientId AND owner_type = :$ownerType AND owner_id = :$ownerId');
        $stmt->execute(array(
            'clientId' => $clientId,
            'ownerType' => $ownerType,
            'ownerId' => $ownerId
        ));
    }

    /**
     * Associate a redirect URI with a session
     *
     * @param  int    $sessionId   The session ID
     * @param  string $redirectUri The redirect URI
     * @return void
     */
    public function associateRedirectUri($sessionId, $redirectUri){
        $stmt = $this->db->prepare('INSERT INTO oauth_session_redirects (session_id, redirect_uri) VALUE (:sessionId, :redirectUri)');
        $stmt->execute(array(
            'sessionId' => $sessionId,
            'redirectUri' => $redirectUri
        ));
    }

    /**
     * Associate an access token with a session
     *
     * @param  int    $sessionId   The session ID
     * @param  string $accessToken The access token
     * @param  int    $expireTime  Unix timestamp of the access token expiry time
     * @return int                 The access token ID
     */
    public function associateAccessToken($sessionId, $accessToken, $expireTime){
        $stmt = $this->db->prepare('INSERT INTO oauth_session_access_tokens (session_id, access_token, access_token_expires) VALUES (:sessionId, :accessToken, :accessTokenExpire)');
        $stmt->execute(array(
            'sessionId' => $sessionId,
            'accessToken' => $accessToken,
            'accessTokenExpire' => $accessTokenExpire
        ));

        return (int) $this->db->lastInsertId();
    }

    /**
     * Associate a refresh token with a session
     *
     * @param  int    $accessTokenId The access token ID
     * @param  string $refreshToken  The refresh token
     * @param  int    $expireTime    Unix timestamp of the refresh token expiry time
     * @param  string $clientId      The client ID
     * @return void
     */
    public function associateRefreshToken($accessTokenId, $refreshToken, $expireTime, $clientId){
        $stmt = $this->db->prepare('INSERT INTO oauth_session_refresh_tokens (session_access_token_id, refresh_token, refresh_token_expires, client_id) VALUE (:accessTokenId, :refreshToken, :expireTime, :clientId)');
        $stmt->execute(array(
            'accessTokenId' => $accessTokenId,
            'refreshToken' => $refreshToken,
            'expireTime' => $expireTime,
            'clientId' => $clientId
        ));
    }

    /**
     * Assocate an authorization code with a session
     *
     * @param  int    $sessionId  The session ID
     * @param  string $authCode   The authorization code
     * @param  int    $expireTime Unix timestamp of the access token expiry time
     * @return int                The auth code ID
     */
    public function associateAuthCode($sessionId, $authCode, $expireTime){
        $stmt = $this->db->prepare('INSERT INTO oauth_session_authcodes (session_id, auth_code, auth_code_expires) VALUES (:sessionId, :authCode, :authCodeExpires)');
        $stmt->execute(array(
            'sessionId' => $sessionId,
            'authCode' => $authCode,
            'authCodeExpires' => $expireTime
        ));

        return (int) $this->db->lastInsertId();
    }

    /**
     * Remove an associated authorization token from a session
     *
     * @param  int    $sessionId   The session ID
     * @return void
     */
    public function removeAuthCode($sessionId){
        $stmt = $this->db->prepare('DELETE FROM oauth_session_authcodes WHERE session_id = :sessionId');
        $stmt->execute(array(
            'sessionId' => $sessionId
        ));
    }

    /**
     * Validate an authorization code
     *
     * Example SQL query:
     *
     * <code>
     * SELECT oauth_sessions.id AS session_id, oauth_session_authcodes.id AS authcode_id FROM oauth_sessions
     *  JOIN oauth_session_authcodes ON oauth_session_authcodes.`session_id` = oauth_sessions.id
     *  JOIN oauth_session_redirects ON oauth_session_redirects.`session_id` = oauth_sessions.id WHERE
     * oauth_sessions.client_id = :clientId AND oauth_session_authcodes.`auth_code` = :authCode
     *  AND `oauth_session_authcodes`.`auth_code_expires` >= :time AND
     *  `oauth_session_redirects`.`redirect_uri` = :redirectUri
     * </code>
     *
     * Expected response:
     *
     * <code>
     * array(
     *     'session_id' =>  (int)
     *     'authcode_id'  =>  (int)
     * )
     * </code>
     *
     * @param  string     $clientId    The client ID
     * @param  string     $redirectUri The redirect URI
     * @param  string     $authCode    The authorization code
     * @return array|bool              False if invalid or array as above
     */
    public function validateAuthCode($clientId, $redirectUri, $authCode){
        $stmt = $this->db->prepare('
            SELECT
                oauth_sessions.id AS session_id, oauth_session_authcodes.id AS authcode_id
            FROM
                oauth_sessions
                    JOIN
                        oauth_session_authcodes
                    ON
                        oauth_session_authcodes.`session_id` = oauth_sessions.id
                    JOIN
                        oauth_session_redirects
                    ON
                        oauth_session_redirects.`session_id` = oauth_sessions.id
            WHERE
                oauth_sessions.client_id = :clientId
                AND oauth_session_authcodes.`auth_code` = :authCode
                AND `oauth_session_authcodes`.`auth_code_expires` >= :time
                AND `oauth_session_redirects`.`redirect_uri` = :redirectUri
        ');

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute(array(
            'clientId' => $clientId,
            'authCode' => $authCode,
            'redirectUri' => $redirectUri,
            'time' => time()
        ));

        $row = $stmt->fetch();

        if ($row) {
            return $row;
        } else {
            return false;
        }
    }

    /**
     * Validate an access token
     *
     * Example SQL query:
     *
     * <code>
     * SELECT session_id, oauth_sessions.`client_id`, oauth_sessions.`owner_id`, oauth_sessions.`owner_type`
     *  FROM `oauth_session_access_tokens` JOIN oauth_sessions ON oauth_sessions.`id` = session_id WHERE
     *  access_token = :accessToken AND access_token_expires >= UNIX_TIMESTAMP(NOW())
     * </code>
     *
     * Expected response:
     *
     * <code>
     * array(
     *     'session_id' =>  (int),
     *     'client_id'  =>  (string),
     *     'owner_id'   =>  (string),
     *     'owner_type' =>  (string)
     * )
     * </code>
     *
     * @param  string     $accessToken The access token
     * @return array|bool              False if invalid or an array as above
     */
    public function validateAccessToken($accessToken){
        $stmt = $this->db->prepare('SELECT id, owner_id, owner_type FROM oauth_sessions WHERE access_token = :accessToken');
        $stmt->setFetchMode(PDO::FETCH_OBJ);
        $stmt->execute(array(
            ':accessToken' => $accessToken
        ));

        $row = $stmt->fetch();

        if ($row) {
            return array(
                'id'    =>  $row->id,
                'owner_type' =>  $row->owner_type,
                'owner_id'  =>  $row->owner_id
            );
        } else {
            return false;
        }
    }

    /**
     * Removes a refresh token
     *
     * Example SQL query:
     *
     * <code>
     * DELETE FROM `oauth_session_refresh_tokens` WHERE refresh_token = :refreshToken
     * </code>
     *
     * @param  string $refreshToken The refresh token to be removed
     * @return void
     */
    public function removeRefreshToken($refreshToken){
        $stmt = $this->db->prepare('DELETE FROM `oauth_session_refresh_tokens` WHERE refresh_token = :refreshToken');
        $stmt->execute(array(
            'refreshToken' => $refreshToken
        ));
    }

    /**
     * Validate a refresh token
     *
     * Example SQL query:
     *
     * <code>
     * SELECT session_access_token_id FROM `oauth_session_refresh_tokens` WHERE refresh_token = :refreshToken
     *  AND refresh_token_expires >= UNIX_TIMESTAMP(NOW()) AND client_id = :clientId
     * </code>
     *
     * @param  string   $refreshToken The refresh token
     * @param  string   $clientId     The client ID
     * @return int|bool               The ID of the access token the refresh token is linked to (or false if invalid)
     */
    public function validateRefreshToken($refreshToken, $clientId){
        $stmt = $this->db->prepare('
            SELECT session_access_token_id FROM `oauth_session_refresh_tokens` WHERE refresh_token = :refreshToken
            AND refresh_token_expires >= UNIX_TIMESTAMP(NOW()) AND client_id = :clientId
        ');

        $stmt->setFetchMode(PDO::FETCH_OBJ);

        $stmt->execute(array(
            'refreshToken' => $refreshToken,
            'clientId' => $clientId
        ));

        $row = $stmt->fetch();

        if ($row) {
            return $row->session_access_token_id;
        } else {
            return false;
        }
    }

    /**
     * Get an access token by ID
     *
     * Example SQL query:
     *
     * <code>
     * SELECT * FROM `oauth_session_access_tokens` WHERE `id` = :accessTokenId
     * </code>
     *
     * Expected response:
     *
     * <code>
     * array(
     *     'id' =>  (int),
     *     'session_id' =>  (int),
     *     'access_token'   =>  (string),
     *     'access_token_expires'   =>  (int)
     * )
     * </code>
     *
     * @param  int    $accessTokenId The access token ID
     * @return array
     */
    public function getAccessToken($accessTokenId){
        $stmt = $this->db->prepare('SELECT * FROM `oauth_session_access_tokens` WHERE `id` = :accessTokenId');

        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->execute(array(
            'accessTokenId' => $accessTokenId
        ));

        $row = $stmt->fetch();

        if ($row) {
            return $row;
        } else {
            return false;
        }
    }

    /**
     * Associate scopes with an auth code (bound to the session)
     *
     * Example SQL query:
     *
     * <code>
     * INSERT INTO `oauth_session_authcode_scopes` (`oauth_session_authcode_id`, `scope_id`) VALUES
     *  (:authCodeId, :scopeId)
     * </code>
     *
     * @param  int $authCodeId The auth code ID
     * @param  int $scopeId    The scope ID
     * @return void
     */
    public function associateAuthCodeScope($authCodeId, $scopeId){
        $stmt = $this->db->prepare('INSERT INTO `oauth_session_authcode_scopes` (`oauth_session_authcode_id`, `scope_id`) VALUES (:authCodeId, :scopeId)');
        $stmt->execute(array(
            'authCodeId' => $authCodeId,
            'scopeId' => $scopeId
        ));
    }

    /**
     * Get the scopes associated with an auth code
     *
     * Example SQL query:
     *
     * <code>
     * SELECT scope_id FROM `oauth_session_authcode_scopes` WHERE oauth_session_authcode_id = :authCodeId
     * </code>
     *
     * Expected response:
     *
     * <code>
     * array(
     *     array(
     *         'scope_id' => (int)
     *     ),
     *     array(
     *         'scope_id' => (int)
     *     ),
     *     ...
     * )
     * </code>
     *
     * @param  int   $oauthSessionAuthCodeId The session ID
     * @return array
     */
    public function getAuthCodeScopes($oauthSessionAuthCodeId){
        $stmt = $this->db->prepare('SELECT scope_id FROM `oauth_session_authcode_scopes` WHERE oauth_session_authcode_id = :authCodeId');

        $stmt->setFetchMode(PDO::FETCH_OBJ);

        $stmt->execute(array(
            'oauth_session_authcode_id' => $oauthSessionAuthCodeId
        ));

        $scopes = array();

        while ($row = $stmt->fetch()) {
            $scopes[] = $row->scope_id;
        }

        return $scopes;
    }

    /**
     * Associate a scope with an access token
     *
     * Example SQL query:
     *
     * <code>
     * INSERT INTO `oauth_session_token_scopes` (`session_access_token_id`, `scope_id`) VALUE (:accessTokenId, :scopeId)
     * </code>
     *
     * @param  int    $accessTokenId The ID of the access token
     * @param  int    $scopeId       The ID of the scope
     * @return void
     */
    public function associateScope($accessTokenId, $scopeId){
        $stmt = $this->db->prepare('INSERT INTO `oauth_session_token_scopes` (`session_access_token_id`, `scope_id`) VALUES (:accessTokenId, :scopeId)');
        $stmt->execute(array(
            'accessTokenId' => $accessTokenId,
            'scopeId' => $scopeId
        ));
    }

    /**
     * Get all associated access tokens for an access token
     *
     * Example SQL query:
     *
     * <code>
     * SELECT oauth_scopes.* FROM oauth_session_token_scopes JOIN oauth_session_access_tokens
     *  ON oauth_session_access_tokens.`id` = `oauth_session_token_scopes`.`session_access_token_id`
     *  JOIN oauth_scopes ON oauth_scopes.id = `oauth_session_token_scopes`.`scope_id`
     *  WHERE access_token = :accessToken
     * </code>
     *
     * Expected response:
     *
     * <code>
     * array (
     *     array(
     *         'id'     =>  (int),
     *         'scope'  =>  (string),
     *         'name'   =>  (string),
     *         'description'    =>  (string)
     *     ),
     *     ...
     *     ...
     * )
     * </code>
     *
     * @param  string $accessToken The access token
     * @return array
     */
    public function getScopes($accessToken){
        $statement = $this->db->prepare('SELECT oauth_session_scopes.scope_id AS id, oauth_scopes.scope, oauth_scopes.name, oauth_scopes.description FROM oauth_session_scopes JOIN oauth_scopes ON oauth_session_scopes.scope_id = oauth_scopes.id WHERE session_id = :id');

        $statement->setFetchMode(PDO::FETCH_ASSOC);

        $statement->execute(array(':id' => $sessionId));

        $scopes = array();

        while ($row = $statement->fetch()) {
            $scopes[] = $row;
        }

        return $scopes;
    }
}
