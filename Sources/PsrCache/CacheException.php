<?php

declare(strict_types=1);

namespace SMF\PsrCache;

use Psr\Cache\CacheException as PsrExceptionInterface;
use RuntimeException;

class CacheException extends RuntimeException implements PsrExceptionInterface
{
}
