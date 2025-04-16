<?php

namespace PhpApi\Swagger\Model;

use PhpApi\Swagger\Attribute\SwaggerTag;

class ItemMetadata
{
    /**
     * @param array<string, SwaggerTag> $tags
     */
    public function __construct(
        public readonly ?string $summary,
        public readonly ?string $description,
        public readonly array $tags,
    ) {
    }
}
