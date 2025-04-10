<?php

namespace PhpApiSample\Routes;

use PhpApi\Model\Response\AbstractJsonResponse;

class Get
{
    public function execute(): GetResponse
    {
        $response = new GetResponse();
        $response->setCode(200);
        return $response;
    }
}

class GetResponse extends AbstractJsonResponse
{
    public const ResponseCode = 200;

    public function __construct(
        public string $message = 'Hello World',
        public int $otherThing = 12,
    ) {
    }
}
