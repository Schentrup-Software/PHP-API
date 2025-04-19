<?php

namespace PhpApiSample\Routes;

use PhpApi\Model\Request\AbstractRequest;
use PhpApi\Model\Request\Attribute\CookieRequestParam;
use PhpApi\Model\Request\Attribute\HeaderRequestParam;
use PhpApi\Model\Response\AbstractJsonResponse;
use PhpApi\Swagger\Attribute\SwaggerTag;

#[SwaggerTag(name: 'Post', description: 'Post example')]
class Post
{
    public function execute(PostRequest $request): PostResponse
    {
        return new PostResponse(message: json_encode($request));
    }
}

class PostRequest extends AbstractRequest
{
    public function __construct(
        #[HeaderRequestParam(name: 'X-My-Authorization')]
        public readonly string $authorization,
        public readonly string $someVar,
        public readonly string $someMessage,
        public readonly PostRequestSubObject $subObject,
    ) {
    }
}

class PostRequestSubObject
{
    public function __construct(
        public readonly string $subMessage,
        public readonly int $subID = 156,
    ) {
    }
}

class PostResponse extends AbstractJsonResponse
{
    public const ResponseCode = 200;

    public function __construct(
        public string $message = 'Resource created successfully',
        public int $createdId = 0,
    ) {
    }
}
