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
    /**
     * @return RequestProperty[]
     * @throws InvalidArgumentException
     */
    public static function getParamTypes(string $requestClass, ?string $method): array
    {
        if ($method === null) {
            throw new InvalidArgumentException('Method cannot be null when getting param types for request');
        }

        $result = [];

        $reflectionClass = new ReflectionClass($requestClass);
        $properties = $reflectionClass->getProperties();
        foreach ($properties as $property) {
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
                if ($inputParamType === null) {
                    continue;
                }
                $name = $attributeInstance->name;
                break;
            }

            if (!isset($name)) {
                $name = $property->getName();
            }

            if (!isset($inputParamType)) {
                $inputParamType = in_array(strtoupper($method), HttpMethod::getQueryOnlyMethods())
                    ? InputParamType::Query
                    : InputParamType::Json;
            }

            $result[] = new RequestProperty(
                name: $name,
                propertyName: $property->getName(),
                type: $inputParamType,
                hasDefaultValue: $property->hasDefaultValue(),
            );
        }

        return $result;
    }
}
