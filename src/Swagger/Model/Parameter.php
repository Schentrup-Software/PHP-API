<?php

namespace PhpApi\Swagger\Model;

class Parameter
{
    /**
     * @param array<string, ContentType> $content
     */
    public function __construct(
        public readonly string $name,
        public readonly string $in,
        public readonly bool $required,
        public readonly Schema $schema,
        public readonly ?string $description = null,
        public readonly array $content = [],
    ) {
    }

    public function toArray(): array
    {
        $result = [
            'name' => $this->name,
            'in' => $this->in,
            'required' => $this->required,
            'schema' => $this->schema->toArray(),
        ];

        if (isset($this->description)) {
            $result['description'] = $this->description;
        }

        if (!empty($this->content)) {
            $result['content'] = array_map(
                fn ($contentType) => $contentType->toArray(),
                $this->content
            );
        }

        return $result;
    }
}
