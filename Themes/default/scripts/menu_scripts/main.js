/**
 * Menu Editor Lite
 *
 * @file ./menu_scripts/main.js
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

(function($)
{

	addButtonJS = function()
	{

		// External Link jQuery
		var link_val = $('#external_link').val();
		if (link_val == 'http://') {
		}
		else {
			$('#external_link').removeClass('grey');
		}
		$('#external_link').focus(function() {
			var current = $(this).val();
			if (current == 'http://') {
				$(this).attr('value', '').removeClass('grey');
			}
		}).blur(function() {
			if ($(this).val() == '') {
				$(this).attr('value', 'http://').addClass('grey');
			}
		});

		// Close Notifications! Woot Woot!
		$('#close_notifis').click(function() {
			$('#notifications').slideUp('slow');
		});

		// This is some basic validation...
		if ($('#link_type').val() == 1) {
			$('#internal_link_group').removeClass('hidden');
		}
		if ($('#link_type').val() == 2) {
			$('#external_link_group').removeClass('hidden');
		}
		$('#link_type').change(function() {
			var link_type_value = $(this).val();
			if (link_type_value == '') {
				$('#internal_link_group').slideUp('slow');
				$('#external_link_group').slideUp('slow');
			}
			if (link_type_value == 1) {
				// Make sure external is gone.
				$('#external_link_group').slideUp('slow');
				$('#internal_link_group').slideDown('slow');
			}
			if (link_type_value == 2) {
				// Make sure internal is gone.
				$('#internal_link_group').slideUp('slow');
				$('#external_link_group').slideDown('slow');
			}
		});

		// Now that we're old enough to have children...
		if ($('#placement').val() == 2) {
			$('.select_children').removeAttr('style');
		}
		else {
			$('.select_children').attr('style', 'display: none');
		}
		$('#placement').change(function() {
			var placement_value = $(this).val();
			if (placement_value == 0) {
				$('.select_children').attr('style', 'display: none');
			}
			if (placement_value == 1) {
				$('.select_children').attr('style', 'display: none;');
			}
			if (placement_value == 2) {
				$('.select_children').removeAttr('style');
			}
		});
	};
})(jQuery);