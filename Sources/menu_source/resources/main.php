<?php
/**
 * Menu Editor Lite
 *
 * @file ./menu_source/resources/main.php
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
 * MenuItems()
 * 
 * @return
 */
function MenuItems()
{

	// Globalize everything we'll need here...
	global $context, $settings, $scripturl, $txt;
	global $user_info, $smcFunc, $menu_editor, $modSettings;

	// Load our language
	loadLanguage('/menu_language/main');

	// Our queryyy!
	$query = $smcFunc['db_query']('', '
		SELECT id_button, name, href,
			target, link_type, button_order,
			level, id_parent, has_children
		FROM {db_prefix}menu_items
		ORDER BY button_order'
	);

	// Membergroups Array
	$menu_editor['smf_mgroups'] = array(
		'?action=logout' => !$user_info['is_guest'],
		'?action=login' => $user_info['is_guest'],
		'?action=register' => $user_info['is_guest'],
        '?action=profile' => !$user_info['is_guest'],
		'?action=calendar' => $modSettings['cal_enabled'] # !! This was just thrown in here for Lite; consider working it in below, later.
	);

	// Permissions Array # !! These should be pulled from Subs.php, eventually.
	$menu_editor['smf_perms'] = array(
		'?action=search' => array(
			'search_posts'
		),
		'?action=admin' => array(
			'admin_forum', 'manage_boards',
			'manage_permissions', 'moderate_forum',
			'manage_membergroups', 'manage_bans',
			'send_mail', 'edit_news',
			'manage_attachments', 'manage_smileys'
		),
		'?action=profile' => array(
			'profile_view_own', 'profile_view_any',
			'profile_identity_own', 'profile_identity_any',
			'profile_extra_own', 'profile_extra_any',
			'profile_remove_own', 'profile_remove_any',
			'moderate_forum', 'manage_membergroups',
			'profile_title_own', 'profile_title_any'
		),
		'?action=pm' => array(
			'pm_read'
		),
		'?action=calendar' => array(
			'calendar_view'
		),
		'?action=mlist' => array(
			'view_mlist'
		)
	);

	// Empty arrays!
	$menu_editor['items'] = array();
	$menu_editor['actual'] = array();
	$menu_editor['id_array'] = array();
	$menu_editor['children']['items'] = array();
	$menu_editor['children']['actual'] = array();
	$menu_editor['children']['id_array'] = array();
	$menu_editor['grandchildren']['items'] = array();
	$menu_editor['grandchildren']['actual'] = array();
	$menu_editor['grandchildren']['id_array'] = array();
	$menu_editor['parent_ids'] = array();

	// Lets fill the arrays now...
	while ($data = $smcFunc['db_fetch_assoc']($query))
	{

		// What level are we?
		switch ($data['level'])
		{
			case 1:
				$defined_set = 'children';
			break;
			case 2:
				$defined_set = 'grandchildren';
			break;
			default:
				$defined_set = '';
			break;
		}

		// Menu Items
		if (!empty($defined_set))
		{
			$menu_editor[$defined_set]['items'][$data['id_parent']][$data['id_button']] = $data;
			$menu_editor[$defined_set]['id_array'][$data['id_button']] = $data['name'];
		}
		else
		{
			$menu_editor['items'][$data['id_button']] = $data;
			$menu_editor['id_array'][$data['id_button']] = $data['name'];
		}

		// Parent IDs! Parent ID -> Button ID
		$menu_editor['parent_ids'][$data['id_button']] = $data['id_parent'];

		// Our Target - aim, fire!
		$temp['final_target'] = '_';
		switch ($data['target'])
		{
			case 1:
				$temp['final_target'] .= 'parent';
                break;
			case 2:
				$temp['final_target'] .= 'blank';
                break;
			case 3:
				$temp['final_target'] .= 'top';
                break;
			default:
				$temp['final_target'] .= 'self';
                break;
		}

		// The final link will be different for each type.
		if ($data['link_type'] == 1)
		{
			switch ($data['href'])
			{
				// $scripturl, anyone?
				case '01001001010011000100110001001101':
					$temp['final_href'] = $scripturl;
				break;
				// Logout needs session_var & session_id for verification.
				case '?action=logout':
					$temp['final_href'] = $scripturl . $data['href'] . ';' . $context['session_var'] . '=' . $context['session_id'];
				break;
				// An action huh? Sweet.
				default:
					$temp['final_href'] = $scripturl . $data['href'];
				break;
			}
		}
		// Otherwise, we're normal >={
		else
			$temp['final_href'] = $data['href'];

		// SMF Default Permissions
		$temp['fill_array'] = array();
		if (in_array($data['href'], array_keys($menu_editor['smf_perms'])))
		{
			if (!allowedTo($menu_editor['smf_perms'][$data['href']]))
				$temp['fill_array'][] = 0;
		}

		// SMF Default Membergroups
		if (in_array($data['href'], array_keys($menu_editor['smf_mgroups'])))
		{
			if (!$menu_editor['smf_mgroups'][$data['href']])
				$temp['fill_array'][] = 0;
		}

		// If this returns = to the original, they aren't in any allowed membergroups.
		if (!empty($temp['fill_array']) && in_array(0, array_values($temp['fill_array'])) || $data['href'] == '?action=moderate' && !$context['user']['can_mod'] || $data['href'] == '?action=logout' && $user_info['is_guest'] || ($data['href'] == '?action=login' || $data['href'] == '?action=register') && !$user_info['is_guest'])
			$temp['final_shown'][$data['id_button']] = 0;
		else
			$temp['final_shown'][$data['id_button']] = 1;


		// The "actual" array ;)
		if (!empty($temp['final_shown'][$data['id_button']]))
		{
			if (!empty($defined_set))
			{
				$menu_editor[$defined_set]['actual'][$data['id_parent']][$data['id_button']] = array();
				$menu_editor[$defined_set]['actual'][$data['id_parent']][$data['id_button']] = array(
					'id_button' => $data['id_button'],
					'title' => $data['name'],
					'show' => $temp['final_shown'][$data['id_button']],
					'href' => $temp['final_href'],
					'target' => $temp['final_target'],
					'level' => $data['level'],
					'id_parent' => $data['id_parent']
				);
			}
			else
			{
				$menu_editor['actual'][$data['name']] = array();
				$menu_editor['actual'][$data['name']] = array(
					'id_button' => $data['id_button'],
					'name' => $data['name'],
					'show' => $temp['final_shown'][$data['id_button']],
					'href' => $temp['final_href'],
					'target' => $temp['final_target'],
					'level' => $data['level'],
					'id_parent' => $data['id_parent']
				);
			}
		}
		// A simple array of id -> name results.
		$menu_editor['full_id_array'][$data['id_button']] = $data['name'];

	}

	// Let's play by the rules.
	$smcFunc['db_free_result']($query);

}

