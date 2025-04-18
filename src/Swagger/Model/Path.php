<?php

namespace PhpApi\Swagger\Model;

class Path
{
    /**
     *
     * @param string[] $tags
     * @param Parameter[] $parameters
     * @param (null|array<int, Response>) $responses
     */
    public function __construct(
        public array $tags,
        public string $summary,
        public string $description,
        public string $operationId,
        public array $parameters,
        public ?RequestBody $requestBody,
        public ?array $responses,
    ) {
    }

    public function toArray(): array
    {
        $result = [
            'tags' => $this->tags,
            'summary' => $this->summary,
            'description' => $this->description,
            'operationId' => $this->operationId,
            'parameters' => array_map(
                fn ($parameter) => $parameter->toArray(),
                $this->parameters
            ),
        ];

        if (isset($this->requestBody)) {
            $result['requestBody'] = $this->requestBody->toArray();
        }

        if (isset($this->responses)) {
            $result['responses'] = [];
            foreach ($this->responses as $response) {
                $result['responses'][] = $response->toArray();
            }
        }

        return $result;
    }
}
