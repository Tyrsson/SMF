<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

$aggregator = new ConfigAggregator([
	\Mezzio\Helper\ConfigProvider::class,
	\Mezzio\LaminasView\ConfigProvider::class,
	\Laminas\Diactoros\ConfigProvider::class,
    \SMF\ConfigProvider::class,
	new PhpFileProvider(realpath(__DIR__) . '/../development.config.php'),
]);
return $aggregator->getMergedConfig();