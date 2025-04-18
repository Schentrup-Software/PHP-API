<?php

namespace PhpApiSample\Routes;

use PhpApi\Model\Response\AbstractJsonResponse;

class Put
{
    public function execute(): PutResponse
    {
        $response = new PutResponse();
        return $response;
    }
}

class PutResponse extends AbstractJsonResponse
{
    public const ResponseCode = 200;

    public function __construct(
        public string $message = 'Resource updated successfully',
        public int $updatedId = 0,
    ) {
    }
}
