<?php

declare(strict_types=1);

namespace SMF;

use SMF\Actions;
use SMF\Middleware;

final class ConfigProvider
{
	public function __invoke(): array
	{
		return [
			'dependencies' => $this->getDependencies(),
			'templates'    => $this->getTemplates(),
		];
	}

	public function getDependencies(): array
	{
		return [
			'factories'  => [
				Forum::class => Factories\ForumFactory::class,
				Board::class => Factories\BoardFactory::class,
				Middleware\BoardIndex::class => Middleware\Factories\BoardIndexFactory::class,
			],
			'invokables' => [
				Actions\Admin\ACP::class 		=> Actions\Admin\ACP::class,
				Actions\Admin\Logs::class		=> Actions\Admin\Logs::class,
				Actions\Display::class			=> Actions\Display::class,
				Actions\DisplayAdminFile::class => Actions\DisplayAdminFile::class,
				Actions\Like::class				=> Actions\Like::class,
				Actions\MessageIndex::class		=> Actions\MessageIndex::class,
				Actions\Moderation\Home::class  => Actions\Moderation\Home::class,
				Actions\Moderation\Main::class  => Actions\Moderation\Main::class,
				Actions\Post::class 			=> Actions\Post::class,
				Actions\Post2::class			=> Actions\Post2::class,
				Actions\Profile\Main::class		=> Actions\Profile\Main::class,
				Actions\Profile\Popup::class	=> Actions\Profile\Popup::class,
				Actions\QuoteFast::class		=> Actions\QuoteFast::class,

			],
		];
	}
	public function getTemplates(): array
	{
		return [
			'paths' => [
				'layout' => [__DIR__ . '/../../Themes/demo/layout'],
			],
		];
	}
}
