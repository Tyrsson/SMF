<?php

declare(strict_types=1);

namespace SMF;

use SMF\Actions;

final class ConfigProvider
{
	public function __invoke(): array
	{
		return [
			'dependencies' => $this->getDependencies(),
		];
	}

	public function getDependencies(): array
	{
		return [
			'factories'  => [
				Forum::class => Factories\ForumFactory::class,
				Board::class => Factories\BoardFactory::class,
			],
			'invokables' => [
				Actions\Admin\ACP::class    => Actions\Admin\ACP::class,
				Actions\Admin\Logs::class   => Actions\Admin\Logs::class,
				Actions\BoardIndex::class   => Actions\BoardIndex::class,
				Actions\MessageIndex::class => Actions\MessageIndex::class,
				Actions\Display::class      => Actions\Display::class,
				Actions\Profile\Main::class  => Actions\Profile\Main::class,
				Actions\Profile\Popup::class => Actions\Profile\Popup::class,
			],
		];
	}
}
