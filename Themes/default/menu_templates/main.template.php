<?php
/**
 * Menu Editor Lite
 *
 * @file ./menu_templates/main.template.php
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

/**
	- The main template ?action=admin;area=menueditor
*/
function template_main()
{

	// Globalize everything we need...
	global $context, $settings, $menu_editor, $txt;

	// Notifications, anybody?
	if (isset($menu_editor['notification']))
	{
		echo '
			<div id="profile_success" class="rfix">
				<div class="floatleft" style="width: 18px; margin-right: 3px;">
					<img src="', $settings['images_url'], '/menu_images/accept.png" alt="" />
				</div>
				<span>', $menu_editor['notification'], '</span>
			</div>
		';
	}

	// Simple header for our list...
	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['menu_menu'], '&nbsp;', $txt['menu_editor_button'], $txt['lab360_plural'], '</h3>
		</div>
	';

	// Then output our actual list.
	template_show_list('menu_items');

}

/**
	- This template handles both adding and modifying menu items.
*/
function template_maddify()
{

	// Globalize everything we'll need...
	global $context, $scripturl, $settings, $id_shortcut, $menu_editor, $txt;

	// Let's not add so many ternaries here folks...
	$modify = isset($id_shortcut['name']) ? true : false;

	// Some template logic, unfortunately.
	if ($modify === true && !empty($menu_editor['items']) && $id_shortcut['level'] == 0)
		unset($menu_editor['items'][$id_shortcut['id_button']]);
	elseif ($modify === true && !empty($menu_editor['children']['items']) && $id_shortcut['level'] == 1 && !empty($id_shortcut['id_parent']))
		unset($menu_editor['children']['items'][$id_shortcut['id_parent']][$id_shortcut['id_button']]);
	elseif ($modify === true && !empty($menu_editor['grandchildren']['items']) && $id_shortcut['level'] == 2 && !empty($id_shortcut['id_parent']))
		unset($menu_editor['grandchildren']['items'][$id_shortcut['id_parent']][$id_shortcut['id_button']]);

	// Some array pops.
	if ($modify === false && !empty($menu_editor['items']) && count($menu_editor['items']) >= 1 && empty($_REQUEST['parent']))
		$temp['max_item'] = array_pop($menu_editor['items']);
	elseif ($modify === false && !empty($_REQUEST['parent']) && array_key_exists($_REQUEST['parent'], $menu_editor['children']['items']) && count(array_keys($menu_editor['children']['items']) >= 1))
		$temp['max_item'] = array_pop($menu_editor['children']['items'][$_REQUEST['parent']]);
	elseif ($modify === false && !empty($_REQUEST['parent']) && array_key_exists($_REQUEST['parent'], $menu_editor['grandchildren']['items']) && count(array_keys($menu_editor['grandchildren']['items']) >= 1))
		$temp['max_item'] = array_pop($menu_editor['grandchildren']['items'][$_REQUEST['parent']]);

	// Output any errors that are possible, here.
	if (isset($menu_editor['errors']))
	{
		echo '
			<div style="margin-bottom: 15px;" id="notifications">
				<div class="cat_bar" style="height: 28px;">
					<h3 class="catbg">
						<span>', $txt['lab360_notification'], '</span>
						<span class="floatright" style="width: 18px; display: block; margin-top: 6px;">
							<img src="', $settings['images_url'], '/menu_images/cross.png" alt="', $txt['lab360_close'], '" title="', $txt['lab360_close'], '" id="close_notifis" style="cursor: pointer;" />
						</span>
					</h3>
				</div>
				<div class="roundframe rfix">
					<div class="innerframe">
						<div class="content">
							<div id="profile_error">', $txt['lab360_sorry_but'], count($menu_editor['errors']), '&nbsp;' . $txt['lab360_error'], count($menu_editor['errors']) > 1 ? $txt['plural_errors'] : $txt['singular_error'], '
								<ul>';
								foreach ($menu_editor['errors'] as $key => $value)
								{
									echo '<li>', $value, '</li>';
								}
							echo '</ul>
							</div>
						</div>
					</div>
				</div>
				<span class="lowerframe"><span></span></span>
			</div>
		';
	}

	// Then begin.
	echo '
		<div class="cat_bar" style="height: 28px;">
			<h3 class="catbg">', $modify == true ? $txt['menu_editor_modify'] . '&nbsp;' . $id_shortcut['name'] : $txt['menu_editor_add'], '&nbsp;', $txt['menu_editor_button'], '</h3>
		</div>
		<div class="roundframe rfix">
			<div class="innerframe">
				<div class="content">
					<form action="', $scripturl, '?action=admin;area=menueditor;sa=', $_REQUEST['sa'], '', $_REQUEST['sa'] == 'modify' ? ';button=' . $id_shortcut['id_button'] : '', isset($_REQUEST['parent']) ? ';parent=' . $_REQUEST['parent'] : '', '" method="post">
						<!-- NAME -->
						<div class="floatleft" style="width: 30%;">
							<label for="name">
								<span class="bold', isset($menu_editor['errors']['empty_name']) || isset($menu_editor['errors']['name_conflict']) ? ' red' : '', '">', $txt['menu_editor_name'], ':</span>
							</label>
							<div class="smalltext">', $txt['name_desc'], '</div>
						</div>
						<div class="floatright" style="text-align: left; width: 70%;">
							<input type="text" class="input_text big_input" name="name" id="name" maxlength="255" size="60"', isset($_POST['name']) ? ' value="' . $_POST['name'] . '"' : '', isset($id_shortcut['name']) ? ' value="' . $id_shortcut['name'] . '"' : '', isset($menu_editor['errors']['empty_name']) || isset($menu_editor['errors']['name_conflict']) ? ' style="border: 1px solid #ff0000;"' : '', ' />
						</div>
						<br class="clear" />
						<hr />';
						if ((!empty($menu_editor['items']) || !empty($temp['max_item'])) && empty($_REQUEST['parent'])  || !empty($_REQUEST['parent']) && (!empty($menu_editor['children']['items']) && isset($menu_editor['children']['items'][$_REQUEST['parent']]) || !empty($menu_editor['grandchildren']['items']) && isset($menu_editor['grandchildren']['items'][$_REQUEST['parent']])))
						{
							echo '<!-- ORDER -->
							<div class="floatleft" style="width: 30%;">
								<label for="placement">
									<span class="bold">', $txt['placement'], ':</span>
								</label>
								<div class="smalltext">', $txt['pacement_desc'], '</div>
							</div>
							<div class="floatright" style="text-align: left; width: 70%;">
								<select name="placement" id="placement" class="big_select">';
								if (isset($id_shortcut['name']))
								{
									echo '<option value="01001001010011000100110001001101"', !isset($_POST['placement']) ? ' selected="selected"' : '', ' style="font-style: italic;">(', $txt['no_change'], ')&nbsp;</option>';
								}
								echo '
									<optgroup label="', $txt['operations'], ':">';
									if ($modify === true && $id_shortcut['level'] == 0 || !isset($_REQUEST['parent']))
									{
										echo '<option value="0"', isset($_POST['placement']) && empty($_POST['placement']) ? ' selected="selected"' : '', '>', $txt['oper_before'], '</option>
											<option value="1"', isset($_POST['placement']) && $_POST['placement'] == 1 || !isset($_POST['placement']) && !isset($id_shortcut) ? ' selected="selected"' : '', '>', $txt['oper_after'], '</option>';
									}
									else
									{
										echo '<option value="0"', isset($_POST['placement']) && empty($_POST['placement']) ? ' selected="selected"' : '', '>', $txt['oper_above'], '</option>
										<option value="1"', isset($_POST['placement']) && !empty($_POST['placement']) || !isset($_POST['placement']) && !isset($id_shortcut) ? ' selected="selected"' : '', '>', $txt['oper_below'], '</option>';
									}
									echo '</optgroup>
								</select>
								<select name="placement_button" id="placement_button" class="big_select">';
									if (isset($id_shortcut['name']))
									{
										echo '<option value="01001001010011000100110001001101"', !isset($_POST['placement_button']) ? ' selected="selected"' : '', ' style="font-style: italic;">(', $txt['no_change'], ')&nbsp;</option>';
									}

									// Shoutout for Ha2!
									echo '<optgroup label="', $txt['menu_editor_button'], $txt['lab360_plural'], ':">';

									// I know this seems redundent, but it's vital!
									if (!empty($menu_editor['items']) && $modify === true && $id_shortcut['level'] == 0 || $modify === false && !isset($_REQUEST['parent']))
									{
										// Now, each menu item...besides the last ;)
										foreach ($menu_editor['items'] as $key => $value)
										{
											echo '<option value="', $value['button_order'], '"', isset($_POST['placement_button']) && $_POST['placement_button'] == $value['button_order'] ? ' selected="selected"' : '', '>', $value['name'], '&nbsp;</option>';
										}
									}
									elseif (!empty($_REQUEST['parent']) && !empty($menu_editor['children']['items'][$_REQUEST['parent']]))
									{
										foreach ($menu_editor['children']['items'][$_REQUEST['parent']] as $key => $value)
										{
											echo '<option value="', $value['button_order'], '"', isset($_POST['placement_button']) && $_POST['placement_button'] == $value['button_order'] ? ' selected="selected"' : '', '>', $value['name'], '&nbsp;</option>';
										}
									}
									elseif (isset($_REQUEST['parent']) && !empty($menu_editor['grandchildren']['items'][$_REQUEST['parent']]))
									{
										foreach ($menu_editor['grandchildren']['items'][$_REQUEST['parent']] as $key => $value)
										{
											echo '<option value="', $value['button_order'], '"', isset($_POST['placement_button']) && $_POST['placement_button'] == $value['button_order'] ? ' selected="selected"' : '', '>', $value['name'], '&nbsp;</option>';
										}
									}

									// The last menu item, which is selected by default.
									if (!empty($temp['max_item']))
										echo '<option value="', $temp['max_item']['button_order'], '"', isset($_POST['placement_button']) && $_POST['placement_button'] == $temp['max_item']['button_order'] || !isset($_POST['placement_button']) && !isset($id_shortcut['button_order']) ? ' selected="selected"': '', '>', $temp['max_item']['name'], '&nbsp;</option>';

									// Shoutout for Lucy!
									echo '</optgroup>
								</select>
							</div>
							<br class="clear" />
							<hr />';
						}
						else
						{
							echo '<input type="hidden" name="button_order" id="button_order" value="1" />';
						}
						echo '<!-- LINK TYPE -->
						<div class="floatleft" style="width: 30%;">
							<label for="link_type">
								<span class="bold', isset($menu_editor['errors']['empty_type']) ? ' red' : '', '">', $txt['link_type'], ':</span>
							</label>
							<div class="smalltext">', $txt['link_type_desc'], '</div>
						</div>
						<div class="floatright" style="text-align: left; width: 70%;">
							<select class="big_select" name="link_type" id="link_type"', isset($menu_editor['errors']['empty_type']) ? ' style="border: 1px solid #ff0000;"' : '', '>
								<option value=""', empty($_POST['link_type']) || !isset($_POST['link_type']) && !isset($id_shortcut['name']) ? ' selected="selected"' : '', '>(Select Link Type)&nbsp;</option>
								<optgroup label="', $txt['link_type'], $txt['lab360_plural'], ':">
									<option value="1"', isset($_POST['link_type']) && $_POST['link_type'] == 1 || isset($id_shortcut) && $id_shortcut['link_type'] == 1 ? ' selected="selected"' : '', '>', $txt['internal_link'], '</option>
									<option value="2"', isset($_POST['link_type']) && $_POST['link_type'] == 2 || isset($id_shortcut) && $id_shortcut['link_type'] == 2 ? ' selected="selected"' : '', '>', $txt['external_link'], '</option>
								</optgroup>
							</select>
						</div>
						<br class="clear" />
						<!-- INTERNAL LINK -->
							<div id="internal_link_group" class="hidden" style="margin-top: 5px;">
								<div class="floatleft" style="width: 30%;">
									<label for="internal_link">
										<span class="bold', isset($menu_editor['errors']['empty_internal_link']) ? ' red' : '', '">', $txt['internal_link'], ':</span>
									</label>
									<div class="smalltext">', $txt['internal_link_desc'], '</div>
								</div>
								<div class="floatright" style="text-align: left; width: 70%;">
									<select class="big_select" name="internal_link" id="internal_link"', isset($menu_editor['errors']['empty_internal_link']) ? ' style="border: 1px solid #ff0000;"' : '', ' >
										<option value=""', !isset($_POST['internal_link']) && !isset($id_shortcut) || empty($_POST['internal_link']) && !isset($id_shortcut) ? ' checked="checked"' : '', '>(', $txt['select_internal_link'], ')&nbsp;</option>
											<!-- GUEST ONLY LINKS -->
											<optgroup label="', $txt['guest_only'], ':">';
												foreach ($menu_editor['internal_links']['guest_only'] as $go => $guest_only)
												{
													echo '<option value="?action=', $go, '"', isset($_POST['internal_link']) && !empty($_POST['internal_link']) && ($_POST['internal_link'] == '?action=' . $go) || isset($id_shortcut) && $id_shortcut['link_type'] == 1 && $id_shortcut['href'] == '?action=' . $go ? ' selected="selected"' : '', '>', $guest_only, '</option>';
												}
											echo '</optgroup>
											<!-- MEMBER ONLY LINKS -->
											<optgroup label="', $txt['member_only'], ':">';
												foreach ($menu_editor['internal_links']['member_only'] as $mo => $member_only)
												{
													echo '<option value="?action=', $mo, '"', isset($_POST['internal_link']) && !empty($_POST['internal_link']) && $_POST['internal_link'] == '?action=' . $mo || isset($id_shortcut) && $id_shortcut['link_type'] == 1 && $id_shortcut['href'] == '?action=' . $mo ? ' selected="selected"' : '', '>', $member_only, '</option>';
												}
											echo '</optgroup>
											<!-- OTHER INTERNAL LINKS -->
											<optgroup label="', $txt['other_links'], '">';
												foreach ($menu_editor['internal_links']['other'] as $key => $other)
												{
													echo '<option value="?action=', $key, '"', isset($_POST['internal_link']) && !empty($_POST['internal_link']) && $_POST['internal_link'] == '?action=' . $key || isset($id_shortcut) && $id_shortcut['link_type'] == 1 && $id_shortcut['href'] == '?action=' . $key ? ' selected="selected"' : '', '>', $other, '</option>';
												}
												echo '<option value="?action=.xml;type=rss"', isset($_POST['internal_link']) && !empty($_POST['internal_link']) && $_POST['internal_link'] == '?action=.xml;type=rss' || isset($id_shortcut) && $id_shortcut['link_type'] == 1 && $id_shortcut['href'] == '?action=.xml;type=rss' ? ' selected="selected"' : '', '>', $txt['link_rss'], '</option>
												<option value="01001001010011000100110001001101"', isset($_POST['internal_link']) && $_POST['internal_link'] == '01001001010011000100110001001101' || isset($id_shortcut['name']) && $id_shortcut['link_type'] == 1 && $id_shortcut['href'] == '01001001010011000100110001001101' ? ' selected="selected"' : '', '>', $txt['link_scripturl'], '</option>
											</optgroup>
									</select>
								</div>
								<br class="clear" />
							</div>
							<!-- EXTERNAL LINK -->
							<div id="external_link_group" class="hidden" style="margin-top: 5px;">
								<div class="floatleft" style="width: 30%;">
									<label for="external_link">
										<span class="bold', isset($menu_editor['errors']['empty_external_link']) ? ' red' : '', '">', $txt['external_link'], ':</span>
									</label>
									<div class="smalltext">', $txt['external_link_desc'], '</div>
								</div>
								<div class="floatright" style="text-align: left; width: 70%;">';
									if (isset($id_shortcut) && $id_shortcut['link_type'] == 2)
									{
										echo '<input type="text" class="input_text big_input grey" name="external_link" id="external_link" maxlength="255" size="60" value="', $id_shortcut['href'], '"', isset($menu_editor['errors']['empty_external_link']) ? ' style="border: 1px solid #ff0000;"' : '', ' />';
									}
									else
									{
										echo '<input type="text" class="input_text big_input grey" name="external_link" id="external_link" maxlength="255" size="60" value="', isset($_POST['external_link']) && !empty($_POST['external_link']) ? $_POST['external_link'] : 'http://"', '"', isset($menu_editor['errors']['empty_external_link']) ? ' style="border: 1px solid #ff0000;"' : '', ' />';
									}
								echo '</div>
								<br class="clear" />
							</div>
						<hr />
						<!-- TARGET -->
						<div class="floatleft" style="width: 30%;">
							<label for="target">
								<strong>', $txt['menu_editor_target'], ':</strong>
							</label>
							<div class="smalltext">', $txt['target_desc'], '</div>
						</div>
						<div class="floatright" style="text-align: left; width: 70%;">
							<select id="target" name="target" class="big_select">
								<optgroup label="', $txt['menu_editor_target'], $txt['lab360_plural'], ':">
									<option value="0"', isset($_POST['target']) && empty($_POST['target']) || isset($id_shortcut['target']) && empty($id_shortcut['target']) && !isset($_POST['target']) || !isset($id_shortcut['target']) && !isset($_POST['target']) ? ' selected="selected"' : '', '>', $txt['menu_editor_self'], '</option>
									<option value="1"', isset($_POST['target']) && $_POST['target'] == 1 || isset($id_shortcut['target']) && $id_shortcut['target'] == 1 && !isset($_POST['target']) ? ' selected="selected"' : '', '>', $txt['menu_editor_parent'], '&nbsp;</option>
									<option value="2"', isset($_POST['target']) && $_POST['target'] == 2 || isset($id_shortcut['target']) && $id_shortcut['target'] == 2 && !isset($_POST['target']) ? ' selected="selected"' : '', '>', $txt['menu_editor_blank'], '</option>
									<option value="3"', isset($_POST['target']) && $_POST['target'] == 3 || isset($id_shortcut['target']) && $id_shortcut['target'] == 3 && !isset($_POST['target']) ? ' selected="selected"' : '', '>', $txt['menu_editor_top'], '</option>
								</optgroup>
							</select>
						</div>
						<br class="clear" />
						<hr />
						<!-- HIDDEN -->';
						if ($modify === true)
						{
							echo '<input type="hidden" value="', $id_shortcut['id_button'], '" name="id_button" id="id_button" />
								<input type="hidden" value="', $id_shortcut['level'], '" name="level" id="level" />';
						}
						elseif (!empty($_REQUEST['parent']))
						{
							echo '
								<input type="hidden" value="', in_array($_REQUEST['parent'], array_keys($menu_editor['items'])) ? 1 : 2, '" name="level" id="level" />
								<input type="hidden" value="', $_REQUEST['parent'], '" name="id_parent" id="id_parent" />
							';
						}
						else
						{
							echo '
								<input type="hidden" value="0" name="level" id="level" />
							';
						}
						echo '
						<!-- SUBMIT -->
						<input type="submit" class="button_submit cust_submit" value="', isset($id_shortcut['name']) ? $txt['menu_editor_modify'] . '&nbsp;' . $id_shortcut['name'] : $txt['menu_editor_add'], '&nbsp;', $txt['menu_editor_button'], '" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					</form>
				</div>
			</div>
		</div>
		<span class="lowerframe"><span></span></span>
	';

	// Do we have Children yet? ;)
	if (isset($id_shortcut) && $id_shortcut['level'] != 2)
	{
		echo '
			<div class="cat_bar spacer">
				<h3 class="catbg">', $txt['menu_editor_children'], '&nbsp;', $txt['menu_editor_button'], $txt['lab360_plural'];
				if ($id_shortcut['level'] != 2)
				{
					echo '
						<span class="floatright">
							<a href="', $scripturl, '?action=admin;area=menueditor;sa=add;parent=', $id_shortcut['id_button'], '" target="_self">[', $txt['menu_editor_add'], '&nbsp;', $txt['menu_editor_child'], '&nbsp;', $txt['menu_editor_button'], ']</a>
						</span>
					';
				}
				echo '</h3>
			</div>
		';

		// The child/grandchild list.
		template_show_list('children_items');
	}

}

// IT IS ILLEGAL TO MODIFY OR REMOVE THE FOLLOWING TWO FUNCTIONS
function template_copyright_above()
{
}
function template_copyright_below()
{
	echo '
		<div class="centertext smalltext">
			Menu Editor Lite<br /><a href="http://www.simplemachines.org/community/index.php?action=profile;u=182638" target="_blank">Copyright Matthew Kerle ', date('Y'), '</a>
		</div>
	';
}