/**
 * menu_items()
 * 
 * @param mixed $start
 * @param mixed $items_per_page
 * @param mixed $sort
 * @return
 */
function menu_items($start, $items_per_page, $sort)
{

	// Globalize everything we'll need...
	global $context, $txt, $scripturl, $settings;
	global $smcFunc, $sourcedir, $menu_editor;

	// Load our language
	if (loadLanguage('/menu_language/main') === false)
		loadLanguage('/menu_language/main');

	// Query time folks!
	$query = $smcFunc['db_query']('', '
		SELECT mi.id_button, mi.name, mi.href, mi.target,
			mi.link_type, mi.button_order, mi.level,
			mi.id_parent, mi.has_children
		FROM {db_prefix}menu_items AS mi
		WHERE level = {int:base}
		ORDER BY ' . $sort . '
		LIMIT ' . $start . ', ' . $items_per_page,
		array(
			'base' => 0,
		)
	);

	// Empty arrays
	$menu_items = array();
	$menu_editor['shown_to'] = array();

	// Query Results...
	while ($data = $smcFunc['db_fetch_assoc']($query))
	{

		// Final href...
		if ($data['link_type'] != 1)
			$temp['final_href'] = '<a href="' . $data['href'] . '" target="_blank" title="' . $data['href'] . '">' . $data['href'] . '</a>';
		elseif ($data['link_type'] == 1 && $data['href'] == '01001001010011000100110001001101')
			$temp['final_href'] = $txt['internal_link'] . ': <a href="' . $scripturl . '" target="_blank" title="' . $txt['home'] . '">' . $txt['home'] . '</a>';
		else
		{
			$searches = array(
				'?action=',
				'pm',
				'mlist'
			);
			$replaces = array(
				'',
				'personal messages',
				'memberlist'
			);
			$display = $smcFunc['ucwords'](str_replace($searches, $replaces, $data['href']));
			$temp['final_href'] = $txt['internal_link'] . ': <a href="' . $scripturl . $data['href'] . '" target="_blank" title="' . $display . '">' . $display . '</a>';
		}

		// Target
		switch ($data['target'])
		{
			case 1:
				$temp['target'] = $txt['menu_editor_parent'];
			break;
			case 2:
				$temp['target'] = $txt['menu_editor_blank'];
			break;
			case 3:
				$temp['target'] = $txt['menu_editor_top'];
			break;
			default:
				$temp['target'] = $txt['menu_editor_self'];
			break;
		}

		// Sprintf for Deleting.
		$sprintf = sprintf($txt['menu_editor_confirm_delete'], $data['name']);

		// Then, the nicely formatted array.
		$menu_items[$data['id_button']] = array(
			'id_button' => $data['id_button'],
			'name' => $data['name'],
			'href' => $temp['final_href'],
			'target' => $temp['target'],
			'children' => !empty($data['has_children']) && $data['level'] == 0 ? '<a href="#" onclick="return false;" id="child_link_' . $data['id_button'] . '" class="children">' . count(array_keys($menu_editor['children']['items'][$data['id_button']])) . '&nbsp;' . (count(array_keys($menu_editor['children']['items'][$data['id_button']])) >= 2 ? $txt['menu_editor_children'] : $txt['menu_editor_child']) . '</a>' : '<img src="' . $settings['images_url'] . '/menu_images/cross.png" title="' . $txt['lab360_no'] . $txt['menu_editor_children'] . '" alt="' . $txt['lab360_no'] . $txt['menu_editor_children'] . '" />',
			'button_order' => $data['button_order'],
			'actions' => '
				<a href="' . $scripturl. '?action=admin;area=menueditor;sa=modify;button=' . $data['id_button'] . '" target="_self" style="text-decoration: none;">
					<img src="' . $settings['images_url'] . '/menu_images/pencil.png" alt="[' . $txt['menu_editor_modify'] . ']" title="' . $txt['menu_editor_modify'] . '" border="0" />
				</a>
				<a href="' . $scripturl . '?action=admin;area=menueditor;sa=remove;button=' . $data['id_button'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" target="_self" onclick="return confirm(\'' . $sprintf . '\');" style="text-decoration: none;">
					<img src="' . $settings['images_url'] . '/menu_images/delete.png" alt="[' . $txt['menu_editor_delete'] . ']" title="' . $txt['menu_editor_delete'] . '" border="0" />
				</a>
			'
		);
	}

	// Free the results.
	$smcFunc['db_free_result']($query);

	// Return the array.
	return $menu_items;

}

