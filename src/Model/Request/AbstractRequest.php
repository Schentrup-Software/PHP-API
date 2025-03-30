<?php

namespace SchentrupSoftware\PhpApiSample\Model\Request;

use ReflectionClass;
use Sapien\Request;
use SchentrupSoftware\PhpApiSample\Enum\HttpMethod;
use SchentrupSoftware\PhpApiSample\Model\Request\Attribute\QueryParam;
use SchentrupSoftware\PhpApiSample\Model\Request\Attribute\InputParam;
use SchentrupSoftware\PhpApiSample\Model\Request\Attribute\JsonRequestParam;

abstract class AbstractRequest
{
    public readonly Request $request;

    public function __construct(
        Request $request,
    )
    {
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

            if ((isset($queryParam) || in_array($this->request->method->name, HttpMethod::getQueryOnlyMethods())) 
                && isset($this->request->query[$name])
            ) {
                $this->{$property->getName()} = $this->request->query[$name];
            } elseif (isset($requestParam) && isset($this->request->input[$name])) {
                $this->{$property->getName()} = $this->request->input[$name];
            } elseif ((isset($jsonParam) || !in_array($this->request->method->name, HttpMethod::getQueryOnlyMethods()))
                && isset($this->request->json[$name])
            ) {
                $this->{$property->getName()} = $this->request->json[$name];
            }
        }
    }
}