<?php

namespace PhpApi\Model;

class SwaggerOptions
{
    public function __construct(
        public readonly bool $enabled = true,
        public readonly ?string $title = 'Swagger Documentation',
        public readonly ?string $description = null,
        public readonly ?string $termsOfServiceUrl = null,
        public readonly ?string $contactName = null,
        public readonly ?string $contactEmail = null,
        public readonly ?string $contactUrl = null,
        public readonly ?string $licenseName = null,
        public readonly ?string $licenseUrl = null,
        public readonly ?string $apiVersion = null,
        public readonly ?string $externalDocsUrl = null,
        public readonly ?string $externalDocsDescription = null,
    ) {
    }
}
