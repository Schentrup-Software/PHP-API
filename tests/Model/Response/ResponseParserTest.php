<?php

namespace PhpApi\Test\Model\Response;

use PhpApi\Enum\ContentType;
use PhpApi\Model\Response\AbstractJsonResponse;
use PhpApi\Model\Response\ResponseParser;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;

class ResponseParserTest extends TestCase
{
    public function test_getResponseProperties_validResponse_returnsProperties(): void
    {
        $properties = ResponseParser::getResponseProperties(TestJsonResponse::class);

        $this->assertNotEmpty($properties);

        // Get property names for easier comparison
        $propertyNames = array_map(function (ReflectionProperty $prop) {
            return $prop->getName();
        }, $properties);

        $this->assertContains('message', $propertyNames);
        $this->assertContains('code', $propertyNames);
        $this->assertContains('data', $propertyNames);
    }

    public function test_getResponseProperties_nonResponseClass_throwsException(): void
    {
        $this->expectException(RuntimeException::class);
        ResponseParser::getResponseProperties(NonResponseClass::class);
    }

    public function test_fillResponse_setsCorrectJsonContent(): void
    {
        $response = new TestJsonResponse('Success', 200, ['test' => true]);

        // Get properties so we can pass them to fillResponse
        $properties = ResponseParser::getResponseProperties(TestJsonResponse::class);
        $response->fillResponse($properties);

        // Check that JSON was created correctly
        $content = $response->getContent();
        $this->assertIsString($content);

        $decodedContent = json_decode($content, true);
        $this->assertEquals('Success', $decodedContent['message']);
        $this->assertEquals(200, $decodedContent['code']);
        $this->assertEquals(['test' => true], $decodedContent['data']);
    }

    public function test_response_send_setsCorrectContentTypeAndCode(): void
    {
        $response = new TestJsonResponse('Test', 201, ['id' => 1]);

        // Mock the parent send method to prevent actually sending headers
        $mockResponse = $this->getMockBuilder(TestJsonResponse::class)
            ->setConstructorArgs(['Test', 201, ['id' => 1]])
            ->onlyMethods(['sendParent'])
            ->getMock();

        $mockResponse->expects($this->once())
            ->method('sendParent');

        // Call send which should set headers and content
        /** @var TestJsonResponse $mockResponse */
        $mockResponse->send();

        // Check that content type and code were set correctly
        $this->assertEquals(201, $mockResponse->getCode());
        $this->assertEquals('application/json', $mockResponse->getHeader('Content-Type')->value);
    }
}

class TestJsonResponse extends AbstractJsonResponse
{
    public const ResponseCode = 201;
    public const ContentType = ContentType::Json;

    public function __construct(
        public string $message,
        public int $code,
        public array $data = []
    ) {
    }

    // Method for testing to avoid actually sending headers
    public function sendParent(): void
    {
        // Mock parent::send()
    }

    // Override send to avoid actually sending headers in tests
    public function send(): void
    {
        $this->fillResponse(ResponseParser::getResponseProperties($this::class));

        $this->setHeader(
            'Content-Type',
            $this::ContentType->value,
        );
        $this->setCode($this::ResponseCode ?? 200);

        $this->sendParent();
    }
}

class NonResponseClass
{
    public string $name = 'test';
}
