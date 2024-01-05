<?php

declare(strict_types=1);

namespace SMF\Mods\Demo;

use SMF\Events\IntegrationEvent;

final class AddActionListener
{
	// 'agreement' => ['', 'SMF\\Actions\\Agreement::call'],
	public function __construct()
	{

	}

	public function __invoke(IntegrationEvent $event)
	{
		$customAction = ['', static::class];
		$target = $event->getTarget();
		$target::$actions += ['demo' => $customAction];
	}
}
