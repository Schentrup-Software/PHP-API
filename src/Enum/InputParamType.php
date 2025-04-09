<?php

namespace PhpApi\Enum;

use PhpApi\Model\Request\Attribute\InputParam;
use PhpApi\Model\Request\Attribute\JsonRequestParam;
use PhpApi\Model\Request\Attribute\QueryParam;

enum InputParamType: int
{
    case Query = 1;
    case Input = 2;
    case Json = 3;

    public static function fromClassInstance(mixed $instance): ?self
    {
        if ($instance instanceof QueryParam) {
            return self::Query;
        } elseif ($instance instanceof InputParam) {
            return self::Input;
        } elseif ($instance instanceof JsonRequestParam) {
            return self::Json;
        }

        return null;
    }
}
