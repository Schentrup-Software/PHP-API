<?php

namespace PhpApi\Swagger\Model;

class Response
{
    /**
     * @param array<string, ResponseContent> $content
     */
    public function __construct(
        public string $description,
        public array $content,
    ) {
    }
}
