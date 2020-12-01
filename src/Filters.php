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
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $this->container['filters'] = $this->process($request);

        return $next($request, $response);
    }

    /**
     * Fix brackets in field names.
     *
     * @param string $field Field name, eg: user_name[~
     * @param mixed  $value Anything (including arrays
     *
     * @return array [$field, $value], eg: ["user_name[~]", ["user1", "user2", "user3"]
     */
    protected function fixBrackets(string $field, $value): array
    {
        if (\is_array($value)) {
            foreach ($value as $vKey => $vValue) {
                unset($value[$vKey]);
                [$fixedVKey, $fixedVValue] = $this->fixBrackets($vKey, $vValue);
                $value[$fixedVKey] = $fixedVValue;
            }
        }
        if ('null' === $value) {
            $value = null;
        }
        if (false !== \strpos($field, '[')) {
            $field = $field.']';
        }

        return [$field, $value];
    }

    /**
     * Process filters from request.
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
            unset($filters[$field]);
            [$field, $value] = $this->fixBrackets($field, $value);
            $filters[$field] = $value;
        }

        $filters['LIMIT'] = $limit;

        return $filters;
    }
}
