<?php

declare(strict_types=1);

namespace SMF\Mods\Demo;

use SMF\BBCodeParser;
use SMF\Editor;
use SMF\Events\Exception\UnknownEventException;
use SMF\Events\IntegrationEvent;
use SMF\Lang;

final class AddBbcListener
{
	private $event;

	/**
	 *
	 * @param IntegrationEvent $event
	 * @return void
	 * @throws UnknownEventException
	 */
	public function __invoke(IntegrationEvent $event)
	{
		$this->event = $event;
		return match($event->eventName()) {
			BBCodeParser::BBC_CODE_EVENT => $this->addCode(),
			Editor::BBC_BUTTON_EVENT     => $this->addButton(),
			default => throw UnknownEventException::fromListener(static::class)
		};
	}

	/** @return void */
	protected function addCode(): void
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

	/** @return void */
	protected function addButton(): void
	{
		Lang::load('IndentBBC');
		/** @var Editor */
		$target = $this->event->getTarget();
		// Add indent bbc buttons new group after 'justify' button.
		$indent_bbc = [];
		foreach ($target::$bbc_tags[0] as $tag)
		{
			$indent_bbc[] = $tag;
			if (isset($tag['code']) && $tag['code'] == 'justify')
			{
				$indent_bbc[] = [
					'image' => 'indent-left',
					'code' => 'indent-left',
					'before' => '[indent-left]',
					'after' => '[/indent-left]',
					'description' => Lang::$txt['indent-left']
				];
				$indent_bbc[] = [
					'image' => 'indent-right',
					'code' => 'indent-right',
					'before' => '[indent-right]',
					'after' => '[/indent-right]',
					'description' => Lang::$txt['indent-right']
				];
				$indent_bbc[] = [
					'image' => 'indent-both',
					'code' => 'indent-both',
					'before' => '[indent-both]',
					'after' => '[/indent-both]',
					'description' => Lang::$txt['indent-both']
				];
			}
		}
		$target::$bbc_tags[0] = $indent_bbc;
	}
}
