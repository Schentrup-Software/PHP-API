<?php

namespace PhpApi\Test;

use PhpApi\Model\RouterOptions;
use PhpApi\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function test_construct_hasGoodOptions_constructs(): void
    {
        $router = new Router(
            new RouterOptions(
                namespace: 'PhpApiSample\\Routes',
            )
        );

        $this->assertInstanceOf(Router::class, $router);
    }
}
