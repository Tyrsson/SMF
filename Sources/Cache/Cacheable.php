<?php

declare(strict_types=1);

namespace SMF\Cache;

use ReflectionClass;

trait Cacheable
{
	public function __serialize(): array
	{
		$current = new ReflectionClass($this);
		$curVars = $current->getDefaultProperties();
	}
}
