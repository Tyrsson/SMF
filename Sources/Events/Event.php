<?php

declare(strict_types=1);

namespace SMF\Events;

use ArrayAccess;
use League\Event\HasEventName;
use Psr\EventDispatcher\StoppableEventInterface;

class Event implements HasEventName, StoppableEventInterface
{

	/**
	 *
	 * @param string $name
	 * @param null|object|string $target
	 * @param array|object $params
	 * @param bool $stopPropagation
	 * @return void
	 */
	public function __construct(
		private string $name,
		private object|string|null $target = null,
		private array|object $params = [],
		private bool $stopPropagation = false
	) {
	}

	/**
	 *
	 * @param string $name
	 * @return Event
	 */
	public function setName(string $name): self
	{
		$this->name = $name;

		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->eventName();
	}

	/**
	 *
	 * @param object|string $target
	 * @return Event
	 */
	public function setTarget(object|string $target): self
	{
		$this->target = $target;

		return $this;
	}

	/**
	 *
	 * @return object|string
	 */
	public function getTarget(): object|string
	{
		return $this->target;
	}

	/**
	 *
	 * @param array|object $params
	 * @return Event
	 */
	public function setParams(array|object $params): self
	{
		$this->params = $params;

		return $this;
	}

	/**
	 *
	 * @return array|object
	 */
	public function getParams(): array|object
	{
		return $this->params;
	}

	/**
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return Event
	 */
	public function setParam(string $name, mixed $value): self
	{
		if (is_array($this->params) || $this->params instanceof ArrayAccess) {
			$this->params[$name] = $value;
		}

		if (is_object($this->params)) {
			$this->params->{$name} = $value;
		}

		return $this;
	}

	/**
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getParam(string $name, mixed $default = null): mixed
	{
		if (is_array($this->params) || $this->params instanceof ArrayAccess) {
			if (!isset($this->params[$name])) {
				return $default;
			}
		}

		if (! isset($this->params->{$name})) {
			return $default;
		}

		return $this->params->{$name};
	}

	/**
	 *
	 * @return string
	 */
	public function eventName(): string
	{
		return $this->name;
	}

	/**
	 *
	 * @param bool $flag
	 * @return Event
	 */
	public function stopPropagation(bool $flag = true): self
	{
		$this->stopPropagation = $flag;

		return $this;
	}

	/**
	 *
	 * @return bool
	 */
	public function isPropagationStopped(): bool
	{
		return $this->stopPropagation;
	}
}
