<?php

declare(strict_types=1);

/**
 * @credit laminas
 */

namespace SMF\Container\Factory;

use SMF\Container\ContainerInterface;

interface FactoryInterface
{
	public function __invoke(ContainerInterface $container, $requested_name, ?array $options = null);
}
