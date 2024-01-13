<?php

declare(strict_types=1);

namespace SMF\Events;

use League\Event\HasEventName;
use SMF\Cache\CacheableInterface;

class AbstractCacheEvent implements HasEventName
{

	public function __construct(
		private string $name,
		private int $ttl = 120,
		private ?CacheableInterface $target,
	) {

	}
	public function eventName(): string
	{

	}
}
