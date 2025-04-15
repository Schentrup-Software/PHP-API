<?php

namespace PhpApi\Enum;

enum ContentType: string
{
    case Json = 'application/json';
    case Xml = 'application/xml';
    case Html = 'text/html';
    case Input = 'application/x-www-form-urlencoded';
}
