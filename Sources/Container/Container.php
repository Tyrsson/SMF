<?php

declare(strict_types=1);

namespace SMF\Container;

use Exception as SplException;
use SMF\Container\ContainerInterface;
use SMF\Container\Exception;
use SMF\Container\Factory;

use function sprintf;

class Container implements ContainerInterface
{
	/**
	 *
	 * @var string[]
	 */
	protected $aliases = [];
	/**
	 *
	 * @var bool
	 */
	 protected $configured = false;

	/**
	 * factories (string name or callable)
	 *
	 * @var string[]|callable[]
	 */
	protected $factories = [];

	/**
	 *
	 * @var array<string,array|object>
	 */
	protected array $services = [];

	/**
	 *
	 * @var ContainerInterface
	 */
	protected ContainerInterface $creation_context;

	public function __construct(array $config = [])
	{
		$this->creation_context = $this;
		$this->configure($config);
	}

	/**
	 *
	 * @param string $id
	 * @return mixed
	 */
	public function get($name)
	{
		// We start by checking if we have cached the requested service;
		// this is the fastest method.
		if (isset($this->services[$name])) {
			return $this->services[$name];
		}

		// If we do not have aliases, skip it for performance
		if (! $this->aliases) {
			$object = $this->doCreate($name);
			// cache all objects
			$this->services[$name] = $object;
			// return the object
			return $object;
		}

		// We now deal with requests which may be aliases.
		$resolvedName = $this->aliases[$name] ?? $name;

		// At this point, we have to create the object.
		// We use the resolved name for that.
		$object = $this->doCreate($resolvedName);

		// cache the resolved alias
		$this->services[$resolvedName] = $object;

		return $object;
	}

	/**
	 * @param string|class-string $name
	 * @return bool
	 */
	public function has(string $name): bool
	{
		return isset($this->services[$name]);
	}

	/**
	 * Get an non cached, non shared instance
	 * @param mixed $name
	 * @param null|array $options
	 * @return mixed
	 */
	public function build($name, ?array $options = null)
	{
		return $this->doCreate($name, $options);
	}

	public function configure(array $config): self
	{
		if (isset($config['services']) && ! empty($config['services'])) {
			$this->services = $config['services'] + $this->services;
		}

		if (isset($config['invokables']) && ! empty($config['invokables'])) {
			$aliases = $this->wireInvokableFactories($config['invokables']);
			$config['aliases'] = $aliases + ($config['aliases'] ?? []);
		}

		if (isset($config['factories'])) {
			$this->factories = $config['factories'] + $this->factories;
		}

		if (! empty($config['aliases'])) {
			$this->aliases = $config['aliases'] + $this->aliases;
			$this->mapAliasesToTargets();
		} elseif (! $this->configured && ! empty($this->aliases)) {
			$this->mapAliasesToTargets();
		}
		$this->configured = true;
		return $this;
	}

	public function setFactory($name, $factory): void
	{
		if (isset($this->services[$name])) {
			throw Exception\ContainerModificationsNotAllowedException::fromExistingService($name);
		}

		$this->factories[$name] = $factory;
	}
	/**
	 * Get a factory for the service
	 * @throws ServiceNotFoundException
	 */
	private function getFactory(string $name): callable
	{
		$factory = $this->factories[$name] ?? null;

		$lazyLoaded = false;
		if (is_string($factory) && class_exists($factory)) {
			$factory    = new $factory();
			$lazyLoaded = true;
		}

		// did we map the service to itself?
		if (is_callable($factory)) {
			if ($lazyLoaded) {
				$this->factories[$name] = $factory;
			}

			return $factory;
		}

		throw new Exception\ServiceNotFoundException(sprintf(
			'Unable to resolve service "%s" to a factory; are you certain you provided it during configuration?',
			$name
		));
	}

	public function setService($name, $service): void
	{
		if (isset($this->services[$name])) {
			throw Exception\ContainerModificationsNotAllowedException::fromExistingService($name);
		}
		$this->services[$name] = $service;
	}

	private function doCreate(string $resolved_name, ?array $options = null)
	{
		try {
			$factory = $this->getFactory($resolved_name);
			$object  = $factory();
		} catch (SplException $exception) {
			throw new Exception\ServiceNotFoundException(
				sprintf(
					'Service with name "%s" could not be created. Reason: %s',
					$resolved_name,
					$exception->getMessage(),
				), (int) $exception->getCode(), $exception
			);
		}
		return $object;
	}

