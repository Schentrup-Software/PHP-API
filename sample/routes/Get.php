<?php

namespace SchentrupSoftware\PhpApiSample;

use PhpApi\Model\Response\AbstractJsonResponse;

class Get
{
    public function execute(): AbstractJsonResponse
    {
        $response = new GetResponse();
        $response->setCode(200);
        return $response;
    }
}

class GetResponse extends AbstractJsonResponse
{
    public function __construct(
        public string $message = 'Hello World',
        public int $otherThing = 12,
    ) {
    }
}