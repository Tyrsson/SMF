<?php

declare(strict_types=1);

namespace SMF\Cache;

interface CacheableInterface
{
	public function __serialize(): array;
	public function __unserialize(array $data): void;
}
