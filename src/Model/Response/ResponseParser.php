<?php

namespace PhpApi\Model\Response;

use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

class ResponseParser
{
    /**
     * @return ReflectionProperty[]
     */
    public static function getResponseProperties(string $class): array
    {
        $thisClass = new ReflectionClass($class);

        if (!$thisClass->isSubclassOf(AbstractResponse::class)) {
            throw new RuntimeException('Class ' . $class . ' is not a subclass of AbstractResponse');
        }

        $baseClass = new ReflectionClass(AbstractResponse::class);
        $inheritedProperties = $baseClass->getProperties();
        $properties = array_diff($thisClass->getProperties(), $inheritedProperties);

        return $properties;
    }
}
