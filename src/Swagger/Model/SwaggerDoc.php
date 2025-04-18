<?php

namespace PhpApi\Swagger\Model;

class SwaggerDoc
{
    /**
     * @param string $openapi OpenAPI version
     * @param Tags[] $tags
     * @param array<string, array<string, Path>> $paths $path[path][httpMethod] = Path
     * @param (null|Servers[]) $servers
     */
    public function __construct(
        public readonly string $openapi,
        public readonly Info $info,
        public readonly ?ExternalDocs $externalDocs,
        public readonly array $tags,
        public readonly array $paths,
        public readonly ?array $servers = null,
    ) {
    }

    public function toArray(): array
    {
        $result = [
            'openapi' => $this->openapi,
            'info' => $this->info->toArray(),
            'tags' => $this->tags,
            'paths' => array_map(
                fn ($path) => array_map(
                    fn ($method) => $method->toArray(),
                    $path
                ),
                $this->paths
            ),
        ];

        if (isset($this->externalDocs)) {
            $result['externalDocs'] = $this->externalDocs->toArray();
        }

        if (isset($this->servers)) {
            $result['servers'] = array_map(
                fn ($server) => $server->toArray(),
                $this->servers
            );
        }

        return $result;
    }
}
