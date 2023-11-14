<?php

/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines https://www.simplemachines.org
 * @copyright 2023 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 3.0 Alpha 1
 */

namespace SMF\Actions;

abstract class AbstractAction implements ActionInterface
{
	/**
	 * Constructor. Protected to force instantiation via self::load().
	 */
	protected function __construct()
	{
	}
	/**
	 * @var static $obj
	 *
	 * An instance of the concrete class.
	 */
	protected static self $obj;
	public static function load(): ActionInterface
	{
		if (!isset(static::$obj))
			static::$obj = new static();

		return static::$obj;
	}

	/**
	 * Convenience method to load() and execute() an instance of this class.
	 */
	public static function call(): void
	{
		static::load()->execute();
	}
}
