<?php

namespace PhpApi\Model\Response;

use PhpApi\Enum\CommonHeader;
use PhpApi\Enum\ContentType;
use ReflectionClass;
use ReflectionProperty;
use Sapien\Response;

abstract class AbstractResponse extends Response
{
    public function sendResponse(): void
    {
        $thisClass = new ReflectionClass($this);
        $parentClass = $thisClass->getParentClass();

        $properties = $thisClass->getProperties();
        $inheritedProperties = $parentClass->getProperties();
        $properties = array_diff($properties, $inheritedProperties);

        $this->fillResponse($properties);

        $this->setHeader(CommonHeader::CONTENT_TYPE->value, $this->getContentType()->value);
        $this->send();
    }
    
    /**
     * @param ReflectionProperty[] $properties  
     */
    abstract public function fillResponse(array $properties): void;

    abstract public function getContentType(): ContentType;
}