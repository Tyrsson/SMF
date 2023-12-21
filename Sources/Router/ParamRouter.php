<?php

declare(strict_types=1);

namespace SMF\Router;

use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ParamRouter implements RouterInterface
{

    public function addRoute(Route $route): void { }

    public function match(ServerRequestInterface $request): RouteResult
	{

	}

    public function generateUri(string $name, array $substitutions = [], array $options = []): string { }

}
