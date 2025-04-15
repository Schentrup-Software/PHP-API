<?php

namespace PhpApiSample\Routes\Path\Subpath;

use PhpApi\Model\Request\AbstractRequest;
use PhpApi\Model\Response\AbstractJsonResponse;

class GetPathSubpath
{
    public function execute(GetRequest $_, int $pathVar2): GetResponse
    {
        $response = new GetResponse(
            message: 'Hello World 2',
            pathVar: $pathVar2,
        );
        $response->setCode(200);
        return $response;
    }
}

class GetRequest extends AbstractRequest
{
    public function __construct(
        public ?int $someVar,
        public ?string $someMessage,
    ) {
    }
}

class GetResponse extends AbstractJsonResponse
{
    public const ResponseCode = 200;

    public function __construct(
        public int $pathVar,
        public string $message = 'Hello World 2',
    ) {
    }
}
