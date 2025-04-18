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

    public function toArray(): array
    {
        $result = [
            'name' => $this->name,
        ];

        if (isset($this->description)) {
            $result['description'] = $this->description;
        }

        if (isset($this->externalDocs)) {
            $result['externalDocs'] = $this->externalDocs->toArray();
        }

        return $result;
    }
}
