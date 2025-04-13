<?php

namespace PhpApi\Swagger;

use AutoRoute\AutoRoute;
use InvalidArgumentException;
use PhpApi\Enum\ContentType as EnumContentType;
use PhpApi\Enum\InputParamType;
use PhpApi\Model\Request\RequestParser;
use PhpApi\Model\Response\AbstractResponse;
use PhpApi\Model\Response\ResponseParser;
use PhpApi\Model\RouterOptions;
use PhpApi\Swagger\Model\ContentType;
use PhpApi\Swagger\Model\ExternalDocs;
use PhpApi\Swagger\Model\Info;
use PhpApi\Swagger\Model\Parameter;
use PhpApi\Swagger\Model\Path;
use PhpApi\Swagger\Model\RequestBody;
use PhpApi\Swagger\Model\RequestObjectParseResults;
use PhpApi\Swagger\Model\Response;
use PhpApi\Swagger\Model\ResponseContent;
use PhpApi\Swagger\Model\Schema;
use PhpApi\Swagger\Model\Servers;
use PhpApi\Swagger\Model\SwaggerDoc;
use PhpApi\Utility\Arrays;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
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

        $toArray = function ($swaggerDoc) use (&$toArray) {
            return array_map(
                fn ($p) => is_object($p)
                    ? $toArray($p)
                    : (is_array($p)
                        ? array_map($toArray, $p)
                        : $p),
                (array) $swaggerDoc
            );
        };

        $swaggerDocArray = $toArray($swaggerDocs);

        $withoutNull = function ($a) use (&$withoutNull) {
            return array_filter(
                array_map(
                    fn ($p) => is_array($p) ? $withoutNull($p) : $p,
                    $a
                ),
                fn ($p) => !empty($p)
            );
        };
        $swaggerDocArray = $withoutNull($swaggerDocArray);

        $jsonResult = json_encode($swaggerDocArray);
        if ($jsonResult === false) {
            throw new InvalidArgumentException('Failed to encode JSON: ' . json_last_error_msg());
        }

        return $jsonResult;
    }

    /**
     * @param array<string, array<string, string>> $urls url[path][httpMethod] = class
     */
    private function generateSwagger(array $urls): SwaggerDoc
    {
        $paths = [];
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
                $cleanPath = $path;

                /** @var Parameter[] $parameters */
                $parameters = [];
                foreach (($pathVariables[1] ?? []) as $typeVariable) {
                    $parsedTypeVariable = explode(':', $typeVariable);
                    $parameters[] = new Parameter(
                        name: $parsedTypeVariable[1],
                        in: 'path',
                        required: true,
                        schema: new Schema(
                            type: $this->basicPhpTypeToSwaggerType($parsedTypeVariable[0]),
                        ),
                    );
                    $cleanPath = str_replace($parsedTypeVariable[0] . ":", "", $cleanPath);
                }

                $requestObject = Arrays::getFirstElement($reflectionMethod->getParameters())?->getType();
                if ($requestObject instanceof ReflectionNamedType || $requestObject instanceof ReflectionUnionType) {
                    $requestBody = $this->parseRequestType($requestObject, $method, $parameters);
                } elseif ($requestObject instanceof ReflectionIntersectionType) {
                    throw new InvalidArgumentException("Intersection types are not supported");
                }

                $returnType = $reflectionMethod->getReturnType();
                if ($returnType instanceof ReflectionNamedType || $returnType instanceof ReflectionUnionType) {
                    $responses = $this->parseReturnType($returnType);
                } elseif ($returnType instanceof ReflectionIntersectionType) {
                    throw new InvalidArgumentException("Intersection types are not supported");
                }

                $description = $reflectionMethod->getDocComment();
                if ($description == false) {
                    $description = $reflectionMethod->getName();
                }

                $paths[$cleanPath][strtolower($method)] = new Path(
                    tags: [],
                    summary: $description,
                    description: $description,
                    operationId: $method . '_' . $reflectionClass->getName(),
                    parameters: $parameters,
                    requestBody: $requestBody ?? null,
                    responses: $responses ?? null,
                );
            }
        }
        return new SwaggerDoc(
            openapi: '3.1.0',
            info: new Info(
                title: 'API Documentation',
                description: 'API Documentation',
                termsOfService: 'https://example.com/terms',
                contact: null,
                license: null,
                version: '1.0.0',
            ),
            externalDocs: new ExternalDocs(
                url: 'https://example.com',
                description: 'External Docs',
            ),
            servers: [],
            tags: [],
            paths: $paths,
        );
    }

    /**
     * @param array &$parameters
     */
    private function parseRequestType(ReflectionNamedType|ReflectionUnionType $reflectionType, string $method, array &$parameters): RequestBody
    {
        if ($reflectionType instanceof ReflectionUnionType) {
            $parsedTypeData = [];
            foreach ($reflectionType->getTypes() as $type) {
                if ($type instanceof ReflectionNamedType) {
                    $parsedType = $this->parseNamedRequestType($type, $method);
                    $parsedTypeData[] = $parsedType;

                    foreach ($parsedType->queryParams as $name => $schema) {
                        $parameters[] = new Parameter(
                            name: $name,
                            in: 'query',
                            required: true,
                            schema: $schema,
                        );
                    }
                }
            }

            /** @var array<string, RequestObjectParseResults[]> $groupedData */
            $groupedData = Arrays::groupBy($parsedTypeData, fn (RequestObjectParseResults $type) => $type->inputContentType?->toContentType());
            $content = array_map(
                fn (RequestObjectParseResults $type) => new ContentType(
                    schema: new Schema(
                        type: 'object',
                        properties: $type->inputContent,
                    )
                ),
                $groupedData
            );

            return new RequestBody(
                required: true,
                content: $content
            );
        } else {
            $requestTypeData = $this->parseNamedRequestType($reflectionType, $method);
            $content = !empty($requestTypeData->inputContentType) ? [
                $requestTypeData->inputContentType?->toContentType() => new ContentType(
                    schema: new Schema(
                        type: 'object',
                        properties: $requestTypeData->inputContent,
                    )
                ),
            ] : [];

            return new RequestBody(
                required: true,
                content: $content,
            );
        }
    }

    private function parseNamedRequestType(ReflectionNamedType $reflectionType, string $method): RequestObjectParseResults
    {
        $queryContent = [];
        $inputContentType = null;
        $inputContent = [];

        $className = $reflectionType->getName();
        $reflectionClass = new ReflectionClass($className);

        $className = $reflectionClass->getName();
        $paramTypes = RequestParser::getParamTypes($className, $method);

        foreach ($paramTypes as $paramType) {
            $propertyType = $reflectionClass->getProperty($paramType->propertyName)->getType();

            if (!($propertyType instanceof ReflectionNamedType)) {
                throw new InvalidArgumentException("Property type must be a named type. Cannot be null or union type");
            }

            if ($paramType->type === InputParamType::Query) {
                $queryContent[$paramType->name] = $this->getSchemaFromClass($propertyType);
            } elseif ($paramType->type === InputParamType::Json) {
                if ($inputContentType === null) {
                    $inputContentType = InputParamType::Json;
                } else {
                    throw new InvalidArgumentException("Cannot have both json and input params in the same request");
                }

                $inputContent[$paramType->name] = $this->getSchemaFromClass($propertyType);
            } elseif ($paramType->type === InputParamType::Input) {
                if ($inputContentType === null) {
                    $inputContentType = InputParamType::Input;
                } else {
                    throw new InvalidArgumentException("Cannot have both json and input params in the same request");
                }

                $inputContent[$paramType->name] = $this->getSchemaFromClass($propertyType);
            }
        }

        return new RequestObjectParseResults(
            queryParams: $queryContent,
            inputContentType: $inputContentType,
            inputContent: $inputContent,
        );
    }

    /**
     * @return array<int, Response>
     */
    private function parseReturnType(ReflectionNamedType|ReflectionUnionType $reflectionType): array
    {
        /** @var array<int, Response> $parsedTypeData */
        $parsedTypeData = [];

        if ($reflectionType instanceof ReflectionUnionType) {
            foreach ($reflectionType->getTypes() as $type) {
                if ($type instanceof ReflectionNamedType) {
                    $parsedTypes = $this->parseReturnType($type);
                    foreach ($parsedTypes as $responseCode => $parsedType) {
                        if (!isset($parsedTypeData[$responseCode])) {
                            $parsedTypeData[$responseCode] = $parsedType;
                        } else {
                            $parsedTypeData[$responseCode] = new Response(
                                description: $parsedType->description,
                                content: array_merge(
                                    $parsedTypeData[$responseCode]->content,
                                    $parsedType->content
                                )
                            );
                        }
                    }
                }
            }
        } else {
            $relectionClass = new ReflectionClass($reflectionType->getName());

            if (!$relectionClass->isSubclassOf(AbstractResponse::class)) {
                throw new InvalidArgumentException("Return type must be a subclass of AbstractResponse");
            }

            $responseCode = $relectionClass->getConstant('ResponseCode');
            if (!is_int($responseCode)) {
                throw new InvalidArgumentException("Response code must be an integer");
            }

            /** @var EnumContentType $contentType */
            $contentType = $relectionClass->getConstant('ContentType');
            if (!in_array($contentType, EnumContentType::cases(), true)) {
                throw new InvalidArgumentException("Content type must be an instance of ContentType");
            }

            $parsedTypeData[$responseCode] = new Response(
                description: $relectionClass->getName(),
                content: [
                    $contentType->value => new ResponseContent(
                        $this->getSchemaFromClass($reflectionType)
                    ),
                ]
            );
        }

        return $parsedTypeData;
    }

    private function getSchemaFromClass(ReflectionNamedType $reflectionType): Schema
    {
        if ($reflectionType->isBuiltin()) {
            return new Schema(
                type: $this->basicPhpTypeToSwaggerType($reflectionType->getName()),
            );
        }

        $className = $reflectionType->getName();
        $reflectionClass = new ReflectionClass($className);

        if ($reflectionClass->isSubclassOf(AbstractResponse::class)) {
            $reflectionProperties = ResponseParser::getResponseProperties($className);
        } else {
            $reflectionProperties = $reflectionClass->getProperties();
        }

        $properties = [];
        foreach ($reflectionProperties as $reflectionProperty) {
            $propertyType = $reflectionProperty->getType();
            if (!($propertyType instanceof ReflectionNamedType)) {
                throw new InvalidArgumentException("Property type must be a named type. Cannot be null or union type");
            }

            $properties[$reflectionProperty->getName()] = $this->getSchemaFromClass($propertyType);
        }

        return new Schema(
            type: 'object',
            properties: $properties,
        );
    }

    private function basicPhpTypeToSwaggerType(string $type): string
    {
        return match ($type) {
            'int' => 'integer',
            'float' => 'number',
            'string' => 'string',
            'bool' => 'boolean',
            default => throw new InvalidArgumentException("Unsupported type: " . $type),
        };
    }
}
