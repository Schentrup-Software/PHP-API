<?php

namespace PhpApi\Swagger\Model;

class ResponseContent
{
    public function __construct(
        public Schema $schema,
    ) {
    }

    public function toArray(): array
    {
        return [
            'schema' => $this->schema->toArray(),
        ];
    }
}
