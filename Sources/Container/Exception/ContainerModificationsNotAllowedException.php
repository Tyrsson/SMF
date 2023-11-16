<?php

declare(strict_types=1);

/**
 * @credit Laminas
 */

namespace SMF\Container\Exception;

use DomainException;

use function sprintf;

class ContainerModificationsNotAllowedException extends DomainException implements ContainerException
{
	/**
	 * @param string $service Name of service that already exists.
	 */
	public static function fromExistingService(string $service): self
	{
		return new self(sprintf(
			'The container does not allow replacing or updating a service'
			. ' with existing instances; the following service'
			. ' already exists in the container: %s',
			$service
		));
	}
}
