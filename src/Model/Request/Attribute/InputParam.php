<?php

namespace PhpApi\Model\Request\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class InputParam
{
    public function __construct(
        public readonly ?string $name = null,
    ) {
    }
}
