<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpApi\Router;
use PhpApi\Model\RouterOptions;

$router = new Router(
    new RouterOptions(
        namespace: 'PhpApiSample\\Routes',
        directory: __DIR__ . '/../src/routes',
    )
);

$router->route();
