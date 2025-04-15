<?php

namespace PhpApi\Model\Response;

use PhpApi\Enum\CommonHeader;
use PhpApi\Enum\ContentType;
use ReflectionProperty;
use RuntimeException;
use Sapien\Response;

abstract class AbstractResponse extends Response
{
    /** @var ?ContentType ContentType */
    public const ContentType = null;
    /** @var ?int ResponseCode */
    public const ResponseCode = null;

    public function sendResponse(): void
    {
        if (!in_array($this::ContentType, ContentType::cases(), true)) {
            throw new RuntimeException('Content type is not set for ' . $this::class);
        }

        if (!is_int($this::ResponseCode)) {
            throw new RuntimeException('Response code is not set for ' . $this::class);
        }

        $this->fillResponse(ResponseParser::getResponseProperties($this::class));

        $this->setHeader(
            CommonHeader::CONTENT_TYPE->value,
            /** @phan-suppress-next-line PhanTypeExpectedObjectPropAccessButGotNull, PhanPossiblyUndeclaredProperty */
            $this::ContentType->value,
        );
        $this->setCode($this::ResponseCode ?? 200);
        $this->send();
    }

    /**
     * @param ReflectionProperty[] $properties
     */
    abstract public function fillResponse(array $properties): void;
}
