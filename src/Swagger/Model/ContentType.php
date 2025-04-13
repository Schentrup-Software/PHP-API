<?php

namespace PhpApi\Swagger\Model;

class ContentType
{
    public function __construct(
        public readonly Schema $schema,
        public readonly mixed $example = null,
    ) {
    }
}
