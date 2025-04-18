<?php

namespace PhpApi\Swagger\Model;

class RequestBody
{
    /**
     * @param array<string, ContentType> $content
     */
    public function __construct(
        public bool $required,
        public array $content,
        public ?string $description = null,
    ) {
    }

    public function toArray(): array
    {
        $result = [
            'required' => $this->required,
            'content' => array_map(
                fn ($contentType) => $contentType->toArray(),
                $this->content
            ),
        ];

        if (isset($this->description)) {
            $result['description'] = $this->description;
        }

        return $result;
    }
}
