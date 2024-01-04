<?php

declare(strict_types=1);

namespace SMF\Events;

use League\Event\EventDispatcher;
use League\Event\PrioritizedListenerRegistry;

final class DispatcherFactory
{
	private static EventDispatcher $dispatcher;

	public function __invoke(): EventDispatcher
	{
		if (!isset(static::$dispatcher)) {
			static::$dispatcher = new EventDispatcher();
		}
		return static::$dispatcher;
	}
}
