<?php

declare(strict_types=1);

namespace SMF\PsrCache;

use SMF\Events\Event;

class CacheLookup extends Event
{

	/** @var array[] */
	private $callbacks = [];

	public function key(): string
	{
		return $this->getKey();
	}

	public function getKey(): string
	{
		return $this->getParam('cache_key');
	}

	public function setValue($value): void
	{
		$this->setParam('value', $value);
	}

	/** @inheritDoc */
	public function getValue()
	{
		return $this->getParam('value');
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
		return !is_null($this->getParam('value'));
	}

}
