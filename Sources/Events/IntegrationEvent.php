<?php

declare(strict_types=1);

namespace SMF\Events;

use League\Event\HasEventName;

final class IntegrationEvent implements HasEventName
{

	private string $name;
	private object $target;

	public function __construct(string $name, object $target)
	{
		$this->name   = $name;
		$this->target = $target;
	}

	public function eventName(): string
	{
		return $this->name;
	}

	public function getTarget(): object
	{
		return $this->target;
	}
}
