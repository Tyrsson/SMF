<?php

declare(strict_types=1);

namespace SMF\Mods;

use League\Event\ListenerRegistry;

use function is_array;

abstract class AbstractMod implements Modification
{
	/** @inheritDoc */
	final public function subscribeListeners(ListenerRegistry $acceptor): void
	{
		foreach ($this->getListenerConfig() as $listener) {
			if (is_array($listener['event'])) {
				for ($i=0; $i < count($listener['event']); $i++) {
					$acceptor->subscribeTo(
						$listener['event'][$i],
						new $listener['listener'],
						$listener['priority']
					);
					continue;
				}
			} else {
				$acceptor->subscribeTo(
					$listener['event'],
					new $listener['listener'],
					$listener['priority']
				);
			}
		}
	}
}
