<?php

namespace PhpApiSample\Middleware;

use PhpApi\Interface\IResponseMiddleware;
use PhpApi\Model\Response\AbstractResponse;

class TimeResponseMiddleware implements IResponseMiddleware
{
    public function handleResponse(AbstractResponse $response): AbstractResponse
    {
        if (property_exists($response, 'time') && !isset($response->time)) {
            $response->time = time();
        }

        return $response;
    }
}
