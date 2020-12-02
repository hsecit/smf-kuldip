<?php
/**
 * Menu Editor Lite
 *
 * @file ./menu_source/main.php
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
 * ManageMenuEditor()
 * 
 * @return
 */
function ManageMenuEditor()
{

	// Globalize everything we'll need...
	global $context, $txt, $sourcedir, $scripturl;
	global $modSettings, $settings, $menu_editor;

	// Load our stuff ;P
	loadTemplate('/menu_templates/main');
	if (loadLanguage('/menu_language/main') == false)
		loadLanguage('/menu_language/main');

	// Require the files we'll need.
	require_once($sourcedir . '/menu_source/resources/main.php');
	require_once($sourcedir . '/Subs-List.php');

	// Here is our main menu item array
	MenuItems();

	// Some final head content...
	$context['html_headers'] .= "\n" . '
		<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/css/menu_css/main.css" />
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
		<script type="text/javascript" src="' . $settings['theme_url'] . '/scripts/menu_scripts/main.js"></script>
		<script type="text/javascript">
		//<![CDATA[
			$(document).ready(function() {
				$("#profile_success").delay(2000).slideUp(2500);
			});
		//]]>
		</script>
	';

	// "Debugging JavaScript is as fun as...well, algebra."
	children_ajax();

	// Throw our copyright into template layers...
	$context['template_layers'][] = 'copyright';

	// Define a page title...
	$context['page_title'] = $txt['menu_editor'];

	// Sub-Action time, folks!
	if (isset($_REQUEST['sa']) && ($_REQUEST['sa'] == 'add' || $_REQUEST['sa'] == 'modify'))
		MenuEditorMaddify();
	elseif (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'remove' && isset($_REQUEST['button']) && in_array($_REQUEST['button'], array_keys($menu_editor['full_id_array'])))
		removeMenuButton($_REQUEST['button']);

	// Load up all the tabs...
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['menu_editor'],
		'help' => '',
		'description' => $txt['menu_editor_desc'],
		'tabs' => array(
			'manage' => array(
			),
			'add' => array(
			)
		)
	);

	// Webkit vs. Non-Webkit Styling.
	if ($context['browser']['is_webkit'])
	{
		// Webkit...
		$context['html_headers'] .= "\n" . '
			<style type="text/css">
			/*<![CDATA[*/
				.big_input {
					padding: 6px;
				}
				.big_select {
					padding: 4px;
				}
			/*]]>*/
			</style>
		';
	}
	else
	{
		// Non-Webkit (eww)
		$context['html_headers'] .= "\n" . '
			<style type="text/css">
			/*<![CDATA[*/
				.big_input {
					padding: 4px;
				}
				.big_select {
					padding: 4px;
				}
			/*]]>*/
			</style>
		';
	}

	// Load Notifications; they work the same way errors do...
	/* #
		DEVELOPMENT:
			Change notifications to work on ONE $_GET var ($_REQUEST['notify'])
			Change logic to use switch() on $_REQUEST['notify']
			Change text strings to one string, using a sprintf based on the switch result.
	*/
	if (isset($_REQUEST['modified']))
		$menu_editor['notification'] = sprintf($txt['notification_base'], $txt['lab360_modified']);
	elseif (isset($_REQUEST['removed']))
		$menu_editor['notification'] = sprintf($txt['notification_base'], $txt['lab360_removed']);
	elseif (isset($_REQUEST['added']))
		$menu_editor['notification'] = sprintf($txt['notification_base'], $txt['lab360_added']);

	// Our list options.
	$listOptions = array(
		'id' => 'menu_items',
		'items_per_page' => $modSettings['defaultMaxMessages'],
		'no_items_label' => $txt['we_have_a_problem'],
		'base_href' => $scripturl . '?action=admin;area=menueditor',
		'default_sort_col' => 'button_order',
		'get_items' => array(
			'function' => 'menu_items',
			'params' => array()
		),
		'get_count' => array(
			'function' => 'menu_item_count',
			'params' => array()
		),
		'columns' => array(
			'button_order' => array(
				'header' => array(
					'value' => $txt['menu_editor_order']
				),
				'data' => array(
					'db' => 'button_order',
					'class' => 'centertext'
				),
				'sort' => array(
					'default' => 'mi.button_order',
					'reverse' => 'mi.button_order DESC'
				)
			),
			'name' => array(
				'header' => array(
					'value' => $txt['menu_editor_name']
				),
				'data' => array(
					'db' => 'name',
					'class' => 'centertext',
					'style' => 'max-width: 150px; min-width: 100px;'
				),
				'sort' => array(
					'default' => 'mi.name',
					'reverse' => 'mi.name DESC'
				)
			),
			'href' => array(
				'header' => array(
					'value' => $txt['menu_editor_link']
				),
				'data' => array(
					'db' => 'href',
					'class' => 'centertext',
					'style' => 'max-width: 300px; min-width: 100px;'
				),
				'sort' => array(
					'default' => 'mi.href',
					'reverse' => 'mi.href DESC'
				)
			),
			'target' => array(
				'header' => array(
					'value' => $txt['menu_editor_target']
				),
				'data' => array(
					'db' => 'target',
					'class' => 'centertext'
				),
				'sort' => array(
					'default' => 'mi.target',
					'reverse' => 'mi.target DESC'
				)
			),
			'children' => array(
				'header' => array(
					'value' => $txt['menu_editor_children']
				),
				'data' => array(
					'db' => 'children',
					'class' => 'centertext'
				),
				'sort' => array(
					'default' => 'mi.has_children',
					'reverse' => 'mi.has_children DESC'
				)
			),
			'actions' => array(
				'header' => array(
					'value' => $txt['menu_editor_actions']
				),
				'data' => array(
					'db' => 'actions',
					'class' => 'centertext'
				)
			)
		)
	);

	// Create the list
	createList($listOptions);

}

