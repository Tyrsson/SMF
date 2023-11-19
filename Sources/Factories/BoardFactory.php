<?php

declare(strict_types=1);

namespace SMF\Factories;

use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\ServerRequest;
use Psr\Container\ContainerInterface;
use SMF\Board;

final class BoardFactory
{
	public function __invoke(ContainerInterface $container): Board
	{
		/** @var ServerRequest */
		$request = $container->get(ServerRequestFactory::class);
		$query = $request->getQueryParams();
		return new Board($query['board']);
	}
}
