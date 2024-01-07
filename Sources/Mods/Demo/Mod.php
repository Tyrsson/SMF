<?php

declare(strict_types=1);

namespace SMF\Mods\Demo;

use League\Event\EventDispatcher;
use League\Event\ListenerPriority;
use League\Event\ListenerRegistry;
use SMF\BBCodeParser;
use SMF\Editor;
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
				'event'    => [BBCodeParser::BBC_CODE_EVENT, Editor::BBC_BUTTON_EVENT],
				'listener' => AddBbcListener::class,
				'priority' => ListenerPriority::HIGH,
			],
		];
	}

	public function subscribeListeners(ListenerRegistry $acceptor): void
	{
		foreach ($this->getListenerConfig() as $listener) {
			if (\is_array($listener['event'])) {
				for ($i=0; $i < count($listener['event']); $i++) {
					$acceptor->subscribeTo(
						$listener['event'][$i],
						new $listener['listener'],
						$listener['priority']
					);
					continue;
				}
			} else {
				$acceptor->subscribeTo(
					$listener['event'],
					new $listener['listener'],
					$listener['priority']
				);
			}
		}
	}
}
