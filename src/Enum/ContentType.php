<?php

namespace PhpApi\Enum;

enum ContentType: string
{
    case JSON = 'application/json';
    case XML = 'application/xml';
    case HTML = 'text/html';
    case INPUT = 'application/x-www-form-urlencoded';
}
