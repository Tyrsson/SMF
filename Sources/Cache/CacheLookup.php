<?php

declare(strict_types=1);

namespace SMF\Cache;

use Psr\EventDispatcher\StoppableEventInterface;

class CacheLookup implements StoppableEventInterface
{
	/** @var mixed */
	private $value;

	/** @var array[] */
	private $callbacks = [];

	/**
	 *
	 * @param string $cacheKey
	 * @return void
	 */
	public function __construct(
		private string $cacheKey = ''
	) {
	}

	public function key(): string
	{
		return $this->getKey();
	}

	public function getKey(): string
	{
		return $this->cacheKey;
	}

	public function setValue($value): void
	{
		$this->value = $value;
	}

	/** @return mixed */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 *
	 * @param callable $callback
	 * @return void
	 */
	public function addCacheCallback(callable $callback): void
	{
		$this->callbacks[] = $callback;
	}

	/** @return bool */
	public function isPropagationStopped(): bool
	{
		return !is_null($this->value);
	}

}
