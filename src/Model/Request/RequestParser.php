<?php

namespace PhpApi\Model\Request;

use InvalidArgumentException;
use PhpApi\Enum\HttpMethod;
use PhpApi\Enum\InputParamType;
use ReflectionClass;
use ReflectionNamedType;
use Sapien\Request;

class RequestParser
{
    public static function generateRequest(
        Request $request,
        string $requestClass,
        string $method,
    ): AbstractRequest {
        if (!class_exists($requestClass)) {
            throw new InvalidArgumentException("Request class $requestClass does not exist");
        }

        $propertyTypes = self::getParamTypes($requestClass, $method);
        $cacheJsonContent = null;
        $constructorArgs = array_map(
            fn (RequestProperty $propertyType) => self::getConstructorArgument($request, $propertyType, $cacheJsonContent),
            $propertyTypes,
        );

        /** @var AbstractRequest $parsedRequest */
        $parsedRequest = new $requestClass(...$constructorArgs);
        $parsedRequest->setRequest($request);

        return $parsedRequest;
    }

    private static function getConstructorArgument(
        Request $request,
        RequestProperty $propertyType,
        ?array &$cacheJsonContent,
    ): mixed {
        if ($propertyType->type === InputParamType::Json && !isset($cacheJsonContent)) {
            $cacheJsonContent = $request->content->getParsedBody() ?? [];
        }

        if (!empty($propertyType->subProperties)) {
            $content = match ($propertyType->type) {
                InputParamType::Query => $request->query,
                InputParamType::Json => $cacheJsonContent,
                InputParamType::Input => $request->input,
            };

            $subItemConstructorArgs = array_map(
                fn (RequestProperty $subProperty) => self::getSubObjectConstructorArguments($subProperty, $content[$propertyType->name] ?? []),
                $propertyType->subProperties,
            );

            return new ($propertyType->reflectionType->getName())(...$subItemConstructorArgs);
        } else {
            if ($propertyType->type === InputParamType::Query) {
                return $request->query[$propertyType->name] ?? $propertyType->defaultValue;
            } elseif ($propertyType->type === InputParamType::Json) {
                return $cacheJsonContent[$propertyType->name] ?? $propertyType->defaultValue;
            } elseif ($propertyType->type === InputParamType::Input) {
                return $request->input[$propertyType->name] ?? $propertyType->defaultValue;
            }
        }

        return $propertyType->defaultValue;
    }

    private static function getSubObjectConstructorArguments(
        RequestProperty $propertyType,
        array $content,
    ): mixed {
        if (!empty($propertyType->subProperties)) {
            $subItemConstructorArgs = array_map(
                fn (RequestProperty $subProperty) => self::getSubObjectConstructorArguments($subProperty, $content[$propertyType->name] ?? []),
                $propertyType->subProperties,
            );

            return new ($propertyType->reflectionType->getName())(...$subItemConstructorArgs);
        } else {
            return $content[$propertyType->name] ?? $propertyType->defaultValue;
        }
    }

    /**
     * @return RequestProperty[]
     * @throws InvalidArgumentException
     */
    public static function getParamTypes(string $requestClass, ?string $httpMethod, bool $isTopLevelClass = true): array
    {
        if ($httpMethod === null) {
            throw new InvalidArgumentException('Method cannot be null when getting param types for request');
        }

        $reflectionClass = new ReflectionClass($requestClass);
        if ($isTopLevelClass && !$reflectionClass->isSubclassOf(AbstractRequest::class)) {
            throw new InvalidArgumentException("Parameter $requestClass is not a subclass of " . AbstractRequest::class);
        }

        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            throw new InvalidArgumentException("Request class $requestClass does not have a constructor");
        }

        $constructorParams = $constructor->getParameters();
        $result = [];

        foreach ($constructorParams as $param) {
            $property = $reflectionClass->getProperty($param->getName());
            $propertyType = $property->getType();

            if (!($propertyType instanceof ReflectionNamedType)) {
                throw new InvalidArgumentException("Property type must be a named type. Cannot be null or union type");
            } elseif ($propertyType->getName() === Request::class) {
                continue;
            }

            $attributes = $property->getAttributes();
            $name = null;

            foreach ($attributes as $attribute) {
                $attributeInstance = $attribute->newInstance();
                $inputParamType = InputParamType::fromClassInstance($attributeInstance);
                if ($inputParamType === null || empty($attributeInstance->name)) {
                    continue;
                }
                $name = $attributeInstance->name;
                break;
            }

            if (!isset($name)) {
                $name = $property->getName();
            }

            if (!isset($inputParamType)) {
                $inputParamType = in_array(strtoupper($httpMethod), HttpMethod::getQueryOnlyMethods())
                    ? InputParamType::Query
                    : InputParamType::Json;
            }

            if (!$propertyType->isBuiltin()) {
                $subProperties = RequestParser::getParamTypes($propertyType->getName(), $httpMethod, false);
            }

            $result[] = new RequestProperty(
                name: $name,
                propertyName: $property->getName(),
                type: $inputParamType,
                hasDefaultValue: $param->isOptional() || $property->hasDefaultValue(),
                defaultValue: $param->isOptional() ? $param->getDefaultValue() : null,
                subProperties: $subProperties ?? [],
                reflectionType: $propertyType,
            );
        }

        return $result;
    }
}
