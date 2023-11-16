<?php

declare(strict_types=1);

use SMF\Container\ContainerInterface;
use SMF\Container\Container;

use SMF\Actions;
use SMF\Actions\Agreement;
use SMF\Actions\BoardIndex;
use SMF\Actions\Display;


return [
	'factories'  => [

	],
	'invokables' => [
		Actions\BoardIndex::class   => Actions\BoardIndex::class,
		Actions\Display::class      => Actions\Display::class,
		Actions\MessageIndex::class => Actions\MessageIndex::class,
	],
];

