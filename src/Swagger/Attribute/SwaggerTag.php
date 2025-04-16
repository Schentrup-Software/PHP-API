<?php

namespace PhpApi\Swagger\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class SwaggerTag
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
    ) {
    }
}