	/**
	 * Determine if a service for any name provided by a service
	 * manager configuration(services, aliases, factories, ...)
	 * already exists, and if it exists, determine if is it allowed
	 * to get overriden.
	 *
	 * Validation in the context of this class means, that for
	 * a given service name we do not have a service instance
	 * in the cache OR override is explicitly allowed.
	 *
	 * @psalm-param ServiceManagerConfiguration $config
	 * @throws ContainerModificationsNotAllowedException If any
	 *     service key is invalid.
	 */
	private function validateServiceNames(array $config): void
	{
		if (! $this->configured) {
			return;
		}

		if (isset($config['services'])) {
			foreach (array_keys($config['services']) as $service) {
				if (isset($this->services[$service])) {
					throw Exception\ContainerModificationsNotAllowedException::fromExistingService($service);
				}
			}
		}

		if (isset($config['aliases'])) {
			foreach (array_keys($config['aliases']) as $service) {
				if (isset($this->services[$service])) {
					throw Exception\ContainerModificationsNotAllowedException::fromExistingService($service);
				}
			}
		}

		if (isset($config['invokables'])) {
			foreach (array_keys($config['invokables']) as $service) {
				if (isset($this->services[$service])) {
					throw Exception\ContainerModificationsNotAllowedException::fromExistingService($service);
				}
			}
		}

		if (isset($config['factories'])) {
			foreach (array_keys($config['factories']) as $service) {
				if (isset($this->services[$service])) {
					throw Exception\ContainerModificationsNotAllowedException::fromExistingService($service);
				}
			}
		}
	}

	public function setInvokableClass($name, $class = null)
	{
		if (isset($this->services[$name])) {
			throw Exception\ContainerModificationsNotAllowedException::fromExistingService($name);
		}

		$this->wireInvokableFactories([$name => $class ?? $name]);
	}

	private function wireInvokableFactories(array $invokables): array
	{
		$aliases = [];

		foreach ($invokables as $name => $class) {
			$this->factories[$class] = Factory\InvokableFactory::class;
			if ($name !== $class) {
				$this->aliases[$name] = $class;
				$aliases[$name]       = $class;
			}
		}

		return $aliases;
	}

	/**
	 * Assuming that the alias name is valid (see above) resolve/add it.
	 *
	 * This is done differently from bulk mapping aliases for performance reasons, as the
	 * algorithms for mapping a single item efficiently are different from those of mapping
	 * many.
	 */
	private function mapAliasToTarget(string $alias, string $target): void
	{
		// $target is either an alias or something else
		// if it is an alias, resolve it
		$this->aliases[$alias] = $this->aliases[$target] ?? $target;

		// a self-referencing alias indicates a cycle
		if ($alias === $this->aliases[$alias]) {
			throw Exception\CyclicAliasException::fromCyclicAlias($alias, $this->aliases);
		}

		// finally we have to check if existing incomplete alias definitions
		// exist which can get resolved by the new alias
		if (in_array($alias, $this->aliases)) {
			$r = array_intersect($this->aliases, [$alias]);
			// found some, resolve them
			foreach ($r as $name => $service) {
				$this->aliases[$name] = $target;
			}
		}
	}

	private function mapAliasesToTargets(): void
	{
		$tagged = [];
		foreach ($this->aliases as $alias => $target) {
			if (isset($tagged[$alias])) {
				continue;
			}

			$tCursor = $this->aliases[$alias];
			$aCursor = $alias;
			if ($aCursor === $tCursor) {
				throw Exception\CyclicAliasException::fromCyclicAlias($alias, $this->aliases);
			}
			if (! isset($this->aliases[$tCursor])) {
				continue;
			}

			$stack = [];

			while (isset($this->aliases[$tCursor])) {
				$stack[] = $aCursor;
				if ($aCursor === $this->aliases[$tCursor]) {
					throw Exception\CyclicAliasException::fromCyclicAlias($alias, $this->aliases);
				}
				$aCursor = $tCursor;
				$tCursor = $this->aliases[$tCursor];
			}

			$tagged[$aCursor] = true;

			foreach ($stack as $alias) {
				if ($alias === $tCursor) {
					throw Exception\CyclicAliasException::fromCyclicAlias($alias, $this->aliases);
				}
				$this->aliases[$alias] = $tCursor;
				$tagged[$alias]        = true;
			}
		}
	}
}
