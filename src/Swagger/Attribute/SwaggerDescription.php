<?php

namespace PhpApi\Swagger\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_ALL)]
class SwaggerDescription
{
    public function __construct(
        public readonly string $description,
    ) {
    }
}
