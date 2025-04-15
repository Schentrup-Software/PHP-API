<?php

namespace PhpApi\Swagger\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class SwaggerTag
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
    ) {
    }
}
