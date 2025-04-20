<?php

namespace PhpApi\Test;

require_once __DIR__ . '/../sample/vendor/autoload.php';

use InvalidArgumentException;
use PhpApi\Model\RouterOptions;
use PhpApi\Router;
use PHPUnit\Framework\TestCase;
use Sapien\Request;

class RouterTest extends TestCase
{
    public const Directory =  __DIR__ . '/../sample/src/Routes';

    public function test_construct_hasGoodOptions_constructs(): void
    {
        $router = new Router(
            new RouterOptions(
                namespace: 'PhpApiSample\\Routes',
                directory: self::Directory,
            )
        );

        $this->assertInstanceOf(Router::class, $router);
    }

    public function test_construct_invalidFactory_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $router = new Router(
            new RouterOptions(
                namespace: 'PhpApiSample\\Routes',
                directory: self::Directory,
            ),
            controllerFactory: 'test',
        );

        $this->assertInstanceOf(Router::class, $router);
    }

    public function test_route_getEndpoint_returnsResponse(): void
    {
        $router = new Router(
            new RouterOptions(
                namespace: 'PhpApiSample\\Routes',
                directory: self::Directory,
            )
        );

        $response = $router->route(new Request(
            method: 'GET',
            url: [
                '/'
            ],
        ));

        $this->assertNotNull($response);
        $this->assertInstanceOf(\PhpApiSample\Routes\GetResponse::class, $response);
    }

    public function test_route_postEndpoint_returnsResponse(): void
    {
        $router = new Router(
            new RouterOptions(
                namespace: 'PhpApiSample\\Routes',
                directory: self::Directory,
            )
        );

        $response = $router->route(new Request(
            method: 'POST',
            url: [
                '/'
            ],
            globals: [
                '_POST' => [
                    'someVar' => 'test',
                    'someMessage' => 'test',
                    'subObject' => [
                        'subMessage' => 'test',
                        'subID' => 123,
                    ],
                ],
            ],
        ));

        $this->assertNotNull($response);
        $this->assertInstanceOf(\PhpApiSample\Routes\PostResponse::class, $response);
        $this->assertEquals('{"someVar":"test","someMessage":"test","subObject":{"subMessage":"test","subID":123}}', $response->message);
    }

    public function test_route_putEndpoint_returnsResponse(): void
    {
        $router = new Router(
            new RouterOptions(
                namespace: 'PhpApiSample\\Routes',
                directory: self::Directory,
            )
        );

        $response = $router->route(new Request(
            method: 'PUT',
            url: [
                '/'
            ],
        ));

        $this->assertNotNull($response);
        $this->assertInstanceOf(\PhpApiSample\Routes\PutResponse::class, $response);
    }

    public function test_route_deleteEndpoint_returnsResponse(): void
    {
        $router = new Router(
            new RouterOptions(
                namespace: 'PhpApiSample\\Routes',
                directory: self::Directory,
            )
        );

        $response = $router->route(new Request(
            method: 'DELETE',
            url: [
                '/'
            ],
        ));

        $this->assertNotNull($response);
        $this->assertInstanceOf(\PhpApiSample\Routes\DeleteResponse::class, $response);
    }

    public function test_route_invalidEndpoint_throwsException(): void
    {
        $router = new Router(
            new RouterOptions(
                namespace: 'PhpApiSample\\Routes',
                directory: self::Directory,
            )
        );

        $router->handleNotFound(
            'four04',
        );

        $response = $router->route(new Request(
            method: 'GET',
            url: [
                'http://',
                'localhost',
                80,
                '',
                '',
                '/invalid',
                '',
                '',
            ],
        ));

        $this->assertNotNull($response);
        $this->assertEquals(404, $response->getCode());
    }
}
