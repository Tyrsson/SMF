<?php

declare(strict_types=1);

namespace SMF\Cache;

use SMF\Cache\Driver;
use SMF\Config;

final class CacheFactory
{
	private $instance;

	public function __construct()
	{

	}

	public function __invoke(?string $override = null, bool $fallback = true): Cache
	{

	}
}
