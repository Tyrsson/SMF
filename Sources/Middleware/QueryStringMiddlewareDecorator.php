<?php

declare(strict_types=1);

namespace SMF\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function rtrim;
use function stripos;
use function strlen;
use function strpos;
use function substr;

final class QueryStringMiddlewareDecorator implements MiddlewareInterface
{
    private MiddlewareInterface $middleware;

    /** @var array $params for which to execute for.  */
    private array $params;

    public function __construct(array $params, MiddlewareInterface $middleware)
    {
        // todo: normalize these values
        $this->params = \array_flip($params);
        $this->middleware = $middleware;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //$path = $request->getUri()->getPath() ?: '/';
        $params = \array_intersect_key($request->getQueryParams(), $this->params);

        // if we do not have the same param count, then were in the wrong place.
        if (\array_keys($params) !== \array_keys($this->params)) {
            return $handler->handle($request);
        }

        // Process our middleware.
        return $this->middleware->process(
            $request,
            $handler
        );
    }
}
