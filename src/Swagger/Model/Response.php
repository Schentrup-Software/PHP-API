<?php

namespace PhpApi\Swagger\Model;

class Response
{
    /**
     * @param (array<string, ResponseContent>|null) $content
     */
    public function __construct(
        public readonly string $description,
        public readonly ?array $content = null,
    ) {
    }
}
