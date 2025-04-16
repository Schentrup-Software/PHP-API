<?php

namespace PhpApi;

use AutoRoute\AutoRoute;
use Closure;
use InvalidArgumentException;
use PhpApi\Enum\RouterExceptions;
use PhpApi\Interface\IRequestMiddleware;
use PhpApi\Interface\IResponseMiddleware;
use PhpApi\Model\Request\RequestParser;
use PhpApi\Model\Response\AbstractResponse;
use PhpApi\Model\RouterOptions;
use PhpApi\Model\SwaggerOptions;
use PhpApi\Swagger\GenerateSwaggerDocs;
use PhpApi\Utility\Arrays;
use ReflectionClass;
use ReflectionFunction;
use ReflectionNamedType;
use Sapien\Request;
use Sapien\Response;

class Router
{
    public const StaticRoutes = [
        'GET' => [
            '/swagger' => 'handleSwaggerPage',
            '/swagger/json' => 'handleSwaggerJson',
        ],
    ];

    protected AutoRoute $autoRoute;

    /** @var IRequestMiddleware[] $requestMiddlewares */
    private array $requestMiddlewares = [];

    /** @var IResponseMiddleware[] $responseMiddlewares */
    private array $responseMiddlewares = [];

    /** @var array<int, callable> $errorHandlers */
    private array $errorHandlers = [];

    /** @var callable $controllerFactory */
    private mixed $controllerFactory;

    /**
     * @param callable|null $controllerFactory
     */
    public function __construct(
        private RouterOptions $routerOptions,
        private SwaggerOptions $swaggerOptions = new SwaggerOptions(),
        mixed $controllerFactory = null,
    ) {
        if ($controllerFactory === null) {
            $this->controllerFactory = function (string $className) {
                return new $className();
            };
        } elseif (is_callable($controllerFactory)) {
            $this->controllerFactory = $controllerFactory;
        } else {
            throw new InvalidArgumentException('Controller factory must be a callable or null');
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

        $httpMethod = $request->method->name ?? 'GET';
        $path = $request->url->path ?? '';

        if ($this->swaggerOptions->enabled
            && isset(self::StaticRoutes[$httpMethod][$path])
        ) {
            $method = self::StaticRoutes[$httpMethod][$path];
            $this->$method();
            return;
        }

        $route = $this->autoRoute->getRouter()->route($httpMethod, $path);

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
                $request = RequestParser::generateRequest($request, $parameterClass, $httpMethod);

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

    private function handleSwaggerJson(): void
    {
        $response = new Response();
        $response->setCode(200);
        $response->setHeader('Content-Type', 'application/json');
        $response->setContent((new GenerateSwaggerDocs(
            $this->autoRoute,
            $this->routerOptions,
            $this->swaggerOptions,
        ))->generate());
        $response->send();
    }

    private function handleSwaggerPage(): void
    {
        $response = new Response();
        $response->setCode(200);
        $response->setHeader('Content-Type', 'text/html');
        $response->setContent(file_get_contents(__DIR__ . '/Swagger/index.html'));
        $response->send();
    }
}
