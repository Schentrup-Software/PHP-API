<?php

namespace PhpApiSample\Middleware;

use Exception;
use PhpApi\Interface\IRequestMiddleware;
use PhpApi\Model\Request\AbstractRequest;

class HeaderCheckMiddleware implements IRequestMiddleware
{
    public function handleRequest(AbstractRequest $request): AbstractRequest
    {
        $rawRequest = $request->getRequest();

        if (isset($rawRequest->headers['x-my-authorization'])
            && $rawRequest->headers['x-my-authorization'] !== 'expected_value'
        ) {
            throw new Exception('Unauthorized: Invalid authorization header');
        }

        return $request;
    }
}
