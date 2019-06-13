<?php

declare(strict_types=1);

namespace Wtf\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Wtf\Root;

/**
 * Add `__wtf_orm_filters` key into container with current filters, based on medoo WHERE syntax.
 *
 * @see http://dev.slimframework.com/docs/v4/concepts/middleware.html
 * @see https://medoo.in/api/where
 *
 * @example https://example.com?filter[created_at]=2017-05-05
 */
class Filters extends Root
{
    /**
     * @see https://www.slimframework.com/docs/concepts/middleware.html
     *
     * @param ServerRequest  $request PSR-7 request
     * @param RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $this->container->add('__wtf_orm_filters', $this->process($request));

        $response = $handler->handle($request);

        return $response;
    }

    /**
     * Process filters from request.
     *
     * @param ServerRequest $request
     *
     * @return array
     */
    protected function process(ServerRequest $request): array
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
