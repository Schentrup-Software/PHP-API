<?php

namespace PhpApiSample\Routes;

use PhpApi\Model\Response\AbstractJsonResponse;
use PhpApi\Swagger\Attribute\SwaggerDescription;
use PhpApi\Swagger\Attribute\SwaggerSummary;
use PhpApi\Swagger\Attribute\SwaggerTag;

#[SwaggerTag(name: 'Get', description: 'Get example')]
#[SwaggerTag(name: 'Get2', description: 'Get example2')]
class Get
{
    #[SwaggerSummary('Retrieve a subpath summary')]
    #[SwaggerDescription('Retrieve a base path description')]
    public function execute(): GetResponse
    {
        $response = new GetResponse();
        return $response;
    }
}

class GetResponse extends AbstractJsonResponse
{
    public const ResponseCode = 200;

    public function __construct(
        public string $message = 'Operation successful',
        public int $itemCount = 12,
        public ?int $timestamp = null,
    ) {
    }
}
