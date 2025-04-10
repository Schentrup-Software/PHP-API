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
        $parentClass = $thisClass->getParentClass();

        if ($parentClass === false) {
            throw new RuntimeException('No parent class found for ' . $thisClass->getName());
        }

        $properties = $thisClass->getProperties();
        $inheritedProperties = $parentClass->getProperties();
        $properties = array_diff($properties, $inheritedProperties);

        return $properties;
    }
}
