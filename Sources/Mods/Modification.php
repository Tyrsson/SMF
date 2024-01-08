<?php

declare(strict_types=1);

namespace SMF\Mods;

use League\Event\ListenerSubscriber;

interface Modification extends ListenerSubscriber
{
	/**
	 *
	 * @return list { array {,
	 * 		event: string|list{0: string, 1?: string},
	 * 		listener: class-string,
	 * 		priority: int,
	 * 		},
	 * }
	 */
	public function getListenerConfig(): array;
}
