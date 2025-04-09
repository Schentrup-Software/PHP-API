<?php

namespace PhpApi\Swagger;

use AutoRoute\AutoRoute;
use InvalidArgumentException;
use PhpApi\Enum\InputParamType;
use PhpApi\Model\Request\AbstractRequest;
use PhpApi\Model\Request\RequestProperty;
use PhpApi\Model\RouterOptions;
use PhpApi\Swagger\Model\ContentType;
use PhpApi\Swagger\Model\Parameter;
use PhpApi\Swagger\Model\RequestBody;
use PhpApi\Swagger\Model\Schema;
use PhpApi\Utility\Arrays;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionUnionType;

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

                $pathVariables = [];
                preg_match_all('/\{([^}]+)\}/', $path, $pathVariables);

                $parameters = [];
                foreach (($pathVariables[1] ?? []) as $typeVariable) {
                    $parsedTypeVariable = explode(':', $typeVariable);
                    $parameters[] = new Parameter(
                        name: $parsedTypeVariable[1],
                        in: 'path',
                        required: true,
                        schema: [
                            new Schema(
                                type: $parsedTypeVariable[0],
                            ),
                        ]
                    );
                }

                $requestObject = Arrays::getFirstElement($reflectionMethod->getParameters())?->getType();
                if ($requestObject instanceof ReflectionNamedType || $requestObject instanceof ReflectionUnionType) {
                    $requestBody = $this->getRequestFromClass($requestObject, $method);
                } else {
                    $requestBody = null;
                }


                echo $method . ' ' . $path . '<br>';
                echo 'Class: ' . $class . '<br>';
                echo 'Method: ' . $reflectionMethod->getName() . '<br>';
            }
        }
        return $swagger;
    }

    private function parseReturnType(ReflectionNamedType|ReflectionUnionType $reflectionType, string $method): RequestBody
    {
        if ($reflectionType instanceof ReflectionUnionType) {
            $types = [];
            foreach ($reflectionType->getTypes() as $type) {
                if ($type instanceof ReflectionNamedType) {
                    $types[] = $this->getSchemaFromClass($type, $method);
                }
            }
            return new RequestBody(
                required: true,
                content: [
                    'application/json' => new ContentType(
                        schema: new Schema(
                            oneOf: $types,
                        )
                    ),
                ]
            );
        } else {
            return new RequestBody(
                required: true,
                content: [
                    'application/json' => new ContentType(
                        schema: $this->getSchemaFromClass(
                            $reflectionType,
                            $method
                        )
                    ),
                ]
            );
        }
    }

    private function parseNamedReturnType(ReflectionNamedType $reflectionType, string $method)
    {
        $bodyContent = [];
        $queryContent = [];

        $className = $reflectionType->getName();
        $reflectionClass = new ReflectionClass($className);

        if (!$reflectionClass->isSubclassOf(AbstractRequest::class)) {
            throw new InvalidArgumentException("Controller argument of type $className is not a subclass of " . AbstractRequest::class);
        }

        $className = $reflectionClass->getName();
        /** @var RequestProperty[] $paramTypes */
        $paramTypes = $className::getParamTypes($method);

        foreach ($paramTypes as $paramType) {
            $propertyType = $reflectionClass->getProperty($paramType->propertyName)->getType();

            if (!($propertyType instanceof ReflectionNamedType)) {
                throw new InvalidArgumentException("Property type must be a named type. Cannot be null or union type");
            }

            // We need to throw some type of error if you mix json and input
            // We also need to get if its json on input up to the parseReturnType function
            if ($paramType->type === InputParamType::Query) {
                $queryContent[$paramType->name] = $this->getSchemaFromClass(
                    $propertyType,
                    $method
                );
            } elseif ($paramType->type === InputParamType::Json) {
                $bodyContent[$paramType->name] = $this->getSchemaFromClass(
                    $propertyType,
                    $method
                );
            } elseif ($paramType->type === InputParamType::Input) {
                $bodyContent[$paramType->name] = $this->getSchemaFromClass(
                    $propertyType,
                    $method
                );
            }
        }
    }

    private function getSchemaFromClass(ReflectionNamedType $reflectionType): Schema
    {
        if ($reflectionType->isBuiltin()) {
            return new Schema(
                type: $reflectionType->getName(),
            );
        }

        return new Schema(
            type: 'object',
            properties: $properties,
        );
    }
}
