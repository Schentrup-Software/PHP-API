<?php

namespace PhpApi\Model\Request;

use PhpApi\Enum\InputParamType;

class RequestProperty
{
    public function __construct(
        public readonly string $name,
        public readonly string $propertyName,
        public readonly InputParamType $type
    ) {
    }
}
