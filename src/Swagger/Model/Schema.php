<?php

namespace PhpApi\Swagger\Model;

class Schema
{
    /**
     * @param (string[]|null) $required
     * @param (array<string, Schema>|null) $properties
     * @param (array<Schema>|null) $oneOf
     */
    public function __construct(
        public readonly ?string $type = null,
        public readonly ?array $required = null,
        public readonly ?array $properties = null,
        public readonly ?array $oneOf = null,
    ) {
    }

    public function toArray(): array
    {
        $result = [];

        if (isset($this->type)) {
            $result['type'] = $this->type;
        }

        if (!empty($this->required)) {
            $result['required'] = $this->required;
        }

        if (!empty($this->properties)) {
            $result['properties'] = array_map(
                fn ($property) => $property->toArray(),
                $this->properties
            );
        }

        if (!empty($this->oneOf)) {
            $result['oneOf'] = array_map(
                fn ($oneOf) => $oneOf->toArray(),
                $this->oneOf
            );
        }

        return $result;
    }
}