/**
 * menu_item_count()
 * 
 * @return
 */
function menu_item_count()
{

	// 1) Globalize.
	global $smcFunc;

	// 2) Query.
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(id_button) AS menu_item_count
		FROM {db_prefix}menu_items
		WHERE level = {int:base}',
		array(
			'base' => 0
		)
	);

	// 3) The Count
	list ($count) = $smcFunc['db_fetch_row']($request);

	// 4a) Free Results.
	$smcFunc['db_free_result']($request);

	// 4b) Return.
	return $count;

}

/**
 * children_items()
 * 
 * @param mixed $start
 * @param mixed $items_per_page
 * @param mixed $sort
 * @param mixed $params
 * @return
 */
function children_items($start, $items_per_page, $sort, $params = array())
{

	// Globalization!
	global $txt, $scripturl, $settings, $context;
	global $smcFunc, $sourcedir, $menu_editor;

	// The Query...
	$query = $smcFunc['db_query']('', '
		SELECT mi.id_button, mi.name, mi.href, mi.target,
			mi.link_type, mi.button_order,
			mi.level, mi.id_parent, mi.has_children
		FROM {db_prefix}menu_items AS mi
		WHERE level = {int:children} AND id_parent = {int:parent}
		ORDER BY ' . $sort . '
		LIMIT ' . $start . ', ' . $items_per_page,
		array(
			'children' => (int) $params + 1,
			'parent' => (int) $_REQUEST['button']
		)
	);

	// Variable = Empty Array.
	$children_items = array();

	// Save our destination.
	switch ($params + 2)
	{
		case 1:
			$array_location = 'children';
		break;
		case 2:
			$array_location = 'grandchildren';
		break;
		default:
			$array_location = '';
		break;
	}

	// Go ahead and go through the results.
	while ($data = $smcFunc['db_fetch_assoc']($query))
	{

		// Final href...
		if ($data['link_type'] != 1)
			$temp['final_href'] = '<a href="' . $data['href'] . '" target="_blank" title="' . $data['href'] . '">' . $data['href'] . '</a>';
		elseif ($data['link_type'] == 1 && $data['href'] == '01001001010011000100110001001101')
			$temp['final_href'] = $txt['internal_link'] . ': <a href="' . $scripturl . '" target="_blank" title="' . $txt['home'] . '">' . $txt['home'] . '</a>';
		else
		{
			$searches = array(
				'?action=',
				'pm',
				'mlist'
			);
			$replaces = array(
				'',
				'personal messages',
				'memberlist'
			);
			$display = ucwords(str_replace($searches, $replaces, $data['href']));
			$temp['final_href'] = $txt['internal_link'] . ': <a href="' . $scripturl . $data['href'] . '" target="_blank" title="' . $display . '">' . $display . '</a>';
		}

		// Target...
		switch ($data['target'])
		{
			case 1:
				$temp['target'] = $txt['menu_editor_parent'];
			break;
			case 2:
				$temp['target'] = $txt['menu_editor_blank'];
			break;
			case 3:
				$temp['target'] = $txt['menu_editor_top'];
			break;
			default:
				$temp['target'] = $txt['menu_editor_self'];
			break;
		}

		// Sprintfing for deletion.
		$sprintf = sprintf($txt['menu_editor_confirm_delete'], $data['name']);

		// Define $array_location.
		if (!empty($array_location) && !empty($data['has_children']))
			$array_shortcut = $menu_editor[$array_location]['items'][$data['id_button']];
		elseif (empty($array_location) && !empty($data['has_children']))
			$array_shortcut = $menu_editor['items'][$data['id_parent']];

		// Format the list array.
		$children_items[$data['id_button']] = array(
			'id_button' => $data['id_button'],
			'name' => $data['name'],
			'href' => $temp['final_href'],
			'target' => $temp['target'],
			'children' => !empty($data['has_children']) ? '<a href="#" onclick="return false;" id="child_link_' . $data['id_button'] . '" class="children">' . count(array_keys($array_shortcut)) . '&nbsp;' . (count(array_keys($array_shortcut)) >= 2 ? $txt['menu_editor_children'] : $txt['menu_editor_child']) . '</a>' : '<img src="' . $settings['images_url'] . '/menu_images/cross.png" title="' . $txt['lab360_no'] . $txt['menu_editor_children'] . '" alt="' . $txt['lab360_no'] . $txt['menu_editor_children'] . '" />',
			'button_order' => $data['button_order'],
			'actions' => '
				<a href="' . $scripturl. '?action=admin;area=menueditor;sa=modify;button=' . $data['id_button'] . ';parent=' . $data['id_parent'] . '" target="_self" style="text-decoration: none;">
					<img src="' . $settings['images_url'] . '/menu_images/pencil.png" alt="[' . $txt['menu_editor_modify'] . ']" title="' . $txt['menu_editor_modify'] . '" border="0" />
				</a>
				<a href="' . $scripturl . '?action=admin;area=menueditor;sa=remove;button=' . $data['id_button'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" target="_self" onclick="return confirm(\'' . $sprintf . '\');" style="text-decoration: none;">
					<img src="' . $settings['images_url'] . '/menu_images/delete.png" alt="[' . $txt['menu_editor_delete'] . ']" title="' . $txt['menu_editor_delete'] . '" border="0" />
				</a>
			'
		);
	}

	// Free the results.
	$smcFunc['db_free_result']($query);

	// Return the array.
	return $children_items;

}

