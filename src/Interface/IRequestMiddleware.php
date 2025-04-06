<?php

namespace PhpApi\Interface;

use PhpApi\Model\Request\AbstractRequest;

interface IRequestMiddleware
{
    public function handleRequest(AbstractRequest $request): AbstractRequest;
}
