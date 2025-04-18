<?php

namespace PhpApi\Model\Request\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS)]
class HeaderRequestParam
{
    public function __construct(
        public readonly ?string $name = null,
    ) {
    }
}
