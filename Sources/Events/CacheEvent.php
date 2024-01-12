<?php

declare(strict_types=1);

namespace SMF\Events;

use League\Event\HasEventName;

class CacheEvent implements HasEventName
{
	private string $name;
	private object $target;
	private int $ttl;

    public function eventName(): string
	{
		return
	}
}
