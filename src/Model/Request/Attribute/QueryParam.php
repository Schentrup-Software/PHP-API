<?php

namespace SchentrupSoftware\PhpApiSample\Model\Request\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class QueryParam
{
    public function __construct(
        public readonly ?string $name = null,
    ) {
    }
}