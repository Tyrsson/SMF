<?php

declare(strict_types=1);

namespace SMF\Mods\Demo;

use League\Event\EventDispatcher;
use League\Event\ListenerRegistry;

return function(EventDispatcher $eventDispatcher, ListenerRegistry $listenerRegistry): void {
	$mod = (new Mod($eventDispatcher, $listenerRegistry))();
};