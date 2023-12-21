<?php

declare(strict_types=1);

namespace SMF\Factories;

use Laminas\HttpHandlerRunner\RequestHandlerRunnerInterface;
use Mezzio\ApplicationPipeline;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\RouteCollector;
use Mezzio\Router\RouteCollectorInterface;
use Psr\Container\ContainerInterface;
use SMF\Forum;

final class ForumFactory
{
	public function __invoke(ContainerInterface $container): Forum
	{

		return new Forum(
			$container,
			$container->get(MiddlewareFactory::class),
			$container->get(ApplicationPipeline::class),
			$container->has(RouteCollectorInterface::class) ?
			$container->get(RouteCollectorInterface::class) :
			$container->get(RouteCollector::class),
			$container->get(RequestHandlerRunnerInterface::class));
	}
}
