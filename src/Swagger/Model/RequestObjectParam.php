<?php

namespace PhpApi\Swagger\Model;

use PhpApi\Enum\InputParamType;

class RequestObjectParam
{
    public function __construct(
        public readonly Schema $schema,
        public readonly bool $allowsNull,
        public readonly ?string $description,
        public readonly InputParamType $paramType,
    ) {
    }
}
