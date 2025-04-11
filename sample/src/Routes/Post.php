<?php

namespace PhpApiSample\Routes;

use PhpApi\Model\Response\AbstractJsonResponse;

class Post
{
    public function execute(): PostResponse
    {
        $response = new PostResponse();
        $response->setCode(201);
        return $response;
    }
}

class PostResponse extends AbstractJsonResponse
{
    public const ResponseCode = 201;

    public function __construct(
        public string $message = 'Resource created successfully',
        public int $createdId = 0,
    ) {
    }
}