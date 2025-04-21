<?php

namespace PhpApi\Test\Model\Request;

use InvalidArgumentException;
use PhpApi\Enum\InputParamType;
use PhpApi\Model\Request\AbstractRequest;
use PhpApi\Model\Request\Attribute\CookieRequestParam;
use PhpApi\Model\Request\Attribute\HeaderRequestParam;
use PhpApi\Model\Request\Attribute\JsonRequestParam;
use PhpApi\Model\Request\Attribute\QueryParam;
use PhpApi\Model\Request\RequestParser;
use PHPUnit\Framework\TestCase;
use Sapien\Request;

class RequestParserTest extends TestCase
{
    public function test_getParamTypes_queryParams_returns_correct_properties(): void
    {
        $params = RequestParser::getParamTypes(TestQueryRequest::class, 'GET');

        $this->assertCount(2, $params);
        $this->assertEquals('query', $params[0]->name);
        $this->assertEquals(InputParamType::Query, $params[0]->type);
        $this->assertEquals('offset', $params[1]->name);
        $this->assertEquals(InputParamType::Query, $params[1]->type);
    }

    public function test_getParamTypes_jsonParams_returns_correct_properties(): void
    {
        $params = RequestParser::getParamTypes(TestJsonRequest::class, 'POST');

        $this->assertCount(2, $params);
        $this->assertEquals('username', $params[0]->name);
        $this->assertEquals(InputParamType::Json, $params[0]->type);
        $this->assertEquals('password', $params[1]->name);
        $this->assertEquals(InputParamType::Json, $params[1]->type);
    }

    public function test_getParamTypes_cookieParams_returns_correct_properties(): void
    {
        $params = RequestParser::getParamTypes(TestCookieRequest::class, 'GET');

        $this->assertCount(1, $params);
        $this->assertEquals('session_token', $params[0]->name);
        $this->assertEquals(InputParamType::Cookie, $params[0]->type);
    }

    public function test_getParamTypes_headerParams_returns_correct_properties(): void
    {
        $params = RequestParser::getParamTypes(TestHeaderRequest::class, 'GET');

        $this->assertCount(1, $params);
        $this->assertEquals('x-api-key', $params[0]->name);
        $this->assertEquals(InputParamType::Header, $params[0]->type);
    }

    public function test_generateRequest_queryParams_populates_object(): void
    {
        $request = new Request(
            method: 'GET',
            globals: [
                '_GET' => [
                    'query' => 'test',
                    'offset' => '10'
                ]
            ]
        );

        /** @var TestQueryRequest $parsedRequest */
        $parsedRequest = RequestParser::generateRequest($request, TestQueryRequest::class, 'GET');

        $this->assertInstanceOf(TestQueryRequest::class, $parsedRequest);
        $this->assertEquals('test', $parsedRequest->query);
        $this->assertEquals(10, $parsedRequest->offset);
    }

    public function test_generateRequest_jsonParams_populates_object(): void
    {
        $request = new Request(
            method: 'POST',
            globals: [
                '_POST' => [
                    'username' => 'testuser',
                    'password' => 'secret'
                ]
            ]
        );

        /** @var TestJsonRequest $parsedRequest */
        $parsedRequest = RequestParser::generateRequest($request, TestJsonRequest::class, 'POST');

        $this->assertInstanceOf(TestJsonRequest::class, $parsedRequest);
        $this->assertEquals('testuser', $parsedRequest->username);
        $this->assertEquals('secret', $parsedRequest->password);
    }

    public function test_generateRequest_cookieParams_populates_object(): void
    {
        $request = new Request(
            method: 'GET',
            globals: [
                '_COOKIE' => [
                    'session_token' => 'abc123'
                ]
            ]
        );

        /** @var TestCookieRequest $parsedRequest */
        $parsedRequest = RequestParser::generateRequest($request, TestCookieRequest::class, 'GET');

        $this->assertInstanceOf(TestCookieRequest::class, $parsedRequest);
        $this->assertEquals('abc123', $parsedRequest->sessionToken);
    }

    public function test_generateRequest_headerParams_populates_object(): void
    {
        $request = new Request(
            method: 'GET',
            globals: [
                '_SERVER' => [
                    'HTTP_X_API_KEY' => 'key123'
                ]
            ]
        );

        /** @var TestHeaderRequest $parsedRequest */
        $parsedRequest = RequestParser::generateRequest($request, TestHeaderRequest::class, 'GET');

        $this->assertInstanceOf(TestHeaderRequest::class, $parsedRequest);
        $this->assertEquals('key123', $parsedRequest->apiKey);
    }

    public function test_generateRequest_missingRequiredParam_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $request = new Request(method: 'POST');
        RequestParser::generateRequest($request, TestJsonRequest::class, 'POST');
    }

    public function test_generateRequest_withDefaultValues_usesDefaults(): void
    {
        $request = new Request(
            method: 'GET',
            globals: [
                '_GET' => [
                    'query' => 'test'
                ]
            ]
        );

        /** @var TestQueryRequest $parsedRequest */
        $parsedRequest = RequestParser::generateRequest($request, TestQueryRequest::class, 'GET');

        $this->assertEquals('test', $parsedRequest->query);
        $this->assertEquals(0, $parsedRequest->offset);
    }
}

class TestQueryRequest extends AbstractRequest
{
    public function __construct(
        #[QueryParam]
        public string $query,
        #[QueryParam]
        public int $offset = 0
    ) {
    }
}

class TestJsonRequest extends AbstractRequest
{
    public function __construct(
        #[JsonRequestParam]
        public string $username,
        #[JsonRequestParam]
        public string $password
    ) {
    }
}

class TestCookieRequest extends AbstractRequest
{
    public function __construct(
        #[CookieRequestParam(name: 'session_token')]
        public string $sessionToken
    ) {
    }
}

class TestHeaderRequest extends AbstractRequest
{
    public function __construct(
        #[HeaderRequestParam(name: 'x-api-key')]
        public string $apiKey
    ) {
    }
}
