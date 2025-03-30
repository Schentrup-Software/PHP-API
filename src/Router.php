<?php 

namespace PhpApi;

use AutoRoute\AutoRoute;
use InvalidArgumentException;
use PhpApi\Model\RouterOptions;
use ReflectionClass;
use Sapien\Request;
use SchentrupSoftware\PhpApiSample\Model\Request\AbstractRequest;
use SchentrupSoftware\PhpApiSample\Utility\Arrays;

class Router
{
    protected AutoRoute $autoRoute;

    public function __construct(
        private readonly RouterOptions $routerOptions,
        private readonly mixed $controllerFactory = null,
    )
    {
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
            ->route($request->method->name, $request->url->path);

        switch ($route->error) {
            case null:
                $action = ($this->controllerFactory)($route->class);
                $method = $route->method;
                $arguments = $route->arguments;

                $actionReflection = new ReflectionClass($action);
                if (!$actionReflection->hasMethod($method) ) {
                    throw new InvalidArgumentException("Method $method not found in class $route->class");
                }

                $methodReflection = $actionReflection->getMethod($method);
                if (!$methodReflection->isPublic()) {
                    throw new InvalidArgumentException("Method $method is not public in class $route->class"); 
                }

                $parameter = Arrays::getFirstElement($methodReflection->getParameters());
                if ($parameter !== null && $parameter->getType() !== null && !$parameter->getType()->isBuiltin()) {
                    $parameterClass = $parameter->getType()->getName();
                    $paramerClassReflection = new ReflectionClass($parameterClass);
                    if (!$paramerClassReflection->isSubclassOf(AbstractRequest::class)) {
                        throw new InvalidArgumentException("Parameter $parameterClass is not a subclass of " . AbstractRequest::class);
                    }
                    $request = new $parameterClass($request);
                    $arguments = array_merge([$request], $arguments);
                }

                $response = $action->$method(...$arguments);
                break;
        }
    }
}