<?php

declare(strict_types=1);

namespace SMF\Actions;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class Action implements ActionInterface
{
	protected static self $obj;

	public function __construct()
	{
		$this->init();
	}

	// Allow subclass initialization
	public function init(): void
	{
	}

	public function __invoke()
	{
		return new $this();
	}
}
