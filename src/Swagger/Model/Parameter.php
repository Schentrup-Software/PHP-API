<?php

namespace PhpApi\Swagger\Model;

class Parameter
{
    /**
     * @param string $name
     * @param string $in
     * @param bool $required
     * @param string $description
     * @param bool $deprecated
     * @param bool $allowEmptyValue
     * @param array<string, Schema> $schema
     */
    public function __construct(
        public readonly string $name,
        public readonly string $in,
        public readonly bool $required,
        public readonly array $schema,
        public readonly ?string $description = null,
    ) {
    }
}
