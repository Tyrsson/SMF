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

use SMF\User;
use SMF\Utils;
use SMF\Db\DatabaseApi as Db;

/**
 * Outputs each member name on its own line.
 *
 * Used by JavaScript to find members matching the request.
 *
 * @deprecated 3.0 The requestmembers action wasn't used even in SMF 2.0!
 * @todo This is 100% obsolete, but was never officially deprecated. Remove?
 */
class RequestMembers extends AbstractAction
{
	use BackwardCompatibility;

	/**
	 * @var array
	 *
	 * BackwardCompatibility settings for this class.
	 */
	private static $backcompat = array(
		'func_names' => array(
			'call' => 'RequestMembers',
		),
	);

	/*******************
	 * Public properties
	 *******************/

	/**
	 * @var string
	 *
	 * Search string.
	 */
	public string $search = '';

	/****************
	 * Public methods
	 ****************/

	/**
	 * Does the job.
	 */
	public function execute(): void
	{
		User::$me->checkSession('get');

		if (Utils::$context['utf8'] || function_exists('mb_convert_encoding'))
			header('content-type: text/plain; charset=UTF-8');

		$request = Db::$db->query('', '
			SELECT real_name
			FROM {db_prefix}members
			WHERE {raw:real_name} LIKE {string:search}' . (isset($_REQUEST['buddies']) ? '
				AND id_member IN ({array_int:buddy_list})' : '') . '
				AND is_activated IN (1, 11)
			LIMIT ' . (Utils::entityStrlen($this->search) <= 2 ? '100' : '800'),
			array(
				'real_name' => Db::$db->case_sensitive ? 'LOWER(real_name)' : 'real_name',
				'buddy_list' => User::$me->buddies,
				'search' => $this->search,
			)
		);
		while ($row = Db::$db->fetch_assoc($request))
		{
			if (!Utils::$context['utf8'])
			{
				if (($temp = @mb_convert_encoding($row['real_name'], 'UTF-8', Utils::$context['character_set'])) !== false)
					$row['real_name'] = $temp;
			}

			$row['real_name'] = strtr($row['real_name'], array('&amp;' => '&#038;', '&lt;' => '&#060;', '&gt;' => '&#062;', '&quot;' => '&#034;'));

			$row['real_name'] = Utils::entityDecode($row['real_name'], true);

			echo $row['real_name'], "\n";
		}
		Db::$db->free_result($request);

		Utils::obExit(false);
	}

	/******************
	 * Internal methods
	 ******************/

	/**
	 * Constructor. Protected to force instantiation via self::load().
	 */
	protected function __construct()
	{
		$this->search = $_REQUEST['search'];

		$this->search = Utils::htmlspecialchars($this->search);
		$this->search = trim(Utils::strtolower($this->search)) . '*';
		$this->search = strtr($this->search, array('%' => '\%', '_' => '\_', '*' => '%', '?' => '_', '&#038;' => '&amp;'));
	}
}

// Export public static functions and properties to global namespace for backward compatibility.
if (is_callable(__NAMESPACE__ . '\RequestMembers::exportStatic'))
	RequestMembers::exportStatic();

?>