<?php
// Settings to make all errors more obvious during testing
error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

define('PROJECT_ROOT', realpath(__DIR__ . '/..'));

use There4\Slim\Test\WebTestCase;

require_once PROJECT_ROOT . '/vendor/autoload.php';

require PROJECT_ROOT . '/keys.testing.php';

require_once PROJECT_ROOT . '/lib/common.php';

// Initialize our own copy of the slim application
class LocalWebTestCase extends WebTestCase {
    public function getSlimInstance() {
      $app = new \Slim\Slim(array(
          'version'        => '0.0.0',
          'debug'          => true,
          'mode'           => 'testing'
      ));

      $app->common = new Common($app);

      // Include our core application file
      require_once PROJECT_ROOT . '/app.php';

      return $app;
    }
};

/* End of file bootstrap.php */