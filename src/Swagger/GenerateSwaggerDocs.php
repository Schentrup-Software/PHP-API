<?php

namespace PhpApi\Swagger;

use AutoRoute\AutoRoute;
use PhpApi\Model\RouterOptions;
use ReflectionClass;
use ReflectionException;

class GenerateSwaggerDocs
{
    public function __construct(
        private readonly AutoRoute $autoRoute,
        private readonly RouterOptions $routerOptions,
    ) {
    }

    public function generate(): string
    {
        $urls = $this->autoRoute->getDumper()->dump();
        $swaggerDocs = $this->generateSwagger($urls);
        return json_encode($swaggerDocs);
    }

    /**
     * @param array<string, array<string, string>> $urls url[path][httpMethod] = class
     * @return array
     */
    private function generateSwagger(array $urls): array
    {
        $swagger = [];
        foreach ($urls as $path => $methods) {
            foreach ($methods as $method => $class) {
                $reflectionClass = new ReflectionClass($class);
                try {
                    $reflectionMethod = $reflectionClass->getMethod($this->routerOptions->method);
                } catch (ReflectionException $e) {
                    continue;
                }

                echo $method . ' ' . $path . '<br>';
                echo 'Class: ' . $class . '<br>';
                echo 'Method: ' . $reflectionMethod->getName() . '<br>';
            }
        }
        return $swagger;
    }
}
