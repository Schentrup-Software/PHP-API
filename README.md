# PHP API

A basic api framework for PHP. It uses classes for request and responses to make a self documenting API. It uses [pmjones/AutoRoute](https://github.com/pmjones/AutoRoute) for routing and [sapienphp/sapien](https://github.com/sapienphp/sapien) for request and response objects.

[![PHP Analysis And Tests](https://github.com/Schentrup-Software/PHP-API/actions/workflows/php.yml/badge.svg)](https://github.com/Schentrup-Software/PHP-API/actions/workflows/php.yml)

## Features
* **Convention-Based Routing**: Automatically maps [URLs to controller classes](https://github.com/pmjones/AutoRoute?tab=readme-ov-file#how-it-works) based on directory structure and naming conventions
* **Strongly Typed Requests/Responses**: Type-safe request and response objects with automatic parameter parsing
* **Auto-Generated Swagger Documentation**: API documentation automatically generated from your code and PHP attributes
* **Middleware Support**: Add request/response middleware for cross-cutting concerns like authentication and logging
* **Content Negotiation**: Support for different content types (JSON, Form data, etc.)
* **Input Validation**: Automatic validation and type conversion of request parameters
* **Path Variables**: Support for [dynamic path segments](https://github.com/pmjones/AutoRoute?tab=readme-ov-file#dynamic-parameters) in routes
* **Error Handling**: Customizable error responses for various error conditions
* **Attribute-Based Metadata**: Use PHP 8 attributes for documentation and parameter configuration
* **Parameter Sources**: Get input from different sources (query parameters, JSON body, cookies, headers)


## Installation
```
composer require schentrup-software/php-api
```

## Basic Usage
1. Create a Router

```php
<?php
// index.php
require_once __DIR__ . '/vendor/autoload.php';

use PhpApi\Router;
use PhpApi\Model\RouterOptions;
use PhpApi\Model\SwaggerOptions;

$router = new Router(
    new RouterOptions(
        namespace: 'YourApp\\Routes',
        directory: __DIR__ . '/src/Routes',
    ),
    new SwaggerOptions(
        title: "Your API Documentation",
        apiVersion: "1.0.0",
    )
);

$router->route()->send();
```
2. Create a Simple Controller
```php
<?php
// src/Routes/Get.php
namespace YourApp\Routes;

use PhpApi\Model\Response\AbstractJsonResponse;
use PhpApi\Swagger\Attribute\SwaggerTag;
use PhpApi\Swagger\Attribute\SwaggerDescription;

#[SwaggerTag(name: 'Hello', description: 'Hello world example')]
class Get
{
    #[SwaggerDescription('Returns a hello world message')]
    public function execute(): HelloResponse
    {
        return new HelloResponse();
    }
}

class HelloResponse extends AbstractJsonResponse
{
    public const ResponseCode = 200;

    public function __construct(
        public string $message = 'Hello, World!',
        public string $timestamp = '',
    ) {
        $this->timestamp = date('Y-m-d H:i:s');
    }
}
```

## Request Parameters
PHP-API supports multiple parameter sources:

**Query Parameters**
```php
<?php
class GetUsers extends AbstractRequest
{
    public function __construct(
        #[QueryParam]
        public int $page = 1,
        #[QueryParam]
        public int $limit = 10
    ) {
    }
}
```
**JSON Body Parameters**
```php
<?php
class CreateUserRequest extends AbstractRequest
{
    public function __construct(
        #[JsonRequestParam]
        public string $name,
    ) {
    }
}
```
or
```php
<?php
#[JsonRequestParam]
class CreateUserRequest extends AbstractRequest
{
    public function __construct(
        public string $name,
    ) {
    }
}
```
**Header Parameters**
```php
<?php
class AuthenticatedRequest extends AbstractRequest
{
    public function __construct(
        #[HeaderRequestParam(name: 'Authorization')]
        public string $token
    ) {
    }
}
```
**Cookie Parameters**
```php
<?php
class SessionRequest extends AbstractRequest
{
    public function __construct(
        #[CookieRequestParam(name: 'session_id')]
        public ?string $sessionId = null
    ) {
    }
}
```

## Middleware

**Request Middleware**
```php
<?php
class AuthenticationMiddleware implements IRequestMiddleware
{
    public function handleRequest(AbstractRequest $request): AbstractRequest
    {
        if ($request instanceof AuthenticatedRequest) {
            // Validate token
            if (!$this->validateToken($request->token)) {
                throw new Exception('Invalid token');
            }
        }
        return $request;
    }

    private function validateToken(string $token): bool
    {
        // Token validation logic
        return true;
    }
}
```
**Response Middleware**
```php
<?php
class TimestampMiddleware implements IResponseMiddleware
{
    public function handleResponse(AbstractResponse $response): AbstractResponse
    {
        if (property_exists($response, 'timestamp') && !isset($response->timestamp)) {
            $response->timestamp = time();
        }
        return $response;
    }
}
```
**Adding Middleware**
```php
<?php
$router = new Router($options);
$router->addMiddleware(new AuthenticationMiddleware());
$router->addMiddleware(new TimestampMiddleware());
```

## Error Handling
```php
<?php
// Custom 404 page
$router->handleNotFound('/error/404');

// Custom 404 response
$response = new Response();
$response->setCode(404);
$response->setContent('{"error": "Resource not found"}');
$router->handleNotFound($response);

// Custom handler with closure
$router->handleNotFound(function(Request $req) {
    $response = new Response();
    $response->setCode(404);
    $response->setContent("Could not find: " . $req->url->path);
    return $response;
});
```

## Swagger Documentation
PHP-API automatically generates Swagger/OpenAPI documentation from your code. Access the documentation at:

* /swagger - Swagger UI interface
* /swagger/json - Raw JSON OpenAPI definition

Use attributes to enhance the documentation:
```php
<?php
#[SwaggerTag(name: 'Users', description: 'User management endpoints')]
class PostUser
{
    #[SwaggerSummary('Create a new user')]
    #[SwaggerDescription('Creates a new user with the provided information')]
    public function execute(CreateUserRequest $request): UserResponse
    {
        // Implementation
    }
}
```

## Path Variables
Path variables are automatically mapped to method parameters:
```php
<?php
// Maps to /users/{id}
class GetUsersId
{
    public function execute($_, int $id): UserResponse
    {
        return new UserResponse($id);
    }
}
```

More info on this can be found in the auto-router documentation: [pmjones/auto-route](https://github.com/pmjones/AutoRoute).

## Contributing
Contributions are welcome! Please feel free to submit a Pull Request.

## License
This project is licensed under the MIT License - see the LICENSE file for details.
