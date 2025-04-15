<?php

namespace PhpApiSample\Routes\Path\Subpath;

use PhpApi\Model\Request\AbstractRequest;
use PhpApi\Model\Response\AbstractJsonResponse;
use PhpApi\Swagger\Attribute\SwaggerDescription;
use PhpApi\Swagger\Attribute\SwaggerSummary;

#[SwaggerSummary('Get a subpath summary')]
#[SwaggerDescription('Get a subpath description')]
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
    #[SwaggerDescription('someVar description')]
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
