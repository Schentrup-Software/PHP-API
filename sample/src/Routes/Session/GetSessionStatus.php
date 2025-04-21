<?php

namespace PhpApiSample\Routes\Session;

use PhpApi\Model\Request\AbstractRequest;
use PhpApi\Model\Response\AbstractJsonResponse;
use PhpApi\Model\Request\Attribute\CookieRequestParam;
use PhpApi\Swagger\Attribute\SwaggerDescription;
use PhpApi\Swagger\Attribute\SwaggerSummary;
use PhpApi\Swagger\Attribute\SwaggerTag;

#[SwaggerTag(name: 'Session', description: 'Session management')]
class GetSessionStatus
{
    #[SwaggerSummary('Get session status')]
    #[SwaggerDescription('Retrieve the current user session information using cookie')]
    public function execute(StatusRequest $request): StatusResponse
    {
        // Check if session token exists
        if (empty($request->sessionToken)) {
            return new StatusResponse(
                isAuthenticated: false,
                message: 'No active session'
            );
        }

        // Validate session token (simplified example)
        // In reality, you would verify this against a database or cache
        $isValid = substr($request->sessionToken, 0, 5) === 'valid';

        return new StatusResponse(
            isAuthenticated: $isValid,
            username: $isValid ? $this->extractUsername($request->sessionToken) : null,
            sessionExpiry: $isValid ? time() + 3600 : null,
            message: $isValid ? 'Active session' : 'Invalid session token'
        );
    }

    private function extractUsername(string $token): string
    {
        // In a real app, this would decode the session token
        return 'user_' . substr(md5($token), 0, 8);
    }
}

class StatusRequest extends AbstractRequest
{
    public function __construct(
        #[CookieRequestParam(name: 'session_token')]
        public ?string $sessionToken = null
    ) {
    }
}

class StatusResponse extends AbstractJsonResponse
{
    public const ResponseCode = 200;

    public function __construct(
        public bool $isAuthenticated = false,
        public ?string $username = null,
        public ?int $sessionExpiry = null,
        public string $message = ''
    ) {
    }
}
