<?php

declare(strict_types=1);

namespace SMF\Cache;

use SMF\Cache\Driver;
use SMF\Clock;
use SMF\Config;

use function class_exists;
use function is_string;

final class CacheFactory
{
	private static ?Cache $instance = null;

	/**
	 *
	 * @param DriverInterface|string class-string $override
	 * @param bool $fallback
	 * @return Cache|null
	 */
	public function __invoke(DriverInterface|string $override = null, bool $fallback = true): Cache|null
	{
		$enabled = (bool) min(max((int) Config::$cache_enable, 0), 3);
		$level   = min(max((int) Config::$cache_enable, 1), 3);

		/**
		 * If the cache is not enabled, step out now
		 * return null as to allow nullsafe operator usage in userland
		 */
		if (!$enabled) {
			return null;
		}

		/**
		 * if factory has been previously invoked and a cache instance created, return it
		 * unless we want to override it.
		 */
		if (
			isset(self::$instance)
			&& self::$instance instanceof DriverInterface
			&& $override === null
		) {
			return self::$instance;
		}

		// we have to know which are installed and supported
		$supported_drivers = Cache::detect();
		// override, its all we need, as long as its valid
		if ($override !== null) {
			if (
				$override instanceof DriverInterface
				&& is_array(get_class($override), $supported_drivers, true)
			) {
				self::$instance = new Cache($override, $level);
			}
			/**
			 * All conditions must be met
			 * None supported drivers will not be returned
			 */
			if (
				is_string($override)
				&& class_exists($override, false)
				&& in_array($override, $supported_drivers, true)
			) {
				self::$instance = new Cache(new $override(), $level);
			}

			return self::$instance;
		}

		// todo: insure we are being returned a class-string string
		if (
			$override === null
			&& isset(Config::$cache_accelerator)
			&& class_exists(Config::$cache_accelerator, false)
			&& in_array(Config::$cache_accelerator, $supported_drivers, true)
		) {
			self::$instance = new Cache(new Config::$cache_accelerator(), $level);
		}

		// if we do not have a cache by now, and fallback in true, kick it off
		if ($fallback && !self::$instance instanceof Cache) {
			self::$instance = new Cache(new Cache::$default_driver(), $level);
		}
		return self::$instance;
	}
}
