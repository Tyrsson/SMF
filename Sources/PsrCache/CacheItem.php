<?php

declare(strict_types=1);

namespace SMF\PsrCache;

use DateTimeInterface;
use DateInterval;
use DateTimeZone;
use InvalidArgumentException;
use Psr\Cache\CacheItemInterface;
use Psr\Clock\ClockInterface;
use SMF\User;

use function is_int;
use function sprintf;

class CacheItem implements CacheItemInterface
{
	private ?int $expiration = null;

	private ClockInterface $clock;

	/**
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param bool $isHit
	 * @param null|ClockInterface $clock
	 * @return void
	 */
	public function __construct(
		private string $key,
		private mixed $value,
		private bool $isHit,
		?ClockInterface $clock = null
	) {
		$this->value = $isHit ? $value :null;
		$this->clock ??= new Clock(new DateTimeZone(User::getTimezone()));
	}

	/** @inheritDoc */
	public function getKey()
	{
		return $this->key;
	}

	/** @inheritDoc */
	public function get()
	{
		return $this->value;
	}

	/** @inheritDoc */
	public function isHit()
	{
		if (!$this->isHit) {
			return false;
		}

		$ttl = $this->getTtl();
		return $ttl === null || $ttl > 0;
	}

	/** @inheritDoc */
	public function set(mixed $value)
	{
		$this->value = $value;

		return $this;
	}

	/** @inheritDoc */
	public function expiresAt(?DateTimeInterface $expiration)
	{
		if (! ($expiration === null || $expiration instanceof DateTimeInterface)) {
			throw new InvalidArgumentException('$expiration must be null or an instance of DateTimeInterface');
		}

		$this->expiration = $expiration instanceof DateTimeInterface ? $expiration->getTimestamp() : null;

		return $this;
	}

	/** @inheritDoc */
	public function expiresAfter(int|DateInterval|null $time)
	{
		if ($time === null) {
			return $this->expiresAt(null);
		}

		if (is_int($time)) {
			$interval = DateInterval::createFromDateString(sprintf('%d', $time));
			if (!$interval) {
				throw new InvalidArgumentException(sprintf('TTL "%d" is not supported.', $time));
			}

			$time = $interval;
		}

		$now = $this->clock->now();
		return $this->expiresAt($now->add($time));
	}

	/**
	 *
	 * @return null|int
	 */
	public function getTtl()
	{
		if ($this->expiration === null) {
			return null;
		}

		$now = $this->clock->now();

		return $this->expiration - $now->getTimestamp();
	}

}
