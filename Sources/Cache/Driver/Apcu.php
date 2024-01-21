<?php

/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines https://www.simplemachines.org
 * @copyright 2024 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 3.0 Alpha 1
 */

declare(strict_types=1);

namespace SMF\Cache\Driver;

use SMF\Cache\AbstractDriver;

use function apcu_clear_cache;
use function apcu_delete;
use function apcu_fetch;
use function apcu_store;
use function function_exists;
use function phpversion;
use function strtr;

if (!defined('SMF')) {
	die('No direct access...');
}

/**
 * Our Cache API class
 *
 * @package CacheAPI
 */
class Apcu extends AbstractDriver
{
	/**
	 * {@inheritDoc}
	 */
	public function isSupported(bool $test = false): bool
	{
		return function_exists('apcu_fetch') && function_exists('apcu_store');
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		$key = $this->prefix . strtr($key, ':/', '-_');

		$value = apcu_fetch($key . 'smf');

		return !empty($value) ? $value : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
	{
		$key = $this->prefix . strtr($key, ':/', '-_');

		// An extended key is needed to counteract a bug in APC.
		if ($value === null) {
			return apcu_delete($key . 'smf');
		}

		return apcu_store($key . 'smf', $value, $ttl !== null ? $ttl : $this->ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	public function clear($type = ''): bool
	{
		$this->invalidateCache();

		return apcu_clear_cache();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getVersion(): string|bool
	{
		return phpversion('apcu');
	}

	/** @inheritDoc*/
	public function has(string $key): bool
	{

	}

	/** @inheritDoc */
	public function delete(string $key): bool
	{

	}

}

?>