<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

$aggregator = new ConfigAggregator([
	\Mezzio\ConfigProvider::class,
	\Mezzio\Router\ConfigProvider::class,
	\Mezzio\Helper\ConfigProvider::class,
	\Laminas\Diactoros\ConfigProvider::class,
	\Laminas\HttpHandlerRunner\ConfigProvider::class,
    \SMF\ConfigProvider::class,
	new PhpFileProvider(realpath(__DIR__) . '/../development.config.php'),
]);
return $aggregator->getMergedConfig();