<?php 

namespace PhpApi;

use AutoRoute\AutoRoute;
use InvalidArgumentException;
use PhpApi\Model\RouterOptions;
use Sapien\Request;

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
        );
    }

    public function route(?Request $request = null): void
    {
        if ($request === null) {
            $request = new Request();
        }

        $route = $this->autoRoute->getRouter()
            ->route($request->method->name, $request->url->path);

        
    }
}