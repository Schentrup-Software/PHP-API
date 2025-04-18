<?php

namespace PhpApi\Swagger\Model;

use PhpApi\Enum\InputParamType;

class RequestObjectParseResults
{
    /**
     * @param array<string, RequestObjectParam> $params
     * @param array<string, Schema> $inputContent
     */
    public function __construct(
        public readonly array $params,
        public readonly ?InputParamType $inputContentType,
        public readonly array $inputContent,
        public readonly bool $allowsNull,
    ) {
    }
}
