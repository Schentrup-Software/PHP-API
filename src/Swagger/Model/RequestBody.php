<?php

namespace PhpApi\Swagger\Model;

class RequestBody
{
    /**
     * @param array<string, ContentType> $content
     */
    public function __construct(
        public bool $required,
        public array $content,
        public ?string $description = null,
    ) {
    }
}
