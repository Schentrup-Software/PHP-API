<?php

namespace PhpApi\Swagger\Model;

class License
{
    public function __construct(public readonly ?string $name, public readonly ?string $url)
    {
    }

    public function toArray(): array
    {
        $result = [];

        if (isset($this->name)) {
            $result['name'] = $this->name;
        }

        if (isset($this->url)) {
            $result['url'] = $this->url;
        }

        return $result;
    }
}
