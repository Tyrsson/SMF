<?php

declare(strict_types=1);

namespace SMF\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use SMF\Middleware\QueryStringMiddlewareDecorator;

/**
 * Convenience function for creating path-segregated middleware.
 *
 * Usage:
 *
 * <code>
 * use function Laminas\Stratigility\path;
 *
 * $pipeline->pipe(path('/foo', $middleware));
 * </code>
 */
function params(array $params, MiddlewareInterface $middleware): QueryStringMiddlewareDecorator
{
    return new QueryStringMiddlewareDecorator($params, $middleware);
}
