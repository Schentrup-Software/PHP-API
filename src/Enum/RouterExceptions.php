<?php

namespace PhpApi\Enum;

use AutoRoute\Exception\Exception;
use AutoRoute\Exception\InvalidArgument;
use AutoRoute\Exception\MethodNotAllowed;
use AutoRoute\Exception\NotFound;

enum RouterExceptions: int
{
    case InvalidArgumentException = 0;
    case NotFoundException = 1;
    case MethodNotAllowedException = 2;
    case RouterServerError = 3;

    public static function fromRouterException(string $exception): self
    {
        if ($exception === InvalidArgument::class) {
            return self::InvalidArgumentException;
        } elseif ($exception === NotFound::class) {
            return self::NotFoundException;
        } elseif ($exception === MethodNotAllowed::class) {
            return self::MethodNotAllowedException;
        } else {
            return self::RouterServerError;
        }
    }
}