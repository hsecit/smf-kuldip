<?php
// SMF Version: 2.0; CustomPages Version 1.0.12

// Important! Before editing these language files please read the text at the top of index.english.php.

global $scripturl;

// Edit page txt
$txt['page_id'] = 'Page ID';
$txt['page_title'] = 'Page Title';
$txt['page_body'] = 'Page Body';
$txt['page_perms'] = 'Page Permissions';
$txt['advanced_options'] = 'Advanced Options...';
$txt['page_count'] = 'Count Page Views';
$txt['display_views'] = 'Display page views';
$txt['display_title'] = 'Display title';
$txt['page_class'] = 'Page Class';
$txt['page_styles'] = 'Page Styles';
$txt['title_class'] = 'Title Class';
$txt['title_styles'] = 'Title Styles';
$txt['body_class'] = 'Body Class';
$txt['body_styles'] = 'Body Styles';

// Perms text
$txt['p_everyone'] = 'Everyone';
$txt['p_member'] = 'Members Only';
$txt['p_guest'] = 'Guest Only';
$txt['p_admin'] = 'Admin Only';
$txt['p_overriding'] = 'Overriding Options';
$txt['p_regular'] = 'Regular Groups';
$txt['p_post_based'] = 'Post Based Groups';

// Table headings
$txt['th_title'] = 'Title';
$txt['th_URL'] = 'URL';
$txt['th_perms'] = 'Permissions';
$txt['th_modify'] = 'Modify';
$txt['th_views'] = 'Views';

// Other textage
$txt['cp_descr'] = 'This is where you create, edit and remove custom pages. It is all fairly straight forward. The only thing to keep in mind is that the page ID needs to be unique. Support and feedback <a href="http://www.simplemachines.org/community/index.php?topic=295943" target="_blank">here</a>.';
$txt['edit_page'] = 'Edit Page (%s)';
$txt['no_pages'] = 'There are no pages to be displayed. Click <a href="' . $scripturl . '?action=admin;area=pages;sa=edit;pid=0">here</a> to create one.';
$txt['modify'] = 'modify';
$txt['no_title'] = 'No title';
$txt['confirm'] = 'Are you sure you want to completely remove this page?';
$txt['edit'] = 'Edit';
$txt['use'] = 'Use';
$txt['bbcode'] = 'BBCode';
$txt['html'] = 'HTML';
$txt['disabled'] = 'disabled';

// Errors
$txt['invalid_page'] = 'The page does not exist or is invalid.';
$txt['errors'] = 'The following error or errors occurred while updating this page';
$txt['error_id'] = 'The ' . $txt['page_id'] . ' must be unique. The current one is in use by another page';
$txt['error_id2'] = 'You need to fill out the ' . $txt['page_id'];
$txt['error_id3'] = 'The ' . $txt['page_id'] . ' can be no more then %d characters long';
$txt['error_id4'] = 'The ' . $txt['page_id'] . ' can only be a letter, number or underscore';
$txt['error_title'] = 'You need to fill out the ' . $txt['page_title'];
$txt['error_perms'] = 'You need to select a permission';
$txt['error_body'] = 'You need to fill out the ' . $txt['page_body'];

?>