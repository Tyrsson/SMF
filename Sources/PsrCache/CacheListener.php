<?php

declare(strict_types=1);

namespace SMF\PsrCache;

use Psr\Cache\CacheItemPoolInterface;

final class CacheListener
{

	public function __construct(
		private CacheItemPoolInterface $pool,
		private \DateInterval $ttl
	) {
	}

	public function __invoke(CacheLookup $event): void
	{
		$key = $event->getKey();

		$item = $this->pool->getItem($key);
		if ($item->isHit()) {
			$event->setValue($item->get());
		} else {
			$event->addCacheCallback(function($value) use ($item) {
				$item->set($value)->expiresAfter($this->ttl);
				$this->pool->save($item);
			});
		}
	}
}
