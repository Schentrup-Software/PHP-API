<?php

namespace PhpApi\Interface;

use PhpApi\Model\Response\AbstractResponse;

interface IResponseMiddleware
{
    public function handleResponse(AbstractResponse $response): AbstractResponse;
}