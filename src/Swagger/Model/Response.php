<?php

namespace PhpApi\Swagger\Model;

class Response
{
    /**
     * @param (array<string, ResponseContent>|null) $content
     */
    public function __construct(
        public readonly string $description,
        public readonly ?array $content = null,
    ) {
    }

    public function toArray(): array
    {
        $result = [
            'description' => $this->description,
        ];

        if (isset($this->content)) {
            $result['content'] = array_map(
                fn ($contentType) => $contentType->toArray(),
                $this->content
            );
        }

        return $result;
    }
}
