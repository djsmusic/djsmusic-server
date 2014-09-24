<?php
// Settings to make all errors more obvious during testing
error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

define('PROJECT_ROOT', realpath(__DIR__ . '/..'));

use There4\Slim\Test\WebTestCase;

require PROJECT_ROOT . '/vendor/autoload.php';

require PROJECT_ROOT . '/keys.testing.php';

require PROJECT_ROOT . '/lib/common.php';

// Load OAuth models
require PROJECT_ROOT . '/lib/storage/model_client.php';
require PROJECT_ROOT . '/lib/storage/model_scope.php';
require PROJECT_ROOT . '/lib/storage/model_session.php';

// Initialize our own copy of the slim application
class LocalWebTestCase extends WebTestCase {
    public function getSlimInstance() {
      $app = new \Slim\Slim(array(
          'version'        => '0.0.0',
          'debug'          => true,
          'mode'           => 'testing'
      ));

      $app->common = new Common($app);

      /**
       * Setup the OAuth Server
       */
      $OAuthServer = new \League\OAuth2\Server\Authorization(new ClientModel($app), new SessionModel($app), new ScopeModel($app));
      // Enable support for the authorization code grant
      $OAuthServer->addGrantType(new \League\OAuth2\Server\Grant\AuthCode());
      // Resource Server
      $OAuthResourceServer = new \League\OAuth2\Server\Resource(new SessionModel($app), new ScopeModel($app));

      // Include our core application file
      require PROJECT_ROOT . '/app.php';

      return $app;
    }
};

/* End of file bootstrap.php */