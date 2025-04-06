<?php

namespace PhpApi;

use AutoRoute\AutoRoute;
use Closure;
use InvalidArgumentException;
use PhpApi\Enum\RouterExceptions;
use PhpApi\Interface\IRequestMiddleware;
use PhpApi\Interface\IResponseMiddleware;
use PhpApi\Model\Request\AbstractRequest;
use PhpApi\Model\Response\AbstractResponse;
use PhpApi\Model\RouterOptions;
use PhpApi\Utility\Arrays;
use ReflectionClass;
use ReflectionFunction;
use ReflectionNamedType;
use Sapien\Request;
use Sapien\Response;

class Router
{
    protected AutoRoute $autoRoute;

    /** @var IRequestMiddleware[] $requestMiddlewares */
    private array $requestMiddlewares = [];

    /** @var IResponseMiddleware[] $responseMiddlewares */
    private array $responseMiddlewares = [];

    /** @var array<int, callable> $errorHandlers */
    private array $errorHandlers = [];

    public function __construct(
        private RouterOptions $routerOptions,
        private mixed $controllerFactory = null,
    ) {
        if ($this->controllerFactory === null) {
            $this->controllerFactory = function (string $className) {
                return new $className();
            };
        } elseif (!is_callable($this->controllerFactory)) {
            throw new InvalidArgumentException('Controller factory must be callable');
        }

        $this->autoRoute = new AutoRoute(
            $this->routerOptions->namespace,
            $this->routerOptions->directory,
            $this->routerOptions->baseUrl,
            method: $this->routerOptions->method,
            suffix: $this->routerOptions->suffix,
            wordSeparator: $this->routerOptions->wordSeparator,
            ignoreParams: 1,
        );
    }

    public function route(?Request $request = null): void
    {
        if ($request === null) {
            $request = new Request();
        }

        $route = $this->autoRoute->getRouter()
            ->route($request->method->name ?? 'GET', $request->url->path ?? '');

        if ($route->error != null) {
            $routerException = RouterExceptions::fromRouterException($route->error);
            $errorHandler = $this->errorHandlers[$routerException->value] ?? null;
            if ($errorHandler === null) {
                $errorHandler = fn ($r) => $this->defaultErrorHandler();
            }

            print_r($route->messages);

            $response = $errorHandler($request);
            if ($response instanceof Response) {
                $response->send();
            } else {
                throw new InvalidArgumentException("Error handler must return a Response object");
            }

            return;
        }

        $action = ($this->controllerFactory)($route->class);
        $method = $route->method;
        $arguments = $route->arguments;

        $actionReflection = new ReflectionClass($action);
        if (!$actionReflection->hasMethod($method)) {
            throw new InvalidArgumentException("Method $method not found in class $route->class");
        }

        $methodReflection = $actionReflection->getMethod($method);
        if (!$methodReflection->isPublic()) {
            throw new InvalidArgumentException("Method $method is not public in class $route->class");
        }

        $parameterType = Arrays::getFirstElement($methodReflection->getParameters())?->getType();
        if ($parameterType !== null) {
            if (!($parameterType instanceof ReflectionNamedType)) {
                throw new InvalidArgumentException("Parameter type must be a named type. Cannot be null or union type");
            }

            if (!$parameterType->isBuiltin()) {
                $parameterClass = $parameterType->getName();
                $paramerClassReflection = new ReflectionClass($parameterClass);
                if (!$paramerClassReflection->isSubclassOf(AbstractRequest::class)) {
                    throw new InvalidArgumentException("Parameter $parameterClass is not a subclass of " . AbstractRequest::class);
                }
                $request = new $parameterClass($request);

                foreach ($this->requestMiddlewares as $middleware) {
                    $request = $middleware->handleRequest($request);
                }

                $arguments = array_merge([$request], $arguments);
            }
        } else {
            $arguments = array_merge([null], $arguments);
        }

        $response = $action->$method(...$arguments);

        if (!($response instanceof AbstractResponse)) {
            throw new InvalidArgumentException("Method $method must return an instance of " . AbstractResponse::class);
        }

        foreach ($this->responseMiddlewares as $middleware) {
            $response = $middleware->handleResponse($response);
        }

        $response->sendResponse();
    }

    public function addMiddleware(IRequestMiddleware|IResponseMiddleware $middleware): self
    {
        if ($middleware instanceof IRequestMiddleware) {
            $this->requestMiddlewares[] = $middleware;
        }

        if ($middleware instanceof IResponseMiddleware) {
            $this->responseMiddlewares[] = $middleware;
        }

        return $this;
    }

    public function handleInvalidArgument(Response|Closure $handler): self
    {
        return $this->handleException(RouterExceptions::InvalidArgumentException, $handler);
    }

    public function handleNotFound(string|Response|Closure $handler): self
    {
        $response = null;

        if (is_string($handler)) {
            $response = new Response();
            $response->setCode(404);
            $response->setHeader('location', $handler);
            return $this->handleException(RouterExceptions::NotFoundException, $response);
        } else {
            return $this->handleException(RouterExceptions::NotFoundException, $handler);
        }
    }

    public function handleMethodNotAllowed(Response|Closure $handler): self
    {
        return $this->handleException(RouterExceptions::MethodNotAllowedException, $handler);
    }

    public function handleServerError(Response|Closure $handler): self
    {
        return $this->handleException(RouterExceptions::RouterServerError, $handler);
    }

    private function handleException(
        RouterExceptions $exception,
        Response|Closure $handler
    ): self {
        $result = null;

        if ($handler instanceof Closure) {
            $relectionFunction = new ReflectionFunction($handler);
            $parameters = $relectionFunction->getParameters();
            $parameter = Arrays::getFirstElement($parameters)?->getType();

            if ($parameter instanceof ReflectionNamedType && $parameter->getName() !== Request::class) {
                throw new InvalidArgumentException('Handler must accept a Request object');
            }

            $result = $handler;
        } elseif ($handler instanceof Response) {
            $result = fn (Request $request) => $handler;
        } else {
            throw new InvalidArgumentException('Handler must be a callable, string or Response object');
        }

        $this->errorHandlers[$exception->value] = $result;
        return $this;
    }

    private function defaultErrorHandler(): Response
    {
        $response = new Response();
        $response->setCode(500);
        $response->setHeader('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'An internal server error occurred',
        ]));

        return $response;
    }
}
