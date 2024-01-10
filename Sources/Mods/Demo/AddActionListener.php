<?php

declare(strict_types=1);

namespace SMF\Mods\Demo;

use SMF\Events\IntegrationEvent;
use SMF\Forum;
use SMF\Mods\Demo\DemoAction;

final class AddActionListener
{
	/**
	 *
	 * @param IntegrationEvent $event
	 * @return void
	 */
	public function __invoke(IntegrationEvent $event): void
	{
		/** @var Forum */
		$target   = $event->getTarget();
		$instance = new DemoAction();
		$target::$actions += ['demo' => ['', [$instance, '__invoke']]];
	}
}
