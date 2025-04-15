<?php

namespace PhpApi\Swagger\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class SwaggerSummary
{
    public function __construct(
        public readonly string $summary,
    ) {
    }
}
