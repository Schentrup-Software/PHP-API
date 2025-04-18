<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpApi\Router;
use PhpApi\Model\RouterOptions;
use PhpApi\Model\SwaggerOptions;
use PhpApiSample\Middleware\HeaderCheckMiddleware;
use PhpApiSample\Middleware\TimeResponseMiddleware;

$router = (new Router(
    new RouterOptions(
        namespace: 'PhpApiSample\\Routes',
        directory: __DIR__ . '/../src/Routes',
    ),
    new SwaggerOptions(
        title: "Sample API Documents",
        apiVersion: "1.2.3",
    )
))->addMiddleware(
    new TimeResponseMiddleware(),
)->addMiddleware(
    new HeaderCheckMiddleware(),
);

$router->route();
