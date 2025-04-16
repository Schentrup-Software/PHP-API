<?php

namespace PhpApi\Swagger;

use AutoRoute\AutoRoute;
use InvalidArgumentException;
use PhpApi\Enum\ContentType as EnumContentType;
use PhpApi\Enum\InputParamType;
use PhpApi\Model\Request\AbstractRequest;
use PhpApi\Model\Request\RequestParser;
use PhpApi\Model\Response\AbstractResponse;
use PhpApi\Model\Response\ResponseParser;
use PhpApi\Model\RouterOptions;
use PhpApi\Model\SwaggerOptions;
use PhpApi\Swagger\Attribute\SwaggerDescription;
use PhpApi\Swagger\Attribute\SwaggerSummary;
use PhpApi\Swagger\Attribute\SwaggerTag;
use PhpApi\Swagger\Model\Contact;
use PhpApi\Swagger\Model\ContentType;
use PhpApi\Swagger\Model\ExternalDocs;
use PhpApi\Swagger\Model\Info;
use PhpApi\Swagger\Model\ItemMetadata;
use PhpApi\Swagger\Model\License;
use PhpApi\Swagger\Model\Parameter;
use PhpApi\Swagger\Model\Path;
use PhpApi\Swagger\Model\RequestBody;
use PhpApi\Swagger\Model\RequestObjectParseResults;
use PhpApi\Swagger\Model\RequestObjectQueryParam;
use PhpApi\Swagger\Model\Response;
use PhpApi\Swagger\Model\ResponseContent;
use PhpApi\Swagger\Model\Schema;
use PhpApi\Swagger\Model\SwaggerDoc;
use PhpApi\Swagger\Model\Tags;
use PhpApi\Utility\Arrays;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

