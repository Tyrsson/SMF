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

namespace SMF\Cache;

use DateInterval;
use Psr\Clock\ClockInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use SMF\BackwardCompatibility;
use SMF\Cache\Driver;
use SMF\Config;
use SMF\IntegrationHook;
use SMF\Utils;
use stdClass;
use Traversable;
use TypeError;

use function gettype;
use function is_array;
use function is_bool;
use function is_numeric;
use function sprintf;

class Cache implements CacheInterface
{
	use BackwardCompatibility;

	public const DRIVER_DIR = __DIR__ . '/Driver';
	public const DRIVER_NAMESPACE = __NAMESPACE__ . '\\Driver\\';

	public static $default_driver = Driver\FileSystem::class;

	public static ?DriverInterface $loadedApi = null;

	public static $enable;
	public static $level;
	/**
	 * @var array
	 *
	 * BackwardCompatibility settings for this class.
	 */
	private static $backcompat = [
		'prop_names' => [
			'loadedApi' => 'cacheAPI',
			'hits' => 'cache_hits',
			'count_hits' => 'cache_count',
			'misses' => 'cache_misses',
			'count_misses' => 'cache_count_misses',
		],
	];

	/**************************
	 * Public static properties
	 **************************/

	/**
	 * @var array
	 *
	 * Records debugging info.
	 *
	 * For backward compatibilty, also referenced as global $cache_hits.
	 */
	public static array $hits = [];

	/**
	 * @var int
	 *
	 * The number of times the cache has been accessed.
	 *
	 * For backward compatibilty, also referenced as global $cache_count.
	 */
	public static int $count_hits = 0;

	/**
	 * @var array
	 *
	 * Records debugging info.
	 *
	 * For backward compatibilty, also referenced as global $cache_misses.
	 */
	public static array $misses = [];

	/**
	 * @var int
	 *
	 * The number of times the cache has missed.
	 *
	 * For backward compatibilty, also referenced as global $cache_count_misses.
	 */
	public static int $count_misses = 0;

	/**********************
	 * Protected properties
	 **********************/

	/**
	 * @var string The maximum SMF version that this will work with.
	 */
	protected $version_compatible = '3.0.999';

	/**
	 * @var string The minimum SMF version that this will work with.
	 */
	protected $min_smf_version = '2.1 RC1';

	/**
	 * @var string The prefix for all keys.
	 */
	protected $prefix = '';

	/**
	 * @var int The default TTL.
	 */
	protected int $ttl = 120;

	/****************
	 * Public methods
	 ****************/

	/**
	 * Does basic setup of a cache method when we create the object but before we call connect.
	 *
	 */
	public function __construct(
		private DriverInterface $driver,
		private ?ClockInterface $clock = null,
	) {
		if ($this->setPrefix()) {
			$this->driver->setPrefix($this->prefix);
		}
		if ($this->driver instanceof ConnectableInterface) {
			$this->driver->connect();
		}
	}

	/**
	 * new method to test if driver reports it has
	 * all it needs to be used by the cache
	 */
	public function isSupportedDriver(): bool
	{
		return $this->driver->isSupported();
	}
	/**
	 * Checks whether we can use the cache method performed by this API.
	 *
	 * @param bool $test Test if this is supported or enabled.
	 * @return bool Whether or not the cache is supported
	 */
	public function isSupported(bool $test = false): bool
	{
		return $this->driver->isSupported($test);
	}

	/**
	 *
	 * @param string $prefix The prefix to use.
	 *     If empty, the prefix will be generated automatically.
	 * @return bool If this was successful or not.
	 */
	public function setPrefix(string $prefix = ''): bool
	{
		// Use the supplied prefix, if there is one.
		if (!empty($prefix)) {
			$this->prefix = $prefix;

			return true;
		}

		// Ideally the prefix should reflect the last time the cache was reset.
		if (!empty(Config::$cachedir) && file_exists(Config::$cachedir . '/index.php')) {
			$mtime = filemtime(Config::$cachedir . '/index.php');
		}
		// Fall back to the last time that Settings.php was updated.
		elseif (!empty(Config::$boarddir) && file_exists(SMF_SETTINGS_FILE)) {
			$mtime = filemtime(SMF_SETTINGS_FILE);
		}
		// This should never happen, but just in case...
		else {
			$mtime = filemtime(realpath($_SERVER['SCRIPT_FILENAME']));
		}

		$this->prefix = md5(Config::$boardurl . $mtime) . '-SMF-';

		return true;
	}

