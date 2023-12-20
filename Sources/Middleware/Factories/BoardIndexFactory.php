<?php

declare(strict_types=1);

namespace SMF\Middleware\Factories;

use Psr\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;
use SMF\Middleware\BoardIndex;

final class BoardIndexFactory
{
	public function __invoke(ContainerInterface $container): BoardIndex
	{
		return new BoardIndex($container->get(TemplateRendererInterface::class));
	}
}
