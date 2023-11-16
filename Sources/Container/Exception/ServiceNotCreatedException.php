<?php

declare(strict_types=1);

namespace SMF\Container\Exception;

use RuntimeException as SplRuntimeException;

/**
 * Thrown when Container can not create a service
 */
class ServiceNotCreatedException extends SplRuntimeException implements
    ContainerException
{
}
