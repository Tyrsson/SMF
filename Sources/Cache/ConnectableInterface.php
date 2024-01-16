<?php

declare(strict_types=1);

namespace SMF\Cache;

interface ConnectableInterface
{
	/**
	 * Connects to the cache method. This defines our $key. If this fails, we return false, otherwise we return true.
	 *
	 * @return bool Whether or not the cache method was connected to.
	 */
	public function connect(): bool;

	/**
	 * Closes connections to the cache method.
	 *
	 * @return bool Whether the connections were closed.
	 */
	public function quit(): bool;
}
