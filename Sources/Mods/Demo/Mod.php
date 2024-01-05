<?php

declare(strict_types=1);

namespace SMF\Mods\Demo;

use League\Event\EventDispatcher;
use League\Event\ListenerPriority;
use League\Event\ListenerRegistry;
use SMF\Mods\Modification;

final class Mod implements Modification
{

	public function __construct(
		private EventDispatcher $eventDispatcher,
		private ListenerRegistry $listenerRegistry,
	) {
	}

	public function __invoke()
	{
		$this->eventDispatcher->subscribeListenersFrom($this);
	}

	protected function getListenerConfig(): array
	{
		return [
			[
				'event'    => 'add.action',
				'listener' => AddActionListener::class,
				'priority' => ListenerPriority::NORMAL,
			],
			[
				'event'    => 'add.bbc',
				'listener' => AddBbcListener::class,
				'priority' => ListenerPriority::HIGH,
			],
		];
	}

	public function subscribeListeners(ListenerRegistry $acceptor): void
	{
		foreach ($this->getListenerConfig() as $listener) {
			$acceptor->subscribeTo(
				$listener['event'],
				new $listener['listener'],
				$listener['priority']
			);
		}
	}
}
