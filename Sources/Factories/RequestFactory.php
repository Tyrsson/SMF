<?php

declare(strict_types=1);

namespace SMF\Factories;

use Laminas\Diactoros;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use SMF\QueryString;
use SMF\Db\DatabaseApi as Db;

final class RequestFactory
{
	public function __invoke(ContainerInterface $container): ServerRequestInterface
	{
		Db::load();
		QueryString::cleanRequest();
		$factory = $container->get(ServerRequestFactoryInterface::class);
		$request = $factory::fromGlobals(
			$_SERVER,
			$_GET,
			$_POST,
			$_COOKIE,
			$_FILES
		);
		return $request->withAttribute('REQUEST', $_REQUEST);
	}
}
