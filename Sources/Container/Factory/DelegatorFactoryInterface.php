<?php

declare(strict_types=1);

namespace SMF\Container\Factory;

use SMF\Container\Exception\ServiceNotCreatedException;
use SMF\Container\Exception\ServiceNotFoundException;
use SMF\Container\ContainerException;
use SMF\Container\ContainerInterface;

/**
 * Delegator factory interface.
 *
 * Defines the capabilities required by a delegator factory. Delegator
 * factories are used to either decorate a service instance, or to allow
 * decorating the instantiation of a service instance (for instance, to
 * provide optional dependencies via setters, etc.).
 */
interface DelegatorFactoryInterface
{
    /**
     * A factory that creates delegates of a given service
     *
     * @param  string                $name
     * @psalm-param callable():mixed $callback
     * @param  null|array<mixed>     $options
     * @return object
     * @throws ServiceNotFoundException If unable to resolve the service.
     * @throws ServiceNotCreatedException If an exception is raised when creating a service.
     * @throws ContainerException If any other error occurs.
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, ?array $options = null);
}
