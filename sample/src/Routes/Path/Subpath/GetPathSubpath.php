<?php

namespace PhpApiSample\Routes\Path\Subpath;

use PhpApi\Model\Response\AbstractJsonResponse;

class GetPathSubpath
{
    public function execute($_, int $pathVar2): GetResponse
    {
        $response = new GetResponse(
            message: 'Hello World 2',
            pathVar: $pathVar2,
        );
        $response->setCode(200);
        return $response;
    }
}

class GetResponse extends AbstractJsonResponse
{
    public function __construct(
        public int $pathVar,
        public string $message = 'Hello World 2',
    ) {
    }
}
