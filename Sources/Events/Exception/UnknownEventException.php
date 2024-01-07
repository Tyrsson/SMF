<?php

declare(strict_types=1);

namespace SMF\Events\Exception;

use \Exception;

use function sprintf;

final class UnknownEventException extends Exception
{
	public static function fromListener(string $listener): self
	{
		return new self(sprintf(
			'Unknown event in ',
			$listener
		));
	}
}
