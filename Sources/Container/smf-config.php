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
		Actions\Admin\ACP::class    => Actions\Admin\ACP::class,
		Actions\BoardIndex::class   => Actions\BoardIndex::class,
		Actions\Display::class      => Actions\Display::class,
		Actions\Groups::class       => Actions\Groups::class,
		Actions\MessageIndex::class => Actions\MessageIndex::class,
	],
];

