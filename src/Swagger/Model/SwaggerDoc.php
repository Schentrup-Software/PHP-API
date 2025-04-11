<?php

namespace PhpApi\Swagger\Model;

class SwaggerDoc
{
    /**
     * @param string $openapi OpenAPI version
     * @param (null|Servers[]) $servers
     * @param (null|Tags[]) $tags
     * @param array<string, array<string, Path>> $paths $path[path][httpMethod] = Path
     */
    public function __construct(
        public readonly string $openapi,
        public readonly Info $info,
        public readonly ?ExternalDocs $externalDocs,
        public readonly ?array $servers,
        public readonly ?array $tags,
        public readonly array $paths,
    ) {
    }
}
