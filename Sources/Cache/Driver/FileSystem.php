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

use DateInterval;
use FilesystemIterator;
use GlobIterator;
use SMF\Cache\AbstractDriver;
use SMF\Cache\CacheableInterface;
use SMF\Config;
use SMF\Lang;
use SMF\Utils;

use function serialize;

if (!defined('SMF')) {
	die('No direct access...');
}

/**
 * Our Cache API class
 *
 * @package CacheAPI
 */
class FileSystem extends AbstractDriver
{
	/**
	 * @var string The path to the current directory.
	 */
	protected ?string $cachedir = null;

	/**
	 * {@inheritDoc}
	 */
	public function __construct()
	{
		// Set our default cachedir.
		$this->setCachedir();
	}

	public function setTtl(int $ttl = 120): void { }

	public function getTtl(): ?int { }

	public function setPrefix(string $prefix): void { }

	public function getPrefix(): string { }

	public function delete(string $key): bool { }

	public function getMultiple(iterable $keys, mixed $default = null): iterable { }

	public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool { }

	public function deleteMultiple(iterable $keys): bool { }

	public function has(string $key): bool { }

	/**
	 * {@inheritDoc}
	 */
	public function isSupported(): bool
	{
		return is_writable($this->cachedir);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $key, mixed $default = null, int $ttl = 120): mixed
	{
		$file = sprintf(
			'%s/data_%s.cache',
			$this->cachedir,
			$this->prefix . strtr($key, ':/', '-_'),
		);

		// SMF Data returns $value and $expired.  $expired has a unix timestamp of when this expires.
		if (file_exists($file) && ($raw = $this->readFile($file)) !== false) {
			if (($value = Utils::jsonDecode($raw, false, 512, 0, false)) !== null && isset($value->expiration) && $value->expiration >= time()) {
				return $value->value;
			}

			@unlink($file);
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set(string $key, mixed $value = null, null|int|DateInterval $ttl = null): bool
	{
		$file = sprintf(
			'%s/data_%s.cache',
			$this->cachedir,
			$this->prefix . strtr($key, ':/', '-_'),
		);

		if ($value === null) {
			unlink($file);

			return true;
		}

		if ($value instanceof CacheableInterface) {
			$cache_data = serialize($value);
		} else {
			$cache_data = json_encode(
				[
					'expiration' => time() + ($ttl ?? $this->ttl),
					'value' => $value,
				],
				JSON_NUMERIC_CHECK,
			);
		}

		// Write out the cache file, check that the cache write was successful; all the data must be written
		// If it fails due to low diskspace, or other, remove the cache file
		if ($this->writeFile($file, $cache_data) !== strlen($cache_data)) {
			unlink($file);

			return false;
		}

		return true;

	}

	/**
	 * {@inheritDoc}
	 */
	public function clear($type = ''): bool
	{
		// No directory = no game.
		if (!is_dir($this->cachedir)) {
			return false;
		}

		// Remove the files in SMF's own disk cache, if any
		$files = new GlobIterator($this->cachedir . '/' . $type . '*.cache', FilesystemIterator::NEW_CURRENT_AND_KEY);

		foreach ($files as $file => $info) {
			unlink($this->cachedir . '/' . $file);
		}

		// Make this invalid.
		$this->invalidateCache();

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function invalidateCache(): bool
	{
		// We don't worry about $cachedir here, since the key is based on the real $cachedir.
		parent::invalidateCache();

		// Since SMF is file based, be sure to clear the statcache.
		clearstatcache();

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function cacheSettings(array &$config_vars): void
	{
		$class_name = $this->getDriverClassName();
		$class_name_txt_key = strtolower($class_name);

		$config_vars[] = Lang::$txt['cache_' . $class_name_txt_key . '_settings'];
		$config_vars[] = ['cachedir', Lang::$txt['cachedir'], 'file', 'text', 36, 'cache_cachedir'];

		if (!isset(Utils::$context['settings_post_javascript'])) {
			Utils::$context['settings_post_javascript'] = '';
		}

		if (empty(Utils::$context['settings_not_writable'])) {
			Utils::$context['settings_post_javascript'] .= '
			$("#cache_accelerator").change(function (e) {
				var cache_type = e.currentTarget.value;
				$("#cachedir").prop("disabled", cache_type != "' . $class_name . '");
			});';
		}
	}

	/**
	 * Sets the $cachedir or uses the SMF default $cachedir..
	 *
	 * @param string $dir A valid path
	 * @return bool If this was successful or not.
	 */
	public function setCachedir(?string $dir = null): void
	{
		// If its invalid, use SMF's.
		if (is_null($dir) || !is_writable($dir)) {
			$this->cachedir = Config::$cachedir;
		} else {
			$this->cachedir = $dir;
		}
	}

	/**
	 * Gets the current $cachedir.
	 *
	 * @return string the value of $ttl.
	 */
	public function getCachedir(): string
	{
		return $this->cachedir;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getVersion(): string|bool
	{
		return SMF_VERSION;
	}

	private function readFile(string $file): mixed
	{
		if (($fp = @fopen($file, 'rb')) !== false) {
			if (!flock($fp, LOCK_SH)) {
				fclose($fp);

				return false;
			}
			$string = '';

			while (!feof($fp)) {
				$string .= fread($fp, 8192);
			}

			flock($fp, LOCK_UN);
			fclose($fp);

			return $string;
		}

		return false;
	}

	private function writeFile(string $file, mixed $string): mixed
	{
		if (($fp = fopen($file, 'cb')) !== false) {
			if (!flock($fp, LOCK_EX)) {
				fclose($fp);

				return false;
			}
			ftruncate($fp, 0);
			$bytes = 0;
			$pieces = str_split($string, 8192);

			foreach ($pieces as $piece) {
				if (($val = fwrite($fp, $piece, 8192)) !== false) {
					$bytes += $val;
				} else {
					return false;
				}
			}
			fflush($fp);
			flock($fp, LOCK_UN);
			fclose($fp);

			return $bytes;
		}

		return false;
	}
}

?>