	/**
	 * Gets the prefix as defined from set or the default.
	 *
	 * @return string the value of $key.
	 */
	public function getPrefix(): string
	{
		// if this is still empty, generate us one for use.
		if (empty($this->prefix)) {
			$this->setPrefix();
		}
		return $this->prefix;
	}

	/**
	 * Sets a default Time To Live, if this isn't specified we let the class define it.
	 *
	 * @param int $ttl The default TTL
	 * @return bool If this was successful or not.
	 */
	public function setDefaultTTL(int $ttl = 120): self
	{
		$this->ttl = $ttl;

		return $this;
	}

	/**
	 * Gets the TTL as defined from set or the default.
	 *
	 * @return int the value of $ttl.
	 */
	public function getDefaultTTL(): int
	{
		return $this->ttl;
	}

	/**
	 * Invalidate all cached data.
	 *
	 * @return bool Whether or not we could invalidate the cache.
	 */
	public function invalidateCache(): bool
	{
		// Invalidate cache, to be sure!
		// ... as long as index.php can be modified, anyway.
		if (is_writable(Config::$cachedir . '/' . 'index.php')) {
			@touch(Config::$cachedir . '/' . 'index.php');
		}

		return true;
	}

	/**
	 * Specify custom settings that the cache API supports.
	 *
	 * @param array $config_vars Additional config_vars, see ManageSettings.php for usage.
	 */
	public function cacheSettings(array &$config_vars): void
	{
	}

	/**
	 * Gets the latest version of SMF this is compatible with.
	 *
	 * @return string the value of $key.
	 */
	public function getCompatibleVersion(): string|bool
	{
		return $this->version_compatible;
	}

	/**
	 * Gets the min version that we support.
	 *
	 * @return string the value of $key.
	 */
	public function getMinimumVersion(): string|int
	{
		return $this->min_smf_version;
	}

	/**
	 * Gets the Version of the Caching API.
	 *
	 * @return string the value of $key.
	 */
	public function getVersion(): string|bool
	{
		return $this->min_smf_version;
	}

	public function getDriverVersion(): string|int|float
	{
		return $this->driver->getVersion();
	}

	/**
	 * Run housekeeping of this cache
	 * exp. clean up old data or do optimization
	 *
	 */
	public function housekeeping(): void
	{
	}


	/***********************
	 * Public static methods
	 ***********************/

	/**
	 * Get the installed Drivers and suppored drivers
	 */
	final public static function detect(): array
	{
		$drivers = [];

		$installed_drivers = new \GlobIterator(
			self::DRIVER_DIR . '/*.php',
			\FilesystemIterator::NEW_CURRENT_AND_KEY|\FilesystemIterator::SKIP_DOTS
		);

		foreach ($installed_drivers as $file_path => $file_info) {
			$class_name = $file_info->getBasename('.php');
			//$fully_qualified_class_name = self::APIS_NAMESPACE . $class_name;
			$fully_qualified_class_name =  self::DRIVER_NAMESPACE . $class_name;

			if (!class_exists($fully_qualified_class_name)) {
				continue;
			}

			/* @var DriverInterface $driver */
			$driver = new $fully_qualified_class_name();

			// Deal with it!
			if (!$driver instanceof DriverInterface || !$driver->isSupported()) {
				continue;
			}

			$drivers[] = get_class($driver);
			// clean up
			unset($driver);
		}

		IntegrationHook::call('integrate_load_cache_apis', [&$drivers]);

		return $drivers;
	}

