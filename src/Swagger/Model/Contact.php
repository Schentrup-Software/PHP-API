<?php

namespace PhpApi\Swagger\Model;

class Contact
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $url,
    ) {
    }

    public function toArray(): array
    {
        $result = [];

        if (isset($this->name)) {
            $result['name'] = $this->name;
        }

        if (isset($this->email)) {
            $result['email'] = $this->email;
        }

        if (isset($this->url)) {
            $result['url'] = $this->url;
        }

        return $result;
    }
}
