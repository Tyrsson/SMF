<?php

declare(strict_types=1);

namespace SMF\Cache;

use SMF\Cache\DriverInterface;
use SMF\Config;

use function is_writable;
use function substr;
use function strrpos;
use function touch;

abstract class AbstractDriver implements DriverInterface
{
	protected string $prefix;
	// set the SMF default ttl
	protected ?int $ttl = 120;
	// used by CacheDirectoryAwareInterface
	private ?string $cachedir;

	public function setPrefix(string $prefix): void
	{
		$this->prefix = $prefix;
	}

	public function getPrefix(): string
	{
		return $this->prefix;
	}

	/** @inheritDoc */
	public function setTtl(int $ttl = 120): void
	{
		$this->ttl = $ttl;
	}

	/** @inheritDoc */
	public function getTtl(): ?int
	{
		return $this->ttl;
	}

	/**
	 * Invalidate all cached data.
	 *
	 * @return bool Whether or not we could invalidate the cache.
	 */
	public function invalidateCache(): bool
	{
		// Invalidate cache, to be sure!
		// ... as long as index.php can be modified, anyway.
		if (is_writable(Config::$cachedir . '/' . 'index.php')) {
			@touch(Config::$cachedir . '/' . 'index.php');
		}

		return true;
	}

	/**
	 * Gets the class name identifier of the current driver.
	 *
	 * @return string the Driver class name.
	 */
	final public function getDriverClassName(): string
	{
		$class_name = static::class;

		if ($position = strrpos($class_name, '\\')) {
			return substr($class_name, $position + 1);
		}

		return $class_name;
	}

	/**
	 *
	 * @param mixed $value
	 * @return bool
	 */
	final public function isCacheableValue($value): bool
	{
		if (is_array($value)) {
			return true;
		}
		if (is_string($value)) {
			return true;
		}
		if (is_object($value)) {
			return true;
		}
		if (is_numeric($value)) {
			return true;
		}
		if (is_bool($value)) {
			return true;
		}
		if ($value === null) {
			return true;
		}
		return false;
	}
}
