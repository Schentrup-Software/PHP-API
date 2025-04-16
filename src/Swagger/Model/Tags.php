<?php

namespace PhpApi\Swagger\Model;

class Tags
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?ExternalDocs $externalDocs = null,
    ) {
    }
}
