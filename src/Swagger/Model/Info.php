<?php

namespace PhpApi\Swagger\Model;

class Info
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $termsOfService,
        public readonly Contact $contact,
        public readonly License $license,
        public readonly string $version
    ) {
    }
}
