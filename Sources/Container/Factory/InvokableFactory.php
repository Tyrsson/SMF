<?php

declare(strict_types=1);

/**
 * @credit Laminas
 */

namespace SMF\Container\Factory;

use SMF\Container\ContainerInterface;

final class InvokableFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requested_name, ?array $options = null)
	{
		return null  === $options ? new $requested_name() : new $requested_name($options);
	}
}
