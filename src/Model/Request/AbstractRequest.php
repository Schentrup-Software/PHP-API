<?php

namespace PhpApi\Model\Request;

use Sapien\Request;

abstract class AbstractRequest
{
    private Request $request;

    final public function getRequest(): Request
    {
        return $this->request;
    }

    final public function setRequest(Request $request): void
    {
        $this->request = $request;
    }
}
