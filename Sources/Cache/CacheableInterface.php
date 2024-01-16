<?php

declare(strict_types=1);

namespace SMF\Cache;

interface CacheableInterface
{
	/**
	 *
	 * @link https://wiki.php.net/rfc/custom_object_serialization
	 */
	public function __serialize(): array;
	public function __unserialize(array $data): void;
}
