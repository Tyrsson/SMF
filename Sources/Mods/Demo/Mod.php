<?php

declare(strict_types=1);

namespace SMF\Mods\Demo;

use League\Event\EventDispatcher;
use League\Event\ListenerPriority;
use League\Event\ListenerRegistry;
use SMF\BBCodeParser;
use SMF\Editor;
use SMF\Mods\AbstractMod;

final class Mod extends AbstractMod
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

	/** @inheritDoc */
	public function getListenerConfig(): array
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
}
