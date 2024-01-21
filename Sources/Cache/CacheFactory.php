<?php

declare(strict_types=1);

namespace SMF\Cache;

use Psr\SimpleCache\CacheInterface;
use SMF\Cache\Driver;
use SMF\Clock;
use SMF\Config;

use function class_exists;
use function is_string;

final class CacheFactory
{
	public static bool $enable = false;

	private static ?CacheInterface $instance = null;

	/**
	 *
	 * @param DriverInterface|string class-string $override
	 * @param bool $fallback
	 * @return Cache|null
	 */
	public function __invoke(DriverInterface|string $override = null, bool $fallback = true): Cache|null
	{
		Cache::$enable = (bool) Cache::$level = min(max((int) Config::$cache_enable, 0), 3);

		/**
		 * If the cache is not enabled, step out now
		 * return null as to allow nullsafe operator usage in userland
		 */
		if (!Cache::$enable) {
			return null;
		}

		/**
		 * if factory has been previously invoked and a cache instance created, return it
		 * unless we want to override it.
		 */
		if (
			isset(self::$instance)
			&& self::$instance instanceof CacheInterface
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
				self::$instance = new Cache($override);
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
				self::$instance = new Cache(new $override());
			}
			return self::$instance;
		}

		// todo: insure we are being returned a class-string string, currently its only class name
		$targetDriver = Cache::DRIVER_NAMESPACE . Config::$cache_accelerator;
		if (
			$override === null
			&& isset(Config::$cache_accelerator)
			&& class_exists($targetDriver, false)
			&& in_array(
				$targetDriver,
				$supported_drivers,
				true
			)
		) {
			self::$instance = new Cache(new $targetDriver());
		}

		// if we do not have a cache by now, and fallback in true, kick it off
		if ($fallback && !self::$instance instanceof Cache) {
			self::$instance = new Cache(new Cache::$default_driver());
		}
		return self::$instance;
	}
}
