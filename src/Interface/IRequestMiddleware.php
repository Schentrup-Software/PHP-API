<?php

namespace PhpApi\Interface;

use SchentrupSoftware\PhpApiSample\Model\Request\AbstractRequest;

interface IRequestMiddleware
{
    public function handleRequest(AbstractRequest $request): AbstractRequest;
}