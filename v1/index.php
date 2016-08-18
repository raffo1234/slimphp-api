<?php  
require 'libs/Slim/Slim.php';
require 'libs/Slim/Middleware.php';

use \Slim\Middleware\HttpBasicAuth;
use \Slim\Middleware\CorsSlim;

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

require 'connect.php';

// $app->add(new HttpBasicAuth('admin', 'admin'));

$routers = glob('routers/*.router.php');
foreach ($routers as $router) {
    require $router;
}

$app->run();