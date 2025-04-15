<?php

namespace PhpApiSample\Routes\Path\Subpath;

use PhpApi\Model\Request\AbstractRequest;
use PhpApi\Model\Response\AbstractJsonResponse;

class GetPathSubpath
{
    public function execute(GetRequest $r, int $pathVar2): GetResponse
    {
        $response = new GetResponse(
            message: $r->someMessage,
            pathVar: $pathVar2,
            someVar: $r->someVar,
        );
        $response->setCode(200);
        return $response;
    }
}

class GetRequest extends AbstractRequest
{
    public ?int $someVar;
    public string $someMessage = 'Has a default value';
}

class GetResponse extends AbstractJsonResponse
{
    public const ResponseCode = 200;

    public function __construct(
        public int $pathVar,
        public ?int $someVar = null,
        public string $message = 'Hello World 2',
    ) {
    }
}
