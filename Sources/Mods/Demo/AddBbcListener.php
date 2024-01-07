<?php

declare(strict_types=1);

namespace SMF\Mods\Demo;

use SMF\BBCodeParser;
use SMF\Editor;
use SMF\Events\Exception\UnknownEventException;
use SMF\Events\IntegrationEvent;

final class AddBbcListener
{
	private $event;
	private $target;

	public function __construct()
	{

	}

	public function __invoke(IntegrationEvent $event)
	{
		$this->event = $event;
		return match($event->eventName()) {
			BBCodeParser::BBC_CODE_EVENT => $this->addCode(),
			Editor::BBC_BUTTON_EVENT     => $this->addButton(),
			default => throw UnknownEventException::fromListener(static::class)
		};
	}

	protected function addCode()
	{
		/** @var BBCodeParser */
		$target = $this->event->getTarget();
		$target::$codes[] = [
			'tag' => 'indent-left',
			'before' => '<div style="margin-left: 3%;">',
			'after' => '</div>',
			'block_level' => true
		];
		$target::$codes[] = [
			'tag' => 'indent-right',
			'before' => '<div style="margin-right: 3%;">',
			'after' => '</div>',
			'block_level' => true
		];
		$target::$codes[] = [
			'tag' => 'indent-both',
			'before' => '<div style="margin: 0 3%;">',
			'after' => '</div>',
			'block_level' => true
		];
	}

	protected function addButton()
	{

	}
}
