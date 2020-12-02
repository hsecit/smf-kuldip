<?php
/**
 * Menu Editor Lite
 *
 * @file ./menu_language/main.english.php
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

// A couple "main" strings.
$txt['menu_editor'] = 'Menu Editor Lite';
$txt['menu_editor_manage'] = 'Manage';
$txt['menu_editor_add'] = 'Add';
$txt['menu_editor_modify'] = 'Modify';
$txt['menu_editor_delete'] = 'Delete';
$txt['menu_editor_button'] = 'Button';
$txt['menu_menu'] = 'Menu';

// Operations
$txt['no_change'] = 'No Change';
$txt['operations'] = 'Operations';
$txt['oper_before'] = 'Before';
$txt['oper_after'] = 'After';
$txt['oper_above'] = 'Above';
$txt['oper_below'] = 'Below';

// Desc
$txt['menu_editor_desc'] = '<strong>Powerful &amp; Customizable.</strong> Finally, a powerful solution for managing SMF\'s menu, that is completely customizable!';

// Some field->desc strings.
$txt['name_desc'] = 'The name that will be displayed.';

$txt['placement'] = 'Placement';
$txt['pacement_desc'] = 'The placement of this button.';

$txt['link_type'] = 'Type';
$txt['link_type_desc'] = 'Whether the link is internal or external.';
$txt['internal_link_desc'] = 'Specify the action to link to.';
$txt['select_internal_link'] = 'Select Internal Link';
$txt['guest_only'] = 'Guest Only Links:';
$txt['member_only'] = 'Member Only Links';
$txt['other_links'] = '------- Other -------';
$txt['link_rss'] = 'RSS Feed';
$txt['link_scripturl'] = 'Forum Home';

$txt['external_link_desc'] = 'Specify a URL to link to.';
$txt['target_desc'] = 'What target the link opens in.';

// Possible Targets.
$txt['menu_editor_self'] = 'Self';
$txt['menu_editor_parent'] = 'Parent';
$txt['menu_editor_blank'] = 'Blank';
$txt['menu_editor_top'] = 'Top';

$txt['internal_link'] = 'Internal Link';
$txt['external_link'] = 'External Link';

// Some labFrame strings.
$txt['lab360_no'] = 'No';
$txt['lab360_plural'] = 's';
$txt['lab360_notification'] = 'Notification';
$txt['lab360_close'] = 'Close';
$txt['lab360_error'] = 'error';

$txt['menu_editor_home'] = 'Home';
$txt['menu_editor_children'] = 'Children';
$txt['menu_editor_child'] = 'Child';

$txt['we_have_a_problem'] = '<span class="red bold">Uh oh!</span> Just kidding. There are currently no menu items.';
$txt['uh_oh_children'] = '<span class="red bold">Uh oh!</span> Just kidding. There are currently no children items for this button.';

// Some good 'ol Sprintf()'s.
$txt['notification_base'] = 'Your button has been successfully %s!';
$txt['lab360_added'] = 'added';
$txt['lab360_modified'] = 'modified';
$txt['lab360_removed'] = 'removed';
$txt['sorry_no_action'] = 'Sorry! We were unable to %s the menu item. Please try again.';
$txt['lab360_remove'] = 'remove';
$txt['lab360_modify'] = 'modify';
$txt['modify_page_title'] = 'Modify %s Button';

// Column Headers
$txt['menu_editor_order'] = 'Order';
$txt['menu_editor_name'] = 'Name';
$txt['menu_editor_link'] = 'Link';
$txt['menu_editor_target'] = 'Target';
$txt['menu_editor_children'] = 'Children';
$txt['menu_editor_actions'] = 'Actions';

// Internal Links
$txt['menu_editor_login'] = 'Login';
$txt['menu_editor_register'] = 'Register';
$txt['menu_editor_admin'] = 'Admin';
$txt['menu_editor_logout'] = 'Logout';
$txt['menu_editor_moderate'] = 'Moderate';
$txt['menu_editor_pm'] = 'Personal Messages';
$txt['menu_editor_profile'] = 'Profile';
$txt['menu_editor_unread'] = 'Unread Posts';
$txt['menu_editor_unreadreplies'] = 'Unread Replies';
$txt['menu_editor_calendar'] = 'Calendar';
$txt['menu_editor_clock'] = 'Clock';
$txt['menu_editor_groups'] = 'Groups';
$txt['menu_editor_help'] = 'Help';
$txt['menu_editor_mlist'] = 'Memberlist';
$txt['menu_editor_search'] = 'Search';
$txt['menu_editor_stats'] = 'Forum Stats';
$txt['menu_editor_who'] = 'Who\'s Online';

// Errors
$txt['empty_button_name'] = 'A Button Name must be entered.';
$txt['empty_button_type'] = 'Link Type must be selected.';
$txt['internal_link_required'] = 'Internal Link Selection is required.';
$txt['external_link_required'] = 'External Link is required.';
$txt['matthew_is_lazy'] = '&nbsp;already exists. Change the button name or&nbsp;';
$txt['lab360_sorry_but'] = 'Sorry! But we encountered&nbsp;';
$txt['singular_error'] = '. Here it is...';
$txt['plural_errors'] = 's. Here they are...';

// Confirm Delete, por favor!
$txt['menu_editor_confirm_delete'] = 'Are you sure you want to delete the %s button?';