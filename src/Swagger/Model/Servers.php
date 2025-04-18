<?php

namespace PhpApi\Swagger\Model;

class Servers
{
    public function __construct(
        public readonly string $url,
        public readonly string $description,
    ) {
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'description' => $this->description,
        ];
    }
}
