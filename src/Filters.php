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
        $filters = $request->getQueryParam('filter') ?? [];
        $limit = [0, 100];

        // Prepare limit and offset
        if ($request->getQueryParam('offset')) {
            $limit[0] = $filters['offset'];
        }
        if ($request->getQueryParam('limit')) {
            $limit[1] = $filters['limit'];
        }

        // Fix filter symbols issue (like missing "]")
        foreach ($filters as $field => $value) {
            if ('null' === $value) {
                $filters[$field][$key] = null;
            }
            if (strpos($field, '[')) {
                $filters[$field.']'] = $value;
                unset($filters[$field]);
            }
        }

        $filters['LIMIT'] = $limit;

        return $filters;
    }
}
