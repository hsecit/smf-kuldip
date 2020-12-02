<?php
/**
 * Menu Editor Lite
 *
 * @file ./menu_source/resources/ajax.php
 * @author Matthew Kerle <lab360@simplemachines.org>
 * @copyright Matthew Kerle, 2012
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * @version 1.0.5
 */

/**
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://www.labradoodle-360.com code.
 *
 * The Initial Developer of the Original Code is
 * Matthew Kerle.
 * Portions created by the Initial Developer are Copyright (C) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * http_request_children()
 * 
 * @return
 */
function http_request_children()
{

	// The requested ID, ints only.
	if (!empty($_POST['menu_id']))
	{
		$_POST['menu_id'] = str_replace('child_link_', '', $_POST['menu_id']);
		$_POST['menu_id'] = (int) $_POST['menu_id'];
	}

	// If it's (now) empty, it's a failure. else - Otherwise, the query.
	if (empty($_POST['menu_id']))
		echo 'failure';
	else
	{

		// Globalize $smcFunc & $scripturl.
		global $smcFunc, $scripturl;

		// Variable is empty to start.
		$return_array = '';

		// The request, itself.
		$request = $smcFunc['db_query']('', '
			SELECT id_button, name
			FROM {db_prefix}menu_items
			WHERE id_parent = {int:menu_id}
			ORDER BY id_button ASC',
			array(
				'menu_id' => $_POST['menu_id']
			)
		);
	
		// Format the returned data.
		while ($data = $smcFunc['db_fetch_assoc']($request))
			$return_array .= '<a href="' . $scripturl . '?action=admin;area=menueditor;sa=modify;button=' . $data['id_button'] . ';parent=' . $_POST['menu_id'] . '" class="child_item">' . $data['name'] . '</a>';

		// Free the results.
		$smcFunc['db_free_result']($request);

		// Echo the array..
		echo $return_array;
	}

	// And we're done.
	exit;

}