<?php

namespace PhpApiSample\Routes;

use PhpApi\Model\Response\AbstractJsonResponse;

class Delete
{
    public function execute(): DeleteResponse
    {
        $response = new DeleteResponse();
        return $response;
    }
}

class DeleteResponse extends AbstractJsonResponse
{
    public const ResponseCode = 204;

    public function __construct(
        public string $message = 'Resource deleted successfully',
    ) {
    }
}
