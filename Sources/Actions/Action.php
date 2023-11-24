<?php

declare(strict_types=1);

namespace SMF\Actions;

abstract class Action implements ActionInterface
{
	public function __invoke(): self
	{
		return new $this();
	}
}
