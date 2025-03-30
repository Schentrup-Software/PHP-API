<?php

namespace SchentrupSoftware\PhpApiSample\Enum;

enum HttpMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case PATCH = 'PATCH';
    case OPTIONS = 'OPTIONS';
    case HEAD = 'HEAD';

    public static function getQueryOnlyMethods(): array
    {
        return [
            self::GET->value,
            self::HEAD->value,
        ];
    }
}