/**
 * children_item_count()
 * 
 * @param mixed $params
 * @return
 */
function children_item_count($params = array())
{

	// Globalize.
	global $smcFunc;

	// Request.
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(id_button) AS menu_item_count
		FROM {db_prefix}menu_items
		WHERE level = {int:children} AND id_parent = {int:parent}',
		array(
			'children' => (int) $params,
			'parent' => (int) $_REQUEST['button']
		)
	);

	// Count.
	list ($count) = $smcFunc['db_fetch_row']($request);

	// Free Results.
	$smcFunc['db_free_result']($request);

	// Return Variable.
	return $count;

}

/**
 * newMenuButton()
 * 
 * @return
 */
function newMenuButton()
{

	// Globalize everything we'll need...
	global $smcFunc;

	// Validate our session.
	checkSession('post', '', true);

	// Button Order
	if (isset($_POST['button_order']))
		$temp['final_order'] = 1;
	elseif (!isset($_POST['button_order']) && (isset($_POST['placement']) && $_POST['placement'] != 0))
		$temp['final_order'] = $_POST['placement_button'] + 1;
	else
		$temp['final_order'] = $_POST['placement_button'];

	// Button Order; Continued, for more fun!
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}menu_items
		SET button_order = button_order + 1
		WHERE button_order >= {int:placement}
			AND level = {int:level}',
		array(
				'placement' => $temp['final_order'],
				'level' => $_POST['level']
		)
	);

	// And, you are not a parent!
	if (!empty($_REQUEST['parent']))
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}menu_items
			SET has_children = 1
			WHERE id_button = {int:parent_button}',
			array(
				'parent_button' => (int) $_REQUEST['parent']
			)
		);
	}

	// Parent?
	if (!isset($_POST['id_parent']))
		$_POST['id_parent'] = 0;

	// And...begin.
	$smcFunc['db_insert']('insert',
		'{db_prefix}menu_items',
		array(
			'name' => 'string',
			'href' => 'string',
			'target' => 'int',
			'link_type' => 'int',
			'button_order' => 'int',
			'level' => 'int',
			'id_parent' => 'int',
		),
		array(
			$smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES),
			$smcFunc['htmlspecialchars']($_POST['link_type'] == 1 ? $_POST['internal_link'] : $_POST['external_link'], ENT_QUOTES),
			$_POST['target'],
			$_POST['link_type'],
			$temp['final_order'],
			$_POST['level'],
			$_POST['id_parent'],
		),
		array(
			'id_button'
		)
	);

	// Then, redirect!
	redirectexit('action=admin;area=menueditor;sa=manage;added');

}

