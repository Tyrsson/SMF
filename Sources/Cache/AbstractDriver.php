<?php

declare(strict_types=1);

namespace SMF\Cache;

use SMF\Cache\DriverInterface;

abstract class AbstractDriver implements DriverInterface
{
	protected string $prefix;
	// set the SMF default ttl
	protected ?int $ttl = 120;

	public function isCacheableValue($value): bool
	{
		if (is_array($value)) {
			return true;
		}
		if (is_string($value)) {
			return true;
		}
		if (is_object($value)) {
			return true;
		}
		if (is_numeric($value)) {
			return true;
		}
		if (is_bool($value)) {
			return true;
		}
		if ($value === null) {
			return true;
		}
		return false;
	}
}
