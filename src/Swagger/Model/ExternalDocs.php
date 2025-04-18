<?php

namespace PhpApi\Swagger\Model;

class ExternalDocs
{
    public function __construct(public readonly ?string $description, public readonly ?string $url)
    {
    }

    public function toArray(): array
    {
        $result = [];

        if (isset($this->description)) {
            $result['description'] = $this->description;
        }

        if (isset($this->url)) {
            $result['url'] = $this->url;
        }

        return $result;
    }
}
