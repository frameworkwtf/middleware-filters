<?php

declare(strict_types=1);

namespace Wtf\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Wtf\Root;

/**
 * Add `filters` key into container with current filters.
 *
 * @see https://www.slimframework.com/docs/concepts/middleware.html
 *
 * @example https://example.com?filter[created_at]=2017-05-05
 */
class Filters extends Root
{
    /**
     * @see https://www.slimframework.com/docs/concepts/middleware.html
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $this->container['filters'] = $this->process($request);

        return $next($request, $response);
    }

    /**
     * Process filters from request.
     *
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    protected function process(ServerRequestInterface $request): array
    {
        $filters = $request->getQueryParams()['filter'] ?? [];
        $limit = [];

        // Prepare limit and offset
        $limit[0] = $request->getQueryParam('offset') ? $request->getQueryParam('offset') : 0;
        $limit[1] = $request->getQueryParam('limit') ? $request->getQueryParam('limit') : 100;

        // Fix filter symbols issue (like missing "]")
        foreach ($filters as $field => $value) {
            if ('null' === $value) {
                $filters[$field][$key] = null;
            }
            if (\strpos($field, '[')) {
                $filters[$field.']'] = $value;
                unset($filters[$field]);
            }
        }

        $filters['LIMIT'] = $limit;

        return $filters;
    }
}
