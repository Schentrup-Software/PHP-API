<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpApi\Router;
use PhpApi\Model\RouterOptions;
use PhpApiSample\Routes\Get;

$router = new Router(
    new RouterOptions(
        namespace: 'PhpApiSample\\Routes',
        directory: __DIR__ . '/../src/routes',
    )
);

$get = new Get();
$router->route();
