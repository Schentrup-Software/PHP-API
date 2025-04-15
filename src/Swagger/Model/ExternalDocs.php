<?php

namespace PhpApi\Swagger\Model;

class ExternalDocs
{
    public function __construct(public readonly ?string $description, public readonly ?string $url)
    {
    }
}
