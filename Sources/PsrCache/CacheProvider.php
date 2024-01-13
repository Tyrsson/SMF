<?php

declare(strict_types=1);

namespace SMF\PsrCache;

use Fig\EventDispatcher\ParameterDeriverTrait;

final class CacheProvider
{
	use ParameterDeriverTrait;

	protected array $cachers = [];

}
