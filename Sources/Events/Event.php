<?php

declare(strict_types=1);

namespace SMF\Events;

use League\Event\HasEventName;
use PhpCsFixer\Differ\NullDiffer;
use Psr\EventDispatcher\StoppableEventInterface;

class Event implements HasEventName, StoppableEventInterface
{

	public function __construct(
		private string $name,
		private object|string|null $target = null,
		private array|object|null $params = null,
		private bool $stopPropagation = false
	) {
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getName(): string
	{
		return $this->eventName();
	}

	public function setTarget(object|string $target): void
	{
		$this->target = $target;
	}

	public function setParams(array|object $params): void
	{

	}
	public function eventName(): string
	{
		return $this->name;
	}

	public function stopPropagation(bool $flag = true): void
	{
		$this->stopPropagation = $flag;
	}
	public function isPropagationStopped(): bool
	{
		return $this->stopPropagation;
	}
}
