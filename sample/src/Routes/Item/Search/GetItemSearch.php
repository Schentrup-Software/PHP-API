<?php

namespace PhpApiSample\Routes\Item\Search;

use PhpApi\Model\Request\AbstractRequest;
use PhpApi\Model\Response\AbstractJsonResponse;
use PhpApi\Model\Request\Attribute\QueryParam;
use PhpApi\Swagger\Attribute\SwaggerDescription;
use PhpApi\Swagger\Attribute\SwaggerSummary;
use PhpApi\Swagger\Attribute\SwaggerTag;

#[SwaggerTag(name: 'Search', description: 'Search resources')]
class GetItemSearch
{
    #[SwaggerSummary('Search resources with pagination')]
    #[SwaggerDescription('Retrieve resources with filtering, sorting and pagination options')]
    public function execute(SearchRequest $request): SearchResponse
    {
        // Simulated search results
        $items = [];
        $total = min($request->limit * 5, 100); // Fake total count

        for ($i = 0; $i < min($request->limit, $total - $request->offset); $i++) {
            $items[] = [
                'id' => $request->offset + $i + 1,
                'name' => 'Item ' . ($request->offset + $i + 1),
                'relevance' => mt_rand(50, 100) / 100
            ];
        }

        if ($request->sortBy === 'name') {
            usort($items, fn ($a, $b) => $a['name'] <=> $b['name']);
        }

        if ($request->sortDirection === 'desc') {
            $items = array_reverse($items);
        }

        return new SearchResponse(
            items: $items,
            total: $total,
            page: floor($request->offset / $request->limit) + 1,
            query: $request->query
        );
    }
}

class SearchRequest extends AbstractRequest
{
    public function __construct(
        #[QueryParam]
        public string $query = '',
        #[QueryParam]
        public int $offset = 0,
        #[QueryParam]
        public int $limit = 10,
        #[QueryParam]
        public string $sortBy = 'id',
        #[QueryParam]
        public string $sortDirection = 'asc'
    ) {
    }
}

class SearchResponse extends AbstractJsonResponse
{
    public const ResponseCode = 200;

    public function __construct(
        public array $items = [],
        public int $total = 0,
        public int $page = 1,
        public string $query = '',
        public ?int $timestamp = null
    ) {
        $this->timestamp = time();
    }
}
