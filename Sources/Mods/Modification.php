<?php

declare(strict_types=1);

namespace SMF\Mods;

use League\Event\ListenerSubscriber;

interface Modification extends ListenerSubscriber
{
	public const NS_BASE = 'SMF';
	public const MOD_DIR = __DIR__;
}