/**
 * modifyMenuButton()
 * 
 * @param string $bid
 * @return
 */
function modifyMenuButton($bid = '')
{

	// Fatal if empty.
	if (empty($bid))
		fatal_error(sprintf($txt['sorry_no_action'], $txt['lab360_modify']), true);

	// Globalize everything we'll need...
	global $context, $smcFunc, $id_shortcut, $menu_editor;

	// Validate our session.
	checkSession('post', '', true);

	// Get down to their level. ;)
	if ($id_shortcut['level'] == 0)
	{
		$first_button = reset($menu_editor['items']);
		$last_button = end($menu_editor['items']);
	}
	elseif ($id_shortcut['level'] == 1)
	{
		$first_button = reset($menu_editor['children']['items'][$id_shortcut['id_parent']]);
		$last_button = end($menu_editor['children']['items'][$id_shortcut['id_parent']]);
	}
	else
	{
		$first_button = reset($menu_editor['grandchildren']['items'][$id_shortcut['id_parent']]);
		$last_button = end($menu_editor['grandchildren']['items'][$id_shortcut['id_parent']]);
	}

	// Ordering, the best part! >:)
	$bid = (int) $bid;
	$skip = $_POST['placement'] == 0 && $_POST['placement_button'] - 1 == $id_shortcut['button_order'] || $_POST['placement'] == 1 && $_POST['placement_button'] + 1 == $id_shortcut['button_order'] ? true : false;
	if ($_POST['placement'] != '01001001010011000100110001001101' && $_POST['placement_button'] != '01001001010011000100110001001101' && $skip == false)
	{

		// Odd Operations = Our Special Attention.
		if ($bid == $first_button['id_button'] && $_POST['placement'] == 0 && $_POST['placement_button'] != $last_button['id_button'])
			$_POST['placement_button'] = $_POST['placement_button'] - 1;
		elseif ($bid == $last_button['id_button'] && $_POST['placement'] == 1)
			$_POST['placement_button'] = $_POST['placement_button'] + 1;
		elseif ($bid != $first_button['id_button'] && $bid != $last_button['id_button'])
		{
			if ($_POST['placement'] == 0)
				$_POST['placement_button'] = $_POST['placement_button'];
			elseif ($_POST['placement'] == 1)
				$_POST['placement_button'] = $_POST['placement_button'] + 1;
		}

		// Temporarily remove our buttons order.
		$old_order_id = $id_shortcut['button_order'];
		$changed_order = true;
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}menu_items
			SET button_order = 0
			WHERE id_button = {int:bid}',
			array(
				'bid' => $bid
			)
		);

		// Move all items from position up, one down.
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}menu_items
			SET button_order = button_order - 1
			WHERE button_order >= {int:current_placement}
				AND level = {int:current_level}',
			array(
				'current_placement' => $old_order_id,
				'current_level' => $id_shortcut['level']
			)
		);

		// Our desired position, huh? Move all above it one up.
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}menu_items
			SET button_order = button_order + 1
			WHERE button_order >= {int:placement_button}
				AND level = {int:current_level}',
			array(
				'placement_button' => (int) $_POST['placement_button'],
				'current_level' => (int) $_POST['level']
			)
		);

		// And, more placement stuff.
		if ($_POST['placement'] == 0 && ($first_button['button_order'] + 1 == $_POST['placement_button']))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}menu_items
				SET button_order = button_order + 1
				WHERE button_order = {int:placement_button}
					AND level = {int:current_level}
					AND id_button != {int:current_button}',
				array(
					'placement_button' => (int) $_POST['placement_button'],
					'current_level' => (int) $_POST['level'],
					'current_button' => $id_shortcut['id_button']
				)
			);
		}

	}

	// Query Time
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}menu_items
		SET name = {string:name},
			href = {string:href},
			target = {int:target},
			link_type = {int:link_type},
			button_order = {int:button_order}
		WHERE id_button = {int:id_button}',
		array(
			'id_button' => $bid,
			'name' => !empty($_POST['name']) ? $smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES) : '',
			'href' => !empty($_POST['internal_link']) || !empty($_POST['external_link']) ? $smcFunc['htmlspecialchars']($_POST['link_type'] == 1 ? $_POST['internal_link'] : $_POST['external_link'], ENT_QUOTES) : $id_shortcut['href'],
			'target' => !empty($_POST['target']) ? (int) $_POST['target'] : $id_shortcut['target'],
			'link_type' => !empty($_POST['link_type']) ? (int) $_POST['link_type'] : $id_shortcut['link_type'],
			'button_order' => isset($changed_order) ? (int) $_POST['placement_button'] : $id_shortcut['button_order']
		)
	);

	// Then redirect.
	redirectexit('action=admin;area=menueditor;sa=manage;modified');

}

