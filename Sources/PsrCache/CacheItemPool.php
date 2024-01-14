<?php

declare(strict_types=1);

namespace SMF\PsrCache;

use DateTimeZone;
use InvalidArgumentException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Clock\ClockInterface;
use SMF\Cache\CacheApi;
use SMF\User;

final class CacheItemPool implements CacheItemPoolInterface
{

	/** @var array<string,CacheItem> */
	private array $deferred = [];

	private ClockInterface $clock;

	public function __construct(
		private readonly StorageInterface $storage,
		?ClockInterface $clock = null
	) {
		$this->clock = $clock ??= new Clock(new DateTimeZone(User::getTimezone()));
	}

	/** @inheritDoc */
	public function getItem(string $key)
	{
		if (! $this->hasDeferredItem($key)) {
			$value = null;
			$isHit = false;
			try {
				$value = $this->storage->getItem($key, $isHit);
			} catch (InvalidArgumentException $e) {
				//throw $th;
			}
			return new CacheItem($key, $value, $isHit ?? false, $this->clock);
		}

		return clone $this->deferred[$key];
	}

	/** @inheritDoc */
	public function getItems(array $keys = [])
	{
		$items = [];

		// first things first
		foreach ($keys as $key) {
			if ($this->hasDeferredItem($key)) {
				// kill the reference to deferred items blah yada
				$items[$key] = clone $this->deferred[$key];
			}
		}

		$keys = array_diff($keys, array_keys($items));

		try {
			$cacheItems = $this->storage->getItems($keys);
		} catch (InvalidArgumentException $e) {
			throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
		} catch (\Exception) {
			$cacheItems = [];
		}

		foreach ($cacheItems as $key => $value) {
			assert(\is_string($key));
			$items[$key] = new CacheItem($key, $value, true, $this->clock);
		}

		foreach (\array_diff($keys, \array_keys($cacheItems)) as $key) {
			$items[$key] = new CacheItem($key, null, false, $this->clock);
		}

		return $items;
	}

	/** @inheritDoc */
	public function hasItem(string $key)
	{
		if ($this->hasDeferredItem($key)) {
			return true;
		}

		try {
			return $this->storage->hasItem($key);
		} catch (InvalidArgumentException $e) {
			throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
		} catch (\Exception) {
			return false;
		}
	}

	/**
	 * If adding support for namespaces and prefixes, clear by those.
	 * Otherwise clear the entire cache
	 */
	public function clear(): bool
	{
		$this->deferred = [];

		try {
			// todo: implement support
			$options = $this->storage->getOptions();
			$ns = $options->getNamespace() ?? $options->getPrefix();
			if (
				$this->storage instanceof ClearByNamepaceInterface
				|| $this->storage instanceof ClearByPrefixInterface
			) {
				// todo: implement both interfaces to via FlushableInterface
				$cleared = $this->storage->flush();
			}
		} catch (\Exception) {
			$cleared = false;
		}

		return $cleared;
	}

	/** @inheritDoc */
	public function deleteItem(string $key)
	{
		return $this->deleteItem($key);
	}

	/** @inheritDoc */
	public function deleteItems(array $keys)
	{
		$this->deferred = \array_diff_key($this->deferred, \array_flip($keys));

		try {
			$deleted = $this->storage->deleteItems($keys);
		} catch (InvalidArgumentException $e) {
			throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
		} catch (\Exception) {
			return false;
		}

		if ($deleted === []) {
			return true;
		}

		$existing = $this->storage->hasItems($deleted);
		$unified = \array_unique($existing);
		return !\in_array(true, $unified, true);
	}

	/** @inheritDoc */
	public function save(CacheItemInterface $item)
	{
		if (!$item instanceof CacheItem) {
			throw new InvalidArgumentException('$item must be an instance of '. CacheItem::class);
		}
		// todo: implement saveMultipleItems
		return $this->saveMultipleItems([$item], $item->getTtl()) === [];
	}

	/** @inheritDoc */
	public function saveDeferred(CacheItemInterface $item)
	{

	}

	/** @inheritDoc */
	public function commit()
	{

	}

	public function __destruct()
	{
		$this->commit();
	}

	/**
	 * If has deferred item for key and has not expired return true. Otherwise, return false.
	 * @param string $key
	 * @return bool
	 */
	private function hasDeferredItem(string $key): bool
	{
		if (isset($this->deferred[$key])) {
			$ttl = $this->deferred[$key]->getTtl();
			return $ttl === null || $ttl > 0;
		}
		return false;
	}

	private function saveMultipleItems(array $items, ?int $itemTtl): array
    {
        $keyItemPair = [];
        foreach ($items as $item) {
            $keyItemPair[$item->getKey()] = $item;
        }

        // delete expired item
        if ($itemTtl < 0) {
            $this->deleteItems(array_keys($keyItemPair));
            foreach ($keyItemPair as $cacheItem) {
                $cacheItem->setIsHit(false);
            }

            return $keyItemPair;
        }

        $options = $this->storage->getOptions();
        $ttl     = $options->getTtl();

        $keyValuePair = [];
        foreach ($items as $item) {
            $key                = $item->getKey();
            $keyValuePair[$key] = $item->get();
        }

        $options->setTtl($itemTtl ?? 0);

        try {
            $notSavedKeys = $this->storage->setItems($keyValuePair);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        } catch (\Exception) {
            $notSavedKeys = array_keys($keyValuePair);
        } finally {
            $options->setTtl($ttl);
        }

        $notSavedItems = [];
        foreach ($keyItemPair as $key => $item) {
            if (in_array($key, $notSavedKeys, true)) {
                $notSavedItems[$key] = $item;
                continue;
            }

            $item->setIsHit(true);
        }

        return $notSavedItems;
    }
}
