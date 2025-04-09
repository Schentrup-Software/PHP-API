<?php

namespace PhpApi\Swagger\Model;

class Schema
{
    /**
     * @param string $type
     * @param (array<string, Schema>|null) $properties
     * @param (string[]|null) $required
     * @param (array<Schema>|null) $oneOf
     */
    public function __construct(
        public readonly ?string $type = null,
        public readonly ?array $required = null,
        public readonly ?array $properties = null,
        public readonly ?array $oneOf = null,
    ) {
    }
}
