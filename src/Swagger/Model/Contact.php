<?php

namespace PhpApi\Swagger\Model;

class Contact
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $url,
    ) {
    }
}
