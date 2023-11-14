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

use SMF\BackwardCompatibility;

use SMF\Lang;
use SMF\Theme;
use SMF\User;
use SMF\Utils;

/**
 * Despite the name, which is what it is for historical reasons, this action
 * doesn't actually send anything. It just shows a message for a guest.
 */
class SendActivation extends AbstractAction
{
	use BackwardCompatibility;

	/**
	 * @var array
	 *
	 * BackwardCompatibility settings for this class.
	 */
	private static $backcompat = array(
		'func_names' => array(
			'call' => 'SendActivation',
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
		User::$me->is_guest = true;

		// Send them to the done-with-registration-login screen.
		Theme::loadTemplate('Register');

		Utils::$context['page_title'] = Lang::$txt['profile'];
		Utils::$context['sub_template'] = 'after';
		Utils::$context['title'] = Lang::$txt['activate_changed_email_title'];
		Utils::$context['description'] = Lang::$txt['activate_changed_email_desc'];

		// Aaand we're gone!
		Utils::obExit();
	}
}

// Export public static functions and properties to global namespace for backward compatibility.
if (is_callable(__NAMESPACE__ . '\SendActivation::exportStatic'))
	SendActivation::exportStatic();

?>