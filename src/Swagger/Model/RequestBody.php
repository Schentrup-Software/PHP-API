<?php

namespace PhpApi\Swagger\Model;

class RequestBody
{
    /**
     * @param array<string, ContentType> $content
     */
    public function __construct(
        public string $description,
        public bool $required,
        public array $content,
    ) {
    }
}
