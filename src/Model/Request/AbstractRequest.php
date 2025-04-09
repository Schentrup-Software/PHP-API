<?php

namespace PhpApi\Model\Request;

use InvalidArgumentException;
use PhpApi\Enum\HttpMethod;
use PhpApi\Enum\InputParamType;
use ReflectionClass;
use ReflectionNamedType;
use Sapien\Request;

abstract class AbstractRequest
{
    public readonly Request $request;

    public function __construct(
        Request $request,
    ) {
        $this->request = $request;

        $paramTypes = $this->getParamTypes($this->request->method->name);

        foreach ($paramTypes as $paramType) {
            if ($paramType->type === InputParamType::Query) {
                $queryParamValue = $this->request->query[$paramType->name] ?? null;
                if (isset($queryParamValue)) {
                    $this->{$paramType->propertyName} = $queryParamValue;
                }
            } elseif ($paramType->type === InputParamType::Json) {
                $jsonParamValue = $this->request->json[$paramType->name] ?? null;
                if (isset($jsonParamValue)) {
                    $this->{$paramType->propertyName} = $jsonParamValue;
                }
            } elseif ($paramType->type === InputParamType::Input) {
                $inputParamValue = $this->request->input[$paramType->name] ?? null;
                if (isset($inputParamValue)) {
                    $this->{$paramType->propertyName} = $inputParamValue;
                }
            }
        }
    }

    /**
     * @return RequestProperty[]
     * @throws InvalidArgumentException
     */
    public static function getParamTypes(?string $method): array
    {
        if ($method === null) {
            throw new InvalidArgumentException('Method cannot be null when getting param types for request');
        }

        $result = [];

        $reflectionClass = new ReflectionClass(static::class);
        $properties = $reflectionClass->getProperties();
        foreach ($properties as $property) {
            $propertyType = $property->getType();

            if (!($propertyType instanceof ReflectionNamedType)) {
                throw new InvalidArgumentException("Property type must be a named type. Cannot be null or union type");
            } elseif ($propertyType->getName() === Request::class) {
                continue;
            }

            $attributes = $property->getAttributes();
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
                $inputParamType = in_array($method, HttpMethod::getQueryOnlyMethods())
                    ? InputParamType::Query
                    : InputParamType::Json;
            }

            $result[] = new RequestProperty(
                name: $name,
                propertyName: $property->getName(),
                type: $inputParamType,
            );
        }

        return $result;
    }
}
