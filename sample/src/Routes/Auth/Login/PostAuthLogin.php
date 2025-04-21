<?php

namespace PhpApiSample\Routes\Auth\Login;

use PhpApi\Model\Request\AbstractRequest;
use PhpApi\Model\Response\AbstractJsonResponse;
use PhpApi\Model\Request\Attribute\JsonRequestParam;
use PhpApi\Swagger\Attribute\SwaggerDescription;
use PhpApi\Swagger\Attribute\SwaggerSummary;
use PhpApi\Swagger\Attribute\SwaggerTag;

#[SwaggerTag(name: 'Authentication', description: 'Authentication endpoints')]
class PostAuthLogin
{
    #[SwaggerSummary('User authentication')]
    #[SwaggerDescription('Authenticate a user and return access token')]
    public function execute(LoginRequest $request): LoginResponse|LoginErrorResponse
    {
        // Simple username/password validation - in real app would check database
        if ($request->username === 'admin' && $request->password === 'password') {
            return new LoginResponse(
                accessToken: $this->generateToken($request->username),
                refreshToken: $this->generateRefreshToken()
            );
        }

        return new LoginErrorResponse('Invalid credentials');
    }

    private function generateToken(string $username): string
    {
        // In a real application, this would create a proper JWT token
        return base64_encode(json_encode([
            'sub' => $username,
            'exp' => time() + 3600,
            'iat' => time()
        ]));
    }

    private function generateRefreshToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}

#[JsonRequestParam]
class LoginRequest extends AbstractRequest
{
    public function __construct(
        public string $username,
        public string $password
    ) {
    }
}

class LoginResponse extends AbstractJsonResponse
{
    public const ResponseCode = 200;

    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public string $tokenType = 'Bearer',
        public int $expiresIn = 3600
    ) {
    }
}

class LoginErrorResponse extends AbstractJsonResponse
{
    public const ResponseCode = 401;

    public function __construct(
        public string $error,
        public string $errorCode = 'AUTHENTICATION_FAILED'
    ) {
    }
}