class GenerateSwaggerDocs
{
    public function __construct(
        private readonly AutoRoute $autoRoute,
        private readonly RouterOptions $routerOptions,
        private readonly SwaggerOptions $swaggerOptions,
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
                    : $p,
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
        $tags = [];

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

                $reflectionClassMeta = $this->getItemMetadata($reflectionClass);
                $reflectionMethodMeta = $this->getItemMetadata($reflectionMethod);
                /** @var array<string, SwaggerTag> $itemTags */
                $itemTags = array_merge($reflectionMethodMeta->tags, $reflectionClassMeta->tags);
                $tags = array_merge($tags, $itemTags);

                $paths[$cleanPath][strtolower($method)] = new Path(
                    tags: array_keys($itemTags),
                    summary: $reflectionMethodMeta->summary
                        ?? $reflectionClassMeta->summary
                        ?? $reflectionClass->getName(),
                    description: $reflectionMethodMeta->description
                        ?? $reflectionClassMeta->description
                        ?? $reflectionClass->getName(),
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
                title: $this->swaggerOptions->title,
                description: $this->swaggerOptions->description,
                termsOfService: $this->swaggerOptions->termsOfServiceUrl,
                contact: new Contact(
                    name: $this->swaggerOptions->contactName,
                    email: $this->swaggerOptions->contactEmail,
                    url: $this->swaggerOptions->contactUrl,
                ),
                license: new License(
                    name: $this->swaggerOptions->licenseName,
                    url: $this->swaggerOptions->licenseUrl,
                ),
                version: $this->swaggerOptions->apiVersion,
            ),
            externalDocs: new ExternalDocs(
                url: $this->swaggerOptions->externalDocsUrl,
                description: $this->swaggerOptions->externalDocsDescription,
            ),
            tags: array_values(array_map(
                fn (SwaggerTag $tag) => new Tags(
                    name: $tag->name,
                    description: $tag->description,
                ),
                $tags
            )),
            paths: $paths,
        );
    }

    /**
     * @param array &$parameters
     */
    private function parseRequestType(ReflectionNamedType|ReflectionUnionType $reflectionType, string $method, array &$parameters): ?RequestBody
    {
        if ($reflectionType instanceof ReflectionUnionType) {
            $parsedTypeData = [];
            $description = null;

            foreach ($reflectionType->getTypes() as $type) {
                if ($type instanceof ReflectionNamedType) {
                    $parsedType = $this->parseNamedRequestType($type, $method);
                    $parsedTypeData[] = $parsedType;

                    $reflectionClass = new ReflectionClass($type->getName());
                    if (!$reflectionClass->isSubclassOf(AbstractResponse::class)) {
                        throw new InvalidArgumentException("Return type must be a subclass of AbstractResponse");
                    }

                    $description = $this->getItemMetadata($reflectionClass)->description;

                    foreach ($parsedType->queryParams as $name => $propertyData) {
                        $parameters[] = new Parameter(
                            name: $name,
                            in: 'query',
                            required: !$reflectionType->allowsNull()
                                && !$parsedType->allowsNull
                                && !$propertyData->allowsNull,
                            schema: $propertyData->schema,
                            description: $propertyData->description,
                        );
                    }
                } else {
                    throw new InvalidArgumentException("Request type cannot contain intersection types");
                }
            }

            /** @var array<string, RequestObjectParseResults[]> $groupedData */
            $groupedData = Arrays::groupBy($parsedTypeData, fn (RequestObjectParseResults $type) => $type->inputContentType?->toContentType());
            $content = array_map(
                fn ($types) => new ContentType(
                    schema: new Schema(
                        oneOf: array_map(
                            fn (RequestObjectParseResults $type) => new Schema(
                                type: 'object',
                                properties: $type->inputContent,
                            ),
                            $types
                        )
                    )
                ),
                $groupedData
            );

            if (empty($content)) {
                return null;
            }

            return new RequestBody(
                required: !$reflectionType->allowsNull(),
                content: $content,
                description: $description,
            );
        } else {
            $reflectionClass = new ReflectionClass($reflectionType->getName());
            if (!$reflectionClass->isSubclassOf(AbstractRequest::class)) {
                throw new InvalidArgumentException("Return type must be a subclass of AbstractRequest");
            }

            $parsedType = $this->parseNamedRequestType($reflectionType, $method);

            foreach ($parsedType->queryParams as $name => $propertyData) {
                $parameters[] = new Parameter(
                    name: $name,
                    in: 'query',
                    required: !$reflectionType->allowsNull()
                        && !$parsedType->allowsNull
                        && !$propertyData->allowsNull,
                    schema: $propertyData->schema,
                    description: $propertyData->description,
                );
            }

            $content = !empty($parsedType->inputContentType) ? [
                $parsedType->inputContentType?->toContentType() => new ContentType(
                    schema: new Schema(
                        type: 'object',
                        properties: $parsedType->inputContent,
                    )
                ),
            ] : [];

            if (empty($content)) {
                return null;
            }

            return new RequestBody(
                required: !$reflectionType->allowsNull(),
                content: $content,
                description: $this->getItemMetadata($reflectionClass)->description ?? $reflectionClass->getName(),
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
            $property = $reflectionClass->getProperty($paramType->propertyName);
            $propertyType = $property->getType();

            if (!($propertyType instanceof ReflectionNamedType)) {
                throw new InvalidArgumentException("Property type must be a named type. Cannot be null or union type");
            }

            if ($paramType->type === InputParamType::Query) {
                $queryContent[$paramType->name] = new RequestObjectQueryParam(
                    schema: $this->getSchemaFromClass($propertyType),
                    allowsNull: $propertyType->allowsNull()
                        || $paramType->hasDefaultValue,
                    description: $this->getItemMetadata($property)->description,
                );
            } elseif ($paramType->type === InputParamType::Json) {
                if ($inputContentType === null) {
                    $inputContentType = InputParamType::Json;
                } elseif ($inputContentType === InputParamType::Input) {
                    throw new InvalidArgumentException("Cannot have both json and input params in the same request");
                }

                $inputContent[$paramType->name] = $this->getSchemaFromClass($propertyType);
            } elseif ($paramType->type === InputParamType::Input) {
                if ($inputContentType === null) {
                    $inputContentType = InputParamType::Input;
                } elseif ($inputContentType === InputParamType::Json) {
                    throw new InvalidArgumentException("Cannot have both json and input params in the same request");
                }

                $inputContent[$paramType->name] = $this->getSchemaFromClass($propertyType);
            }
        }

        return new RequestObjectParseResults(
            queryParams: $queryContent,
            inputContentType: $inputContentType,
            inputContent: $inputContent,
            allowsNull: $reflectionType->allowsNull(),
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
        $required = [];

        foreach ($reflectionProperties as $reflectionProperty) {
            $propertyType = $reflectionProperty->getType();
            if (!($propertyType instanceof ReflectionNamedType)) {
                throw new InvalidArgumentException("Property type must be a named type. Cannot be null or union type");
            }

            $properties[$reflectionProperty->getName()] = $this->getSchemaFromClass($propertyType);

            if (!$propertyType->allowsNull()) {
                $required[] = $reflectionProperty->getName();
            }
        }

        return new Schema(
            type: 'object',
            properties: $properties,
            required: $required,
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

    private function getItemMetadata(ReflectionClass|ReflectionProperty|ReflectionMethod $item): ItemMetadata
    {
        $attributes = $item->getAttributes();
        $tags = [];

        foreach ($attributes as $attribute) {
            switch ($attribute->getName()) {
                case SwaggerDescription::class:
                    $swaggerDescription = $attribute->newInstance();
                    $description = $swaggerDescription->description;
                    break;
                case SwaggerSummary::class:
                    $swaggerSummary = $attribute->newInstance();
                    $summary = $swaggerSummary->summary;
                    break;
                case SwaggerTag::class:
                    $swaggerTag = $attribute->newInstance();
                    $tags[$swaggerTag->name] = $swaggerTag;
                    break;
            }
        }

        return new ItemMetadata(
            description: $description ?? null,
            summary: $summary ?? null,
            tags: $tags,
        );
    }
}
