<?php

namespace PhpApi\Swagger\Model;

class ContentType
{
    /**
     * @param array<string, string> $schema
     */
    public function __construct(
        public readonly Schema $schema,
        public readonly mixed $example = null,
    ) {
    }
}
