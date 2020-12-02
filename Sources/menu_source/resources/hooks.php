<?php
/**
 * Menu Editor Lite
 *
 * @file ./menu_source/resources/hooks.php
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
 * hook__menu_admin_area()
 * 
 * @param mixed $admin_areas
 * @return
 */
function hook__menu_admin_area(&$admin_areas)
{

	// Load the language.
	loadLanguage('/menu_language/main');

	// Globalize $txt;.
	global $txt;

	// Our array, we'll insert it soon.
	$menueditor_admin = array(
		'menueditor' => array(
			'label' => $txt['menu_editor'],
			'file' => '/menu_source/main.php',
			'function' => 'ManageMenuEditor',
			'icon' => '../menu_images/ui-breadcrumb.png',
			'subsections' => array(
				'manage' => array(
					$txt['menu_editor_manage']
				),
				'add' => array(
					$txt['menu_editor_add']
				)
			)
		)
	);

	// Then we make two pieces, and insert ours in between the two.
	$first = array_slice($admin_areas['config']['areas'], 0, 2);
	$second = array_slice($admin_areas['config']['areas'], 2);
	$admin_areas['config']['areas'] = array_merge($first, $menueditor_admin, $second);

}