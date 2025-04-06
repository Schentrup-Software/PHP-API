<?php

namespace PhpApi\Model\Response;

use PhpApi\Enum\ContentType;
use ReflectionProperty;

class AbstractJsonResponse extends AbstractResponse
{
    /**
     * @param ReflectionProperty[] $propertyValues
     */
    public function fillResponse(array $propertyValues): void
    {
        $content = [];
        foreach ($propertyValues as $property) {
            $content[$property->getName()] = $property->getValue($this);
        }

        $this->setContent(json_encode($content));
    }

    public function getContentType(): ContentType
    {
        return ContentType::JSON;
    }
}
