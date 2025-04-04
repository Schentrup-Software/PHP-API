<?php

namespace PhpApi\Interface;

use PhpApi\Model\Response\AbstractResponse;

interface IRequestMiddleware
{
    public function handleResponse(AbstractResponse $response): AbstractResponse;
}