/**
 * removeMenuButton()
 * 
 * @param string $bid
 * @return
 */
function removeMenuButton($bid = '')
{

	// Extra Validation...
	if (empty($bid))
		fatal_error(sprintf($txt['sorry_no_action'], $txt['lab360_remove']), true);

	// No goofing around here, folks.
	global $smcFunc, $menu_editor;

	// Validate our session.
	checkSession('get', '', true);

	// Oh gosh...here we go!
	$bid = (int) $bid;
	if (!empty($menu_editor['children']['items'][$bid]))
	{
		// Do we have any children?
		foreach ($menu_editor['children']['items'][$bid] as $key => $value)
		{
			// How about grandchildren?
			if (!empty($menu_editor['grandchildren']['items'][$key]))
			{
				// # !! Eventually, this needs to be WHERE id_button IN ({array_int:keys})
				foreach ($menu_editor['grandchildren']['items'][$key] as $second_key => $second_value)
				{
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}menu_items
						WHERE id_button = {int:id_button}',
						array(
							'id_button' => $second_key
						)
					);
				}
			}

			// Finally, delete the children.
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}menu_items
				WHERE id_button = {int:id_button}',
				array(
					'id_button' => $key
				)
			);
		}
	}

	// Is this our last [grand]child? The parent is no longer a parent then.
	if (array_key_exists($bid, $menu_editor['grandchildren']['id_array']) == true)
		$defined_set = 'grandchildren';
	elseif (array_key_exists($bid, $menu_editor['children']['id_array']) === true)
		$defined_set = 'children';

	// The "Query Shortcut" Variable.
	if (isset($defined_set))
		$query_shortcut = $menu_editor[$defined_set]['items'][$menu_editor['parent_ids'][$bid]][$bid];
	else
		$query_shortcut = $menu_editor['items'][$bid];

	// Set has_children = false;.
	if (isset($defined_set) && count($menu_editor[$defined_set]['items'][$menu_editor['parent_ids'][$bid]]) == 1)
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}menu_items
			SET has_children = 0
			WHERE id_button = {int:id_parent}',
			array(
				'id_parent' => $query_shortcut['id_parent']
			)
		);
	}

	// Woah there, back up the truck.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}menu_items
		SET button_order = button_order - 1
		WHERE button_order >= {int:placement}
			AND level = {int:level}',
		array(
			// This is the old id...where the gap will be.
			'placement' => $query_shortcut['button_order'],
			// Only buttons on the same level, folks.
			'level' => $query_shortcut['level']
		)
	);

	// Finish it off by deleting the main level button.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}menu_items
		WHERE id_button = {int:id_button}',
		array(
			'id_button' => $bid
		)
	);

	// Then redirect.
	redirectexit('action=admin;area=menueditor;removed');

}

