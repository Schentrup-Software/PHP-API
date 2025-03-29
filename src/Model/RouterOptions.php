<?php

namespace PhpApi\Model;

class RouterOptions
{
    public function __construct(
        public readonly string $namespace,
        public readonly string $directory = 'routes',
        public readonly string $baseUrl = '/',
        public readonly string $method = 'execute',
        public readonly string $suffix = '',
        public readonly string $wordSeparator = '-',
    ) { }
}