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

namespace SMF\Actions\Admin;

use SMF\BackwardCompatibility;
use SMF\Actions\AbstractAction;

use SMF\Utils;

/**
 * Ends an admin session, requiring authentication to access the ACP again.
 */
class EndSession extends AbstractAction
{
	use BackwardCompatibility;

	/**
	 * @var array
	 *
	 * BackwardCompatibility settings for this class.
	 */
	private static $backcompat = array(
		'func_names' => array(
			'call' => 'AdminEndSession',
		),
	);

	/****************
	 * Public methods
	 ****************/

	/**
	 * Do the job.
	 */
	public function execute(): void
	{
		// This is so easy!
		unset($_SESSION['admin_time']);

		// Clean any admin tokens as well.
		foreach ($_SESSION['token'] as $key => $token)
		{
			if (strpos($key, '-admin') !== false)
				unset($_SESSION['token'][$key]);
		}

		Utils::redirectexit();
	}

	/***********************
	 * Public static methods
	 ***********************/

	/******************
	 * Internal methods
	 ******************/

	/**
	 * Constructor. Protected to force instantiation via self::load().
	 */
	protected function __construct()
	{
	}
}

// Export public static functions and properties to global namespace for backward compatibility.
if (is_callable(__NAMESPACE__ . '\EndSession::exportStatic'))
	EndSession::exportStatic();

?>