/**
 * subsFunc1()
 * 
 * @return
 */
function subsFunc1()
{

	// Globalize everything we'll need...
	global $scripturl, $context, $txt, $menu_editor;

	// Redefine the array
	$buttons = array();

	// Call our items.
	MenuItems();

	// Top Level
	if (!empty($menu_editor['actual']))
	{
		$menu_editor['first_button'] = reset($menu_editor['actual']);;
		$menu_editor['last_button'] = end($menu_editor['actual']);
		foreach ($menu_editor['actual'] as $key => $value)
		{
			$buttons[$value['id_button']] = array(
				'title' => $value['name'],
				'href' => $value['href'],
				'show' => true,
				'target' => $value['target'],
				'sub_buttons' => array(
				),
				'is_last' => $value['id_button'] == $menu_editor['last_button'] ? !$context['right_to_left'] : '',
				'level' => 0
			);
			// Do we have Children?
			if (!empty($menu_editor['children']['actual'][$value['id_button']]))
			{
				$buttons[$value['id_button']]['sub_buttons'] = array();
				foreach ($menu_editor['children']['actual'][$value['id_button']] as $act => $sub_value)
				{
					// Introduce the Grandchildren - If we have any.
					if (!empty($menu_editor['grandchildren']['actual'][$sub_value['id_button']]))
						$temp[$sub_value['id_button']]['sub_buttons'] = $menu_editor['grandchildren']['actual'][$sub_value['id_button']];
					else
						$temp[$sub_value['id_button']]['sub_buttons'] = array();

					// Actually add the Children!
					$buttons[$sub_value['id_parent']]['sub_buttons'][$sub_value['id_button']] = array(
						'title' => $sub_value['title'],
						'href' => $sub_value['href'],
						'show' => true,
						'target' => $sub_value['target'],
						'sub_buttons' => $temp[$sub_value['id_button']]['sub_buttons'],
						'is_last' => '',
						'level' => 1
					);
				}
			}
		}
	}
	// No menu items? And we can admin? Show two items so we're not stuck! # Saves support time ::angel_smiley::
	elseif (empty($menu_editor['actual']) && $context['allow_admin'])
	{
		$buttons = array(
			'home' => array(
				'title' => $txt['home'],
				'href' => $scripturl,
				'show' => true,
				'target' => '_self',
				'sub_buttons' => array(
				),
				'is_last' => $context['right_to_left'],
				'level' => 0
			),
			'admin' => array(
				'title' => $txt['admin'],
				'href' => $scripturl . '?action=admin',
				'show' => $context['allow_admin'],
				'target' => '_self',
				'sub_buttons' => array(
				),
				'is_last' => !$context['right_to_left'],
				'level' => 0
			)
		);
	}

	// Then return the array to Subs.php!
	return $buttons;

}

