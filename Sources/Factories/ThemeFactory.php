<?php

declare(strict_types=1);

namespace SMF\Factories;

use Psr\Container\ContainerInterface;
use SMF\Theme;

final class ThemeFactory
{
	public function __invoke(ContainerInterface $container): Theme
	{
		return new Theme();
	}
}
