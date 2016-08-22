<?php
require 'libs/Slim/Slim.php';
require 'libs/Slim/Middleware.php';

use \Slim\Middleware\HttpBasicAuth;
use \Slim\Middleware\CorsSlim;

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

require 'connect.php';

// $app->add(new HttpBasicAuth('admin', 'admin'));

define("UPLOADS_DIR", 'uploads/');


// production
define("API_URL", 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/api/v1/");

// local
// define("API_URL", 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/igospa/api/v1/");

$routers = glob('routers/*.router.php');
foreach ($routers as $router) {
    require $router;
}

$app->run();
