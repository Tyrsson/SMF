<?php

declare(strict_types=1);

namespace SMF\PsrCache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use SMF\Cache\CacheApi;

final class CacheItemPool implements CacheItemPoolInterface
{

	public function __construct(
	) {
		CacheApi::load();
	}

	/** @inheritDoc */
	public function getItem(string $key)
	{

	}

	/** @inheritDoc */
	public function getItems(array $keys = [])
	{

	}

	/** @inheritDoc */
	public function hasItem(string $key)
	{

	}

	/** @inheritDoc */
	public function clear()
	{

	}

	/** @inheritDoc */
	public function deleteItem(string $key)
	{

	}

	/** @inheritDoc */
	public function deleteItems(array $keys)
	{

	}

	/** @inheritDoc */
	public function save(CacheItemInterface $item)
	{

	}

	/** @inheritDoc */
	public function saveDeferred(CacheItemInterface $item)
	{

	}

	/** @inheritDoc */
	public function commit()
	{

	}

}
