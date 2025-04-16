<?php

namespace PhpApi\Model\Request;

use PhpApi\Enum\InputParamType;
use ReflectionNamedType;

class RequestProperty
{
    /**
     * @param RequestProperty[] $subProperties
     */
    public function __construct(
        public readonly string $name,
        public readonly string $propertyName,
        public readonly InputParamType $type,
        public readonly bool $hasDefaultValue,
        public readonly mixed $defaultValue,
        public readonly array $subProperties,
        public readonly ReflectionNamedType $reflectionType,
    ) {
    }
}
