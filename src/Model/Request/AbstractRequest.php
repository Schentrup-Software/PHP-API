<?php

namespace PhpApi\Model\Request;

use PhpApi\Enum\HttpMethod;
use PhpApi\Model\Request\Attribute\InputParam;
use PhpApi\Model\Request\Attribute\JsonRequestParam;
use PhpApi\Model\Request\Attribute\QueryParam;
use ReflectionClass;
use Sapien\Request;

abstract class AbstractRequest
{
    public readonly Request $request;

    public function __construct(
        Request $request,
    ) {
        $this->request = $request;

        $releactionClass = new ReflectionClass(static::class);
        $properties = $releactionClass->getProperties();
        foreach ($properties as $property) {
            $attributes = $property->getAttributes();
            foreach ($attributes as $attribute) {
                $attributeInstance = $attribute->newInstance();
                if ($attributeInstance instanceof QueryParam) {
                    $queryParam = true;
                    $name = $attributeInstance->name;
                    break;
                } elseif ($attributeInstance instanceof InputParam) {
                    $requestParam = true;
                    $name = $attributeInstance->name;
                    break;
                } elseif ($attributeInstance instanceof JsonRequestParam) {
                    $jsonParam = true;
                    $name = $attributeInstance->name;
                    break;
                }
            }

            if (!isset($name)) {
                $name = $property->getName();
            }

            $queryParamValue = $this->request->query[$name] ?? null;
            if ((isset($queryParam) || in_array($this->request->method->name, HttpMethod::getQueryOnlyMethods()))
                && isset($queryParamValue)
            ) {
                $this->{$property->getName()} = $queryParamValue;
                continue;
            }

            $inputValue = $this->request->input[$name] ?? null;
            if (isset($requestParam) && isset($inputValue)) {
                $this->{$property->getName()} = $inputValue;
                continue;
            }

            $jsonParamValue = $this->request->json[$name] ?? null;
            if ((isset($jsonParam) || !in_array($this->request->method->name, HttpMethod::getQueryOnlyMethods()))
                && isset($jsonParamValue)
            ) {
                $this->{$property->getName()} = $this->request->json[$name];
                continue;
            }
        }
    }
}
