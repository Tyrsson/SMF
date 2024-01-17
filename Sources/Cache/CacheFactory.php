<?php

declare(strict_types=1);

namespace SMF\Cache;

use SMF\Cache\Driver;
use SMF\Clock;
use SMF\Config;

use function class_exist;
use function is_string;

final class CacheFactory
{
	private static Cache $instance;

	/**
	 *
	 * @param DriverInterface|string class-string $override
	 * @param bool $fallback
	 * @return Cache|null
	 */
	public function __invoke(DriverInterface|string $override = null, bool $fallback = true): Cache|null
	{
		$targetDriver = null;
		$enabled = (bool) min(max((int) Config::$cache_enable, 0), 3);
		$level   = min(max((int) Config::$cache_enable, 1), 3);

		// step out now, no sense in going past here.
		if (!$enabled) {
			return null;
		}

		// override, its all we need, as long as its valid
		if ($override !== null) {
			if ($override instanceof DriverInterface) {
				self::$instance = new Cache($override);
			}
			// this takes a little verification, should be class-string format
			if (is_string($override)) {
				if (class_exists($override, true) && in_array($override, Cache::detect())) {
					self::$instance = new Cache(new $override());
				}
			}
		}

		// if we do not have a cache by now, and fallback in true, kick it off
		if ($fallback && !self::$instance instanceof Cache) {
			self::$instance = new Cache(new Cache::$default_driver());
		}
		return self::$instance;
	}
}
