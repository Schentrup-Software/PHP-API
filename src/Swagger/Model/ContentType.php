<?php

namespace PhpApi\Swagger\Model;

class ContentType
{
    public function __construct(
        public readonly Schema $schema,
    ) {
    }

    public function toArray(): array
    {
        return [
            'schema' => $this->schema->toArray(),
        ];
    }
}
