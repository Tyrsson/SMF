<?php

declare(strict_types=1);

namespace SMF\Mods\Demo;

use SMF\Events\IntegrationEvent;
use SMF\Forum;
use SMF\Mods\Demo\DemoAction;

final class AddActionListener
{
	public function __construct()
	{

	}

	public function __invoke(IntegrationEvent $event)
	{
		/** @var Forum */
		$target   = $event->getTarget();
		$instance = new DemoAction();
		$target::$actions += ['demo' => ['', [$instance, '__invoke']]];
	}
}
