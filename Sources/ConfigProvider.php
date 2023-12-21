<?php

declare(strict_types=1);

namespace SMF;

use Mezzio\Router\RouterInterface;


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
			'aliases' => [
				RouterInterface::class => Router\ParamRouter::class
			],
			'factories'  => [
				//SMF
				Forum::class => Factories\ForumFactory::class,
				Board::class => Factories\BoardFactory::class,
				Middleware\BoardIndex::class => Middleware\Factories\BoardIndexFactory::class,
				// Libs
				EmitterInterface::class        => Factories\EmitterFactory::class,
				ErrorHandler::class            => Factories\ErrorHandlerFactory::class,
				Handler\NotFoundHandler::class => Factories\NotFoundHandlerFactory::class,
				MiddlewareContainer::class     => Factories\MiddlewareContainerFactory::class,
				MiddlewareFactory::class       => Factories\MiddlewareFactoryFactory::class,
				// Change the following in development to the WhoopsErrorResponseGeneratorFactory:
				Middleware\ErrorResponseGenerator::class            => Factories\ErrorResponseGeneratorFactory::class,
				RequestHandlerRunner::class                         => Factories\RequestHandlerRunnerFactory::class,
				ResponseInterface::class                            => Factories\ResponseFactoryFactory::class,
				Response\ServerRequestErrorResponseGenerator::class => Factories\ServerRequestErrorResponseGeneratorFactory::class,
				ServerRequestInterface::class                       => Factories\ServerRequestFactoryFactory::class,
				StreamInterface::class                              => Factories\StreamFactoryFactory::class,
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
				Router\ParamRouter::class		=> Router\ParamRouter::class,
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
