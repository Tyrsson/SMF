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

namespace SMF\Actions\Moderation;

use SMF\BackwardCompatibility;
use SMF\Actions\AbstractAction;

use SMF\Utils;

/**
 * Ends a moderator session, requiring authentication to access the moderation
 * center again.
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
			'call' => 'ModEndSession',
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
		unset($_SESSION['moderate_time']);

		// Clean any moderator tokens as well.
		foreach ($_SESSION['token'] as $key => $token)
		{
			if (strpos($key, '-mod') !== false)
				unset($_SESSION['token'][$key]);
		}

		Utils::redirectexit();
	}

}

// Export public static functions and properties to global namespace for backward compatibility.
if (is_callable(__NAMESPACE__ . '\EndSession::exportStatic'))
	EndSession::exportStatic();

?>