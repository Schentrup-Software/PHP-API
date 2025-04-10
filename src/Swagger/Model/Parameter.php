<?php

namespace PhpApi\Swagger\Model;

class Parameter
{
    /**
     * @param array<string, ContentType> $content
     */
    public function __construct(
        public readonly string $name,
        public readonly string $in,
        public readonly bool $required,
        public readonly Schema $schema,
        public readonly ?string $description = null,
        public readonly array $content = [],
    ) {
    }
}
