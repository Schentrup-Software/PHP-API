<?php

namespace PhpApi\Swagger\Model;

class Info
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?string $termsOfService,
        public readonly ?Contact $contact,
        public readonly ?License $license,
        public readonly ?string $version
    ) {
    }

    public function toArray(): array
    {
        $result = [];

        if (isset($this->title)) {
            $result['title'] = $this->title;
        }

        if (isset($this->description)) {
            $result['description'] = $this->description;
        }

        if (isset($this->termsOfService)) {
            $result['termsOfService'] = $this->termsOfService;
        }

        if (isset($this->contact)) {
            $result['contact'] = $this->contact->toArray();
        }

        if (isset($this->license)) {
            $result['license'] = $this->license->toArray();
        }

        if (isset($this->version)) {
            $result['version'] = $this->version;
        }

        return $result;
    }
}
