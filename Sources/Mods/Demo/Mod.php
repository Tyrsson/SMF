<?php

declare(strict_types=1);

namespace SMF\Mods\Demo;

use League\Event\EventDispatcher;
use League\Event\ListenerRegistry;
use SMF\Mods\Modification;

final class Mod implements Modification
{

	public function __construct(private EventDispatcher $eventDispatcher)
	{

	}

	public function __invoke()
	{

	}

	public function init(): array
	{

	}

	public function subscribeListeners(ListenerRegistry $acceptor): void
	{

	}
}
