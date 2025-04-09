<?php

namespace PhpApi\Swagger\Model;

class Path
{
    /**
     *
     * @param string[] $tags
     * @param Parameter[] $parameters
     * @param RequestBody $requestBody
     * @param array<int, Response> $responses
     * @return void
     */
    public function __construct(
        public array $tags,
        public string $summary,
        public string $description,
        public string $operationId,
        public array $parameters,
        public RequestBody $requestBody,
        public array $responses,
    ) {
    }
}
