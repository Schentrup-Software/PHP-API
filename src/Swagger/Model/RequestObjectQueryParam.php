<?php

namespace PhpApi\Swagger\Model;

class RequestObjectQueryParam
{
    public function __construct(
        public readonly Schema $schema,
        public readonly bool $allowsNull,
        public readonly string $description,
    ) {
    }
}
