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
    public ?int $someVar; // TODO: This does not work becuase property is not intialized
    public string $someMessage = 'Has a default value';
}

class GetResponse extends AbstractJsonResponse
{
    public const ResponseCode = 200;

    // TODO: This is kinda weird that we have the constructor version of properties for responses
    // But that does not work for requests. We need to work that out.
    public function __construct(
        public int $pathVar,
        public ?int $someVar = null,
        public string $message = 'Hello World 2',
    ) {
    }
}