/**
 * subsFunc2()
 * 
 * @param mixed $menu_buttons
 * @return
 */
function subsFunc2($menu_buttons)
{

	// Globalize.
	global $context, $scripturl;

	// Loop through our buttons.
	foreach ($menu_buttons as $key => $value)
	{
		// If we don't have any empty href.
		if (!empty($value['href']))
		{

			// Let's clean it up a little...
			$temp['strstr'][$key] = str_replace('action=', '', strstr($value['href'], 'action='));
	
			/* Okay, so...
				1) The current loop item is an INTERNAL link.
				2) Our current action IS this button.
				3) And, only main level links are highlighted.
			*/			
			if (!empty($temp['strstr'][$key]) && $temp['strstr'][$key] == $context['current_action'] && $value['level'] == 0)
			{

				// This is active, then.
				$context['menu_buttons'][$key]['active_button'] = true;

				// We need to know...
				$active_defined = true;

				// Kill the loop, since we're done now.
				break;

			}
			// Is our link the forum home and are we on level 1? Then highlight it.
			elseif ($value['href'] == $scripturl && $value['level'] == 0)
			{
				$scripturl_defined = $key;
			}
		}
	}

	// No other button is active, and home is, so go ahead and highlight it.
	if (!isset($active_defined) && isset($scripturl_defined))
		$context['menu_buttons'][$scripturl_defined]['active_button'] = true;

}

/**
 * children_ajax()
 * 
 * @return
 */
function children_ajax()
{

	// Globalize everything we'll need.
	global $context, $txt;

	// Then return the AJAX to the <head>.
	$context['html_headers'] .= "\n" . '
		<script type="text/javascript">
		//<![CDATA[
			$(document).ready(function() {
				$(".children").click(function() {
					$.ajax({
						url: smf_scripturl + "?ajax_request=menu_children",
						type: "post",
						data: {menu_id: $(this).attr(\'id\')},
						dataType: "html",
						success: function(data) {
							var $dialog = $(\'<div></div>\')
								.html(data)
								.dialog({
									autoOpen: false,
									title: "Button Children",
									show: "slide",
									resizable: true,
									closeOnEscape: false,
									closeText: "",
									hide: "explode",
									modal: true,
									width: 500
								});
								$dialog.dialog("open");
						}
					});
				});
			});
		//]]>
		</script>
	';

}