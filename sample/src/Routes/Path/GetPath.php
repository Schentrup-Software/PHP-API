<?php

namespace PhpApiSample\Routes\Path;

use PhpApi\Model\Response\AbstractJsonResponse;

class GetPath
{
    public function execute($_, int $pathVar): GetResponse
    {
        $response = new GetResponse(
            message: 'Hello World',
            pathVar: $pathVar,
        );
        $response->setCode(200);
        return $response;
    }
}

class GetResponse extends AbstractJsonResponse
{
    public const ResponseCode = 200;

    public function __construct(
        public int $pathVar,
        public string $message = 'Hello World',
    ) {
    }
}
