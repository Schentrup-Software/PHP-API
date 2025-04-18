<?php

namespace PhpApi\Enum;

use InvalidArgumentException;
use PhpApi\Model\Request\Attribute\CookieRequestParam;
use PhpApi\Model\Request\Attribute\HeaderRequestParam;
use PhpApi\Model\Request\Attribute\InputParam;
use PhpApi\Model\Request\Attribute\JsonRequestParam;
use PhpApi\Model\Request\Attribute\QueryParam;

enum InputParamType: int
{
    case Query = 1;
    case Input = 2;
    case Json = 3;
    case Header = 4;
    case Cookie = 5;

    public static function fromClassInstance(mixed $instance): ?self
    {
        if ($instance instanceof QueryParam) {
            return self::Query;
        } elseif ($instance instanceof InputParam) {
            return self::Input;
        } elseif ($instance instanceof JsonRequestParam) {
            return self::Json;
        } elseif ($instance instanceof HeaderRequestParam) {
            return self::Header;
        } elseif ($instance instanceof CookieRequestParam) {
            return self::Cookie;
        }

        return null;
    }

    public function toContentType(): string
    {
        return match ($this) {
            self::Input => 'application/x-www-form-urlencoded',
            self::Json => 'application/json',
            default => throw new InvalidArgumentException('Invalid content type'),
        };
    }

    public function toParamType(): string
    {
        return match ($this) {
            self::Query => 'query',
            self::Header => 'header',
            self::Cookie => 'cookie',
            default => throw new InvalidArgumentException('Invalid parameter type'),
        };
    }
}
