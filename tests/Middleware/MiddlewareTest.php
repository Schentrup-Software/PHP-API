<?php

namespace PhpApi\Test\Middleware;

use Exception;
use PhpApi\Interface\IRequestMiddleware;
use PhpApi\Interface\IResponseMiddleware;
use PhpApi\Model\Request\AbstractRequest;
use PhpApi\Model\Response\AbstractJsonResponse;
use PhpApi\Model\Response\AbstractResponse;
use PHPUnit\Framework\TestCase;
use Sapien\Request;

class MiddlewareTest extends TestCase
{
    public function test_requestMiddleware_modifiesRequest(): void
    {
        $middleware = new TestRequestMiddleware();
        $request = new TestRequest();

        /** @var TestRequest $modifiedRequest */
        $modifiedRequest = $middleware->handleRequest($request);

        $this->assertEquals('modified', $modifiedRequest->testValue);
    }

    public function test_requestMiddleware_canThrowException(): void
    {
        $middleware = new TestExceptionMiddleware();
        $request = new TestRequest();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unauthorized request');

        $middleware->handleRequest($request);
    }

    public function test_responseMiddleware_modifiesResponse(): void
    {
        $middleware = new TestResponseMiddleware();
        $response = new TestResponse();

        /** @var TestResponse $modifiedResponse */
        $modifiedResponse = $middleware->handleResponse($response);

        $this->assertEquals('Modified message', $modifiedResponse->message);
        $this->assertEquals(time(), $modifiedResponse->timestamp);
    }

    public function test_multipleMiddleware_appliesInOrder(): void
    {
        $middleware1 = new TestRequestMiddleware();
        $middleware2 = new TestRequestMiddleware2();
        $request = new TestRequest();

        $modifiedRequest = $middleware1->handleRequest($request);
        /** @var TestRequest $finalRequest */
        $finalRequest = $middleware2->handleRequest($modifiedRequest);

        $this->assertEquals('modified', $finalRequest->testValue);
        $this->assertEquals('second_middleware', $finalRequest->secondValue);
    }
}

class TestRequest extends AbstractRequest
{
    public string $testValue = 'original';
    public string $secondValue = 'original';

    public function __construct()
    {
        $this->setRequest(new Request());
    }
}

class TestRequestMiddleware implements IRequestMiddleware
{
    /**
     * @param TestRequest $request
     * @return TestRequest
     */
    public function handleRequest(AbstractRequest $request): AbstractRequest
    {
        $request->testValue = 'modified';
        return $request;
    }
}

class TestRequestMiddleware2 implements IRequestMiddleware
{
    /**
     * @param TestRequest $request
     * @return TestRequest
     */
    public function handleRequest(AbstractRequest $request): AbstractRequest
    {
        $request->secondValue = 'second_middleware';
        return $request;
    }
}

class TestExceptionMiddleware implements IRequestMiddleware
{
    public function handleRequest(AbstractRequest $request): AbstractRequest
    {
        throw new Exception('Unauthorized request');
    }
}

class TestResponse extends AbstractJsonResponse
{
    public const ResponseCode = 200;

    public string $message = 'Original message';
    public ?int $timestamp = null;

    public function fillResponse(array $properties): void
    {
        // Mock implementation for testing
    }
}

class TestResponseMiddleware implements IResponseMiddleware
{
    public function handleResponse(AbstractResponse $response): AbstractResponse
    {
        if ($response instanceof TestResponse) {
            $response->message = 'Modified message';
            $response->timestamp = time();
        }
        return $response;
    }
}
