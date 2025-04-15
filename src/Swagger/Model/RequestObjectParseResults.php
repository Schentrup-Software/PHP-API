<?php

namespace PhpApi\Swagger\Model;

use PhpApi\Enum\InputParamType;

class RequestObjectParseResults
{
    /**
     * @param array<string, Schema> $queryParams
     * @param array<string, Schema> $inputContent
     */
    public function __construct(
        public readonly array $queryParams,
        public readonly ?InputParamType $inputContentType,
        public readonly array $inputContent,
        public readonly bool $allowsNull,
    ) {
    }
}
