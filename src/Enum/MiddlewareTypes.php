<?php

namespace PhpApi\Enum;

use AutoRoute\Exception\InvalidArgument;
use AutoRoute\Exception\MethodNotAllowed;
use AutoRoute\Exception\NotFound;

enum MiddlewareTypes: int
{
    // Router errors
    case InvalidArgumentException = 0;
    case NotFoundException = 1;
    case MethodNotAllowedException = 2;
    case RouterServerError = 3;

    // Regular middleware
    case Prerequest = 4;
    case Postrequest = 5;

    public static function fromRouterException(Exception $exception): self
    {
        if ($exception instanceof InvalidArgument) {
            return self::InvalidArgumentException;
        } elseif ($exception instanceof NotFound) {
            return self::NotFoundException;
        } elseif ($exception instanceof MethodNotAllowed) {
            return self::MethodNotAllowedException;
        } else {
            return self::RouterServerError;
        }
    }
}