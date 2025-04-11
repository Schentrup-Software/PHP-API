<?php

namespace PhpApi\Swagger\Model;

class Path
{
    /**
     *
     * @param string[] $tags
     * @param Parameter[] $parameters
     * @param (null|array<int, Response>) $responses
     */
    public function __construct(
        public array $tags,
        public string $summary,
        public string $description,
        public string $operationId,
        public array $parameters,
        public ?RequestBody $requestBody,
        public ?array $responses,
    ) {
    }
}
