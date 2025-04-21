<?php

namespace PhpApiSample\Routes;

use PhpApi\Model\Request\AbstractRequest;
use PhpApi\Model\Response\AbstractJsonResponse;
use PhpApi\Model\Request\Attribute\JsonRequestParam;
use PhpApi\Swagger\Attribute\SwaggerDescription;
use PhpApi\Swagger\Attribute\SwaggerSummary;
use PhpApi\Swagger\Attribute\SwaggerTag;

#[SwaggerTag(name: 'Update', description: 'Partial update resources')]
class Patch
{
    #[SwaggerSummary('Update a resource partially')]
    #[SwaggerDescription('Uses PATCH to update only specified fields of a resource')]
    public function execute(PatchRequest $request): PatchResponse|PatchErrorResponse
    {
        // Validate data
        if (isset($request->name) && strlen($request->name) < 3) {
            return new PatchErrorResponse('Name must be at least 3 characters');
        }

        // Process update
        return new PatchResponse(
            id: $request->id,
            updatedFields: array_keys(get_object_vars($request))
        );
    }
}

class PatchRequest extends AbstractRequest
{
    public function __construct(
        #[JsonRequestParam]
        public int $id,
        #[JsonRequestParam]
        public ?string $name = null,
        #[JsonRequestParam]
        public ?string $email = null,
        #[JsonRequestParam]
        public ?int $status = null
    ) {
    }
}

class PatchResponse extends AbstractJsonResponse
{
    public const ResponseCode = 200;

    public function __construct(
        public int $id,
        public array $updatedFields = [],
        public string $message = 'Resource updated successfully',
        public int $timestamp = 0
    ) {
        $this->timestamp = time();
    }
}

class PatchErrorResponse extends AbstractJsonResponse
{
    public const ResponseCode = 400;

    public function __construct(
        public string $error,
        public string $errorCode = 'VALIDATION_ERROR'
    ) {
    }
}