/**
 * MenuEditorMaddify()
 * 
 * @return
 */
function MenuEditorMaddify()
{

	// Globalize everything we'll need...
	global $context, $scripturl, $txt, $modSettings, $settings;
	global $id_shortcut, $menu_editor;

	// Quick validation for moddifying.
	/* # DEVELOPMENT: Look into using array_key_exists(); in many places rather than using in_array(); and array_keys(); */
	if (isset($_REQUEST['button']) && !in_array($_REQUEST['button'], array_keys($menu_editor['full_id_array'])))
		redirectexit('action=admin;area=menueditor;sa=add');
	elseif ($_REQUEST['sa'] == 'modify' && !isset($_REQUEST['button']))
		redirectexit('action=admin;area=menueditor;sa=add');

	// Sub-Template!
	$context['sub_template'] = 'maddify';

	// Logic time!
	if ($_REQUEST['sa'] == 'modify')
	{
		if (!empty($_REQUEST['button']) && in_array($_REQUEST['button'], array_keys($menu_editor['id_array'])))
			$id_shortcut = $menu_editor['items'][$_REQUEST['button']];
		elseif (!empty($_REQUEST['button']) && in_array($_REQUEST['button'], array_keys($menu_editor['children']['id_array'])) && !empty($_REQUEST['parent']) && $menu_editor['parent_ids'][$_REQUEST['button']] == $_REQUEST['parent'])
			$id_shortcut = $menu_editor['children']['items'][$_REQUEST['parent']][$_REQUEST['button']];
		elseif (!empty($_REQUEST['button']) && in_array($_REQUEST['button'], array_keys($menu_editor['grandchildren']['id_array'])) && !empty($_REQUEST['parent']) && $menu_editor['parent_ids'][$_REQUEST['button']] == $_REQUEST['parent'])
			$id_shortcut = $menu_editor['grandchildren']['items'][$_REQUEST['parent']][$_REQUEST['button']];
		else
			redirectexit('action=admin;area=menueditor;sa=manage');
	}

	// Page Title...fun!
	if ($_REQUEST['sa'] == 'modify')
		$context['page_title'] = sprintf($txt['modify_page_title'], $id_shortcut['name']);
	else
		$context['page_title'] = $txt['menu_editor_add'] . '&nbsp;' . $txt['menu_editor_button'];

	// Some last-minute head content
	$context['html_headers'] .= "\n" . '
		<script type="text/javascript">
		//<![CDATA[
			$(document).ready(function() {
				addButtonJS();
			});
		//]]>
		</script>
	';

	// Our links...in neat and organized arrays.
	$link_array = array(
		'guest_only' => array(
			'login', 'register'
		),
		'member_only' => array(
			'admin', 'logout',
			'moderate', 'pm',
			'profile', 'unread',
			'unreadreplies'
		),
		'other' => array(
			'calendar', 'clock',
			'groups', 'help',
			'mlist', 'search',
			'stats', 'who'
		)
	);

	// Where they'll be put...
	$menu_editor['internal_links'] = array();

	// Loop through each link group...
	foreach ($link_array as $id_group => $group)
	{

		// If the group array is not yet set, define it now.
		if (isset($menu_editor['internal_links'][$id_group]) == false)
			$menu_editor['internal_links'][$id_group] = array();

		// Then loop through each item for that group.
		foreach ($link_array[$id_group] as $value)
		{
			// And assign the corresponding text string to each key.
			$menu_editor['internal_links'][$id_group][$value] = $txt['menu_editor_' . $value];
		}
	}

	// If we're maddifying...
	if (isset($_POST['name']))
	{

		// Errors, maybe?
		$menu_editor['errors'] = '';
		if (empty($_POST['name']))
			$menu_editor['errors']['empty_name'] = $txt['empty_button_name'];
		if (empty($_POST['link_type']))
			$menu_editor['errors']['empty_type'] = $txt['empty_button_type'];
		if (!empty($_POST['link_type']) && $_POST['link_type'] == 1 && empty($_POST['internal_link']))
			$menu_editor['errors']['empty_internal_link'] = $txt['internal_link_required'];
		if (!empty($_POST['link_type']) && $_POST['link_type'] == 2 && (empty($_POST['external_link']) || $_POST['external_link'] == 'http://'))
			$menu_editor['errors']['empty_external_link'] = $txt['external_link_required'];
		if (!empty($_POST['name']) && (!isset($id_shortcut) && (in_array($_POST['name'], array_keys($menu_editor['items'])) || in_array($_POST['name'], array_keys($menu_editor['children']['items'])) || in_array($_POST['name'], array_keys($menu_editor['grandchildren']['items'])))))
			$menu_editor['errors']['name_conflict'] =  $_POST['name'] . $txt['matthew_is_lazy'] . '<a href="' . $scripturl . '?action=admin;area=menueditor;sa=modify;button=' . $menu_editor['items'][str_replace(' ', '_', $_POST['name'])]['id_button'] . '" target="_self">' . $txt['menu_editor_modify'] . '&nbsp;' . $_POST['name'] . '</a>';

		// If we can continue...
		if (!is_array($menu_editor['errors']))
		{
			if (!isset($id_shortcut))
				newMenuButton();
			else
				modifyMenuButton($id_shortcut['id_button']);
		}
	}

	// Children Lists
	if (isset($id_shortcut))
	{
		$listOptions = array(
			'id' => 'children_items',
			'items_per_page' => $modSettings['defaultMaxMessages'],
			'no_items_label' => $txt['uh_oh_children'],
			'base_href' => $scripturl . '?action=admin;area=menueditor;sa=modify;button=' . $_REQUEST['button'] . (isset($_REQUEST['parent']) && !empty($_REQUEST['parent']) ? ';parent' . $_REQUEST['parent'] : ''),
			'default_sort_col' => 'button_order',
			'get_items' => array(
				'function' => 'children_items',
				'params' => array(
					$id_shortcut['level']
				)
			),
			'get_count' => array(
				'function' => 'children_item_count',
				'params' => array(
					$id_shortcut['level']
				)
			),
			'columns' => array(
				'button_order' => array(
					'header' => array(
						'value' => $txt['menu_editor_order']
					),
					'data' => array(
						'db' => 'button_order',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'mi.button_order',
						'reverse' => 'mi.button_order DESC'
					),
				),
				'name' => array(
					'header' => array(
						'value' => $txt['menu_editor_name']
					),
					'data' => array(
						'db' => 'name',
						'class' => 'centertext',
						'style' => 'max-width: 150px; min-width: 100px;'
					),
					'sort' => array(
						'default' => 'mi.name',
						'reverse' => 'mi.name DESC'
					)
				),
				'href' => array(
					'header' => array(
						'value' => $txt['menu_editor_link']
					),
					'data' => array(
						'db' => 'href',
						'class' => 'centertext',
						'style' => 'max-width: 300px; min-width: 100px;'
					),
					'sort' => array(
						'default' => 'mi.href',
						'reverse' => 'mi.href DESC'
					)
				),
				'target' => array(
					'header' => array(
						'value' => $txt['menu_editor_target']
					),
					'data' => array(
						'db' => 'target',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'mi.target',
						'reverse' => 'mi.target DESC'
					)
				),
				'actions' => array(
					'header' => array(
						'value' => $txt['menu_editor_actions']
					),
					'data' => array(
						'db' => 'actions',
						'class' => 'centertext'
					)
				)
			)
		);

		// We can't have children in a grandchild button.
		if ($id_shortcut['level'] == 0 || $id_shortcut['level'] == 1)
		{
			$first_half = array_slice($listOptions['columns'], 0, 4, true);
			$second_half = array_slice($listOptions['columns'], 4, 1, true);
			$newColumn['columns'] = array(
				'children' => array(
					'header' => array(
						'value' => $txt['menu_editor_children']
					),
					'data' => array(
						'db' => 'children',
						'class' => 'centertext'
					),
					'sort' => array(
						'default' => 'mi.has_children',
						'reverse' => 'mi.has_children DESC'
					)
				)
			);
			$listOptions['columns'] = array_merge($first_half, $newColumn['columns'], $second_half);
		}

		// Create the list
		createList($listOptions);

	}

}