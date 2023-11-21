<?php

declare(strict_types=1);

namespace SMF\Factories;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use SMF\Forum;

final class ForumFactory
{
	public function __invoke(ContainerInterface $container): Forum
	{

		return new Forum($container->get(ServerRequestInterface::class), $container);
	}
}
