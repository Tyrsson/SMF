<?php

declare(strict_types=1);

namespace SMF\Cache;

use DateInterval;
use ReflectionClass;
use ReflectionProperty;

trait CacheableTrait
{
	public null|int|DateInterval $expiration;
	public mixed $value;

	public function __serialize(): array
	{
		$reflection = new ReflectionClass(static::class);
		$properties = $reflection->getProperties(
			ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE
		);

		foreach($properties as $property) {
			$data[$property->getName()] = $property->getValue($this);
		}

		return $data;
	}

	public function __unserialize(array $data): void
	{
		foreach ($data as $name => $value) {
			$this->{$name} = $value;
		}
	}
}
