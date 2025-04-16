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
}
