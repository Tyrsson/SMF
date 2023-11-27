<?php

declare(strict_types=1);

namespace SMF\Middleware;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use SMF\Forum;
use SMF\Utils;

final class FallbackMiddleware implements MiddlewareInterface
{
	public function __construct(
		private Forum $forum
	) {
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$this->forum->main();
		Utils::obExit(null, null, true);
		return new HtmlResponse(\ob_get_clean());
	}
}
