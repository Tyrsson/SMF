<?php

declare(strict_types=1);

namespace SMF\Container;

use Laminas\ServiceManager\Exception\InvalidServiceException;
use SMF\Container\ContainerException;

/**
 * Interface for a plugin manager
 *
 * A plugin manager is a specialized service locator used to create homogeneous objects
 *
 * @template InstanceType
 */
interface PluginManagerInterface extends ServiceLocatorInterface
{
    /**
     * Validate an instance
     *
     * @return void
     * @throws InvalidServiceException If created instance does not respect the
     *     constraint on type imposed by the plugin manager.
     * @throws ContainerException If any other error occurs.
     * @psalm-assert InstanceType $instance
     */
    public function validate(mixed $instance);
}
