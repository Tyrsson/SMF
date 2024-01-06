<?php

declare(strict_types=1);

namespace SMF\Events;

use League\Event\ListenerRegistry;
use League\Event\PrioritizedListenerRegistry;

final class ListenerRegistryFactory
{
	private static PrioritizedListenerRegistry $registry;

	public function __invoke(): ListenerRegistry
	{
		if (!isset(static::$registry)) {
			static::$registry = new PrioritizedListenerRegistry();
		}
		return static::$registry;
	}
}
