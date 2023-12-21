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
	private $isHandler = false;

	public function __construct(array $params, MiddlewareInterface $middleware)
	{
		if ($params !== [] && $params !== null) {
			$this->isHandler = true;
		}

		$this->middleware = $middleware;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		if ($this->isHandler) {

			$params = \array_intersect_key($request->getUri()->getQuery(), \array_flip($this->params));

			// if we do not have the same param count, then were in the wrong place.
			if ($params !== [] && \array_keys($params) !== \array_keys($this->params)) {
				return $handler->handle($request);
			}
		}

		// Process our middleware.
		return $this->middleware->process(
			$request,
			$handler
		);
	}
}
