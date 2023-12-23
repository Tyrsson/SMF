<?php

declare(strict_types=1);

namespace SMF\Middleware\Factories;

use Psr\Container\ContainerInterface;
use SMF\Middleware\BoardIndex;
use SMF\Theme;

final class BoardIndexFactory
{
	public function __invoke(ContainerInterface $container): BoardIndex
	{
		return new BoardIndex($container->get(Theme::class));
	}
}
