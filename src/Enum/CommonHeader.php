<?php

namespace PhpApi\Enum;

enum CommonHeader: string
{
    case CONTENT_TYPE = 'Content-Type';
    case ACCEPT = 'Accept';
    case AUTHORIZATION = 'Authorization';
}