	/**
	 * Empty out the cache in use as best it can
	 *
	 * It may only remove the files of a certain type (if the $type parameter is given)
	 * Type can be user, data or left blank
	 * 	- user clears out user data
	 *  - data clears out system / opcode data
	 *  - If no type is specified will perform a complete cache clearing
	 * For cache engines that do not distinguish on types, a full cache flush will be done
	 *
	 * @param string $type The cache type ('memcached', 'zend' or something else for SMF's file cache)
	 */
	final public static function clean(string $type = ''): void
	{
		// If we can't get to the API, can't do this.
		// todo: instanceof check
		if (empty(self::$loadedApi)) {
			return;
		}

		// Ask the API to do the heavy lifting. cleanCache also calls invalidateCache to be sure.
		self::$loadedApi->cleanCache($type);

		IntegrationHook::call('integrate_clean_cache');

		clearstatcache();
	}

	/**
	 * Try to retrieve a cache entry. On failure, call the appropriate function.
	 *
	 * @param string $key The key for this entry
	 * @param string $file The file associated with this entry
	 * @param string|array $function The function to call
	 * @param array $params Parameters to be passed to the specified function
	 * @param int $level The cache level
	 * @return string The cached data
	 */
	final public function quickGet(string $key, string $file, string|array $function, array $params, int $level = 1): mixed
	{
		if (class_exists(IntegrationHook::class, false)) {
			IntegrationHook::call('pre_cache_quick_get', [&$key, &$file, &$function, &$params, &$level]);
		}

		/* Refresh the cache if either:
			1. Caching is disabled.
			2. The cache level isn't high enough.
			3. The item has not been cached or the cached item expired.
			4. The cached item has a custom expiration condition evaluating to true.
			5. The expire time set in the cache item has passed (needed for Zend).
		*/
		if (
			self::$enable
			|| self::$level < $level
			|| !is_array($cache_block = self::get(key: $key, ttl: 3600))
			|| (!empty($cache_block['refresh_eval'])
			&& eval($cache_block['refresh_eval']))
			|| (!empty($cache_block['expires'])
			&& $cache_block['expires'] < time())
		) {
			if (!empty($file) && is_file(Config::$sourcedir . '/' . $file)) {
				require_once Config::$sourcedir . '/' . $file;
			}

			$cache_block = call_user_func_array($function, $params);

			if (self::$enable && self::$level >= $level) {
				$this->set($key, $cache_block, $cache_block['expires'] - time());
			}
		}

		// Some cached data may need a freshening up after retrieval.
		if (!empty($cache_block['post_retri_eval'])) {
			eval($cache_block['post_retri_eval']);
		}

		if (class_exists(IntegrationHook::class, false)) {
			IntegrationHook::call('post_cache_quick_get', [&$cache_block]);
		}

		return $cache_block['data'];
	}

	/**
	 * Puts value in the cache under key for ttl seconds.
	 *
	 * - It may "miss" so shouldn't be depended on
	 * - Uses the cache engine chosen in the ACP and saved in Settings.php
	 * - It supports:
	 *	 memcache: https://php.net/memcache
	 *   APCu: https://php.net/book.apcu
	 *	 Zend: http://files.zend.com/help/Zend-Platform/output_cache_functions.htm
	 *	 Zend: http://files.zend.com/help/Zend-Platform/zend_cache_functions.htm
	 *
	 * @param string $key A key for this value
	 * @param mixed $value The data to cache
	 * @param int $ttl How long (in seconds) the data should be cached for
	 */
	final public function put(string $key, mixed $value, int $ttl = 120): void
	{
		if (empty(self::$enable) || empty(self::$loadedApi)) {
			return;
		}

		self::$count_hits++;

		if (isset(Config::$db_show_debug) && Config::$db_show_debug === true) {
			self::$hits[self::$count_hits] = ['k' => $key, 'd' => 'put', 's' => $value === null ? 0 : strlen(Utils::jsonEncode($value))];
			$st = microtime(true);
		}

		// The API will handle the rest.
		$value = $value === null ? null : Utils::jsonEncode($value);
		self::$loadedApi->putData($key, $value, $ttl);

		if (class_exists(IntegrationHook::class, false)) {
			IntegrationHook::call('cache_put_data', [&$key, &$value, &$ttl]);
		}

		if (isset(Config::$db_show_debug) && Config::$db_show_debug === true) {
			self::$hits[self::$count_hits]['t'] = microtime(true) - $st;
		}
	}

	/**
	 * Gets the value from the cache specified by key, so long as it is not older than ttl seconds.
	 * - It may often "miss", so shouldn't be depended on.
	 * - It supports the same as self::put().
	 *
	 * @param string $key The key for the value to retrieve
	 * @param int $ttl The maximum age of the cached data
	 * @return array|null The cached data or null if nothing was loaded
	 */
	final public function get(string $key, mixed $default = null, int $ttl = 120): mixed
	{
		// if (empty(self::$enable) || empty(self::$loadedApi)) {
		// 	return null;
		// }

		self::$count_hits++;

		if (isset(Config::$db_show_debug) && Config::$db_show_debug === true) {
			self::$hits[self::$count_hits] = ['k' => $key, 'd' => 'get'];
			$st = microtime(true);
			$original_key = $key;
		}

		// Ask the API to get the data.
		$value = $this->driver->get(key: $key, ttl: $ttl);

		if (isset(Config::$db_show_debug) && Config::$db_show_debug === true) {
			self::$hits[self::$count_hits]['t'] = microtime(true) - $st;
			self::$hits[self::$count_hits]['s'] = isset($value) ? strlen((string) $value) : 0;

			if (empty($value)) {
				self::$count_misses++;
				self::$misses[self::$count_misses] = ['k' => $original_key, 'd' => 'get'];
			}
		}

		if (class_exists(IntegrationHook::class, false) && isset($value)) {
			IntegrationHook::call('cache_get_data', [&$key, &$ttl, &$value]);
		}

		return match(true) {
			empty($value) => null,
			is_string($value) => Utils::jsonDecode($value, true),
			$value instanceof stdClass => (array) $value,
			default => $value
		};
	}

	/** @inheritDoc */
	public function set(string $key, mixed $value = null, null|int|DateInterval $ttl = null): bool
	{
		if (!$this->driver->isCacheableValue($value)) {
			// todo: replace with correct Lang string
			throw new InvalidArgumentException(
				sprintf(
					'$value must be of type string, int, float, bool, null, array, object received: %s',
					gettype($value)
				)
			);
		}

		if ($ttl === null) {
			$ttl = 120;
		}

		self::$count_hits++;

		if (isset(Config::$db_show_debug) && Config::$db_show_debug === true) {
			self::$hits[self::$count_hits] = ['k' => $key, 'd' => 'set', 's' => $value === null ? 0 : strlen(Utils::jsonEncode($value))];
			$st = microtime(true);
		}

		// proxy to the driver
		//$value = $value === null ? null : Utils::jsonEncode($value);
		$result = $this->driver->set($key, $value, $ttl);

		if (class_exists(IntegrationHook::class, false)) {
			// todo: update hook name or transition to events
			IntegrationHook::call('cache_put_data', [&$key, &$value, &$ttl]);
		}

		if (Config::$db_show_debug) {
			self::$hits[self::$count_hits]['t'] = microtime(true) - $st;
		}

		return $result;
	}

	public function delete(string $key): bool
	{
		return $this->driver->delete($key);
	}

	public function clear(string $type = ''): bool
	{
		// proxy to the driver
		if ($this->driver->clear($type)) {
			IntegrationHook::call('integrate_clean_cache');
			clearstatcache();
			return true;
		}
		return false;
	}

	public function getMultiple(iterable $keys, mixed $default = null): iterable
	{
		return [];
	}

	/** @inheritDoc */
	public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
	{
		if (!is_array($values) || !$values instanceof Traversable) {
			throw new InvalidArgumentException('$values must be an array or Traversable');
		}

		try {
			foreach ($values as $item) {
				if (!isset($item['key']) || !is_string($item['key'])) {
					continue;
				}
				$this->set($item['key'], $item['value'], $item['ttl'] ?? $ttl);
			}
		} catch (TypeError|InvalidArgumentException $e) {
			// todo: log error
			return false;
		}

		return true;
	}

	public function deleteMultiple(iterable $keys): bool
	{
		return true;
	}

	public function has(string $key): bool
	{
		return true;
	}
}

// Export properties to global namespace for backward compatibility.
if (is_callable([Cache::class, 'exportStatic'])) {
	Cache::exportStatic();
}

?>