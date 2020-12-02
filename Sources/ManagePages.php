<?php

/**********************************************************************************
* ManagePages.php                                                       	      *
***********************************************************************************
* Custom Pages Mod for Simple Machines Forum                                      *
*=================================================================================*
* Mod Version:					1.0.12                                            *
* SMF Version created in:		SMF 2.0  			          	                  *
* Mod by:						Robbo (robert@eninja.com.au)                      *
* Copyright 2008 by:			eNinja PTY LTD (http://www.eninja.com.au)         *
* Updates and support at http://custom.simplemachines.org/mods/index.php?mod=1347 *
***********************************************************************************
* This SMF mod is free software; you may redistribute it and/or modify it under   *
* as you see fit as long as the above copyright stays intact on all pages it is   *
* currently on.                                                                   *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

/*	This page holds all the functions with managing a page

	void managePages()
		- the main entrance point for the Manage Pages screen.
		- called by ?action=admin;area=pages.
		- requires the admin_forum permission.
		- loads the CustomPages template and ManagePages language file.
		- calls a function based on the given sub-action.
		
	void viewPages()
		- called by ?action=admin;area=pages as it is the default subaction.
		- uses the view_pages sub template of the CustomPages template.
		- is the area used for navigation to modifying, adding or deleting a page.
		- calls loadPages
		
	void editPage()
		- called by ?action=admin;area=pages;id=(int).
		- if $_GET['pid'] is 0 then the page is used to add a new page.
		- if $_GET['pid'] is an id from the database then the page is used to edit that page.
		- calls loadPage if $_GET['pid'] isn't 0.
		- validates everything before calling updatePage to either insert into the database or update and entry.
		- if $_POST['delete'] is set then it will call removePage to delete the page from the database.
		- logs what the admin does to the.
		
	array loadPages()
		- gets all the custom pages from the database.
		
	array loadGroups()
		- loads all the groups from the database for use with permissions.
		
	void updatePage(string $id, string $page_id, string $page_title, string $page_body, string $page_perms)
		- if $id is false then this function inserts otherwise it updates.
		- uses the inputted data to update the database.
		
	void removePage(string $id)
		- removes a page.
*/

// Main function for this file
function managePages()
{
	global $context, $scripturl, $txt, $settings;
	
	// Load the language file
	loadLanguage('ManagePages');
    
	// Setup the admin tabs.
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['admin_pages'],
		'description' => $txt['cp_descr'],
		'tabs' => array(),
	);
	
    $pid = isset($_GET['pid']) ? $_GET['pid'] : 'main_page';
	
    $context['tabs'] = array(
		'edit' => array(
			'label' => $txt['edit_pages'],
			'url' => $scripturl . '?action=admin;area=pages',
			'is_selected' => strcmp($pid, 0),
		),
		'add' => array(
			'label' => $txt['new_page'],
			'url' => $scripturl . '?action=admin;area=pages;sa=edit;pid=0',
			'is_selected' => !strcmp($pid, 0),
			'is_last' => 1,
		),
		
	);
	
    // Load the scripts needed
    $context['html_headers'] .= '<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/customPages.js?b4"></script>';
    
	// Work out which function to call
	if(empty($_REQUEST['sa']) || $_REQUEST['sa'] != 'edit')
		viewPages();
	else
		editPage();
}

// View all the pages
function viewPages()
{
	global $context, $txt;
	
	// Load the template
	loadTemplate('CustomPages');
	
	// Page title and template
	$context['page_title'] = $txt['admin_pages'];
	$context['sub_template'] = 'view_pages';
	
	// Load the pages up for the template
	$context['pages'] = loadPages();
}

//Load all the custom pages
function loadPages()
{
	global $context, $smcFunc, $db_prefix, $txt;
	
	//Query the pages
	$request = $smcFunc['db_query']('', '
		SELECT id_page, page_title, page_perms, page_views, page_settings
		FROM {db_prefix}pages
        ORDER BY page_time DESC',
		array()
	);
	
	//Now loop through all the rows
	while($row = $smcFunc['db_fetch_assoc']($request))
	{
		//Work out the perms value to use
		switch($row['page_perms'])
		{
			case 'everyone':
				$perms = $txt['p_everyone'];
			break;
			case 'member':
				$perms = $txt['p_member'];
			break;
			case 'guest':
				$perms = $txt['p_guest'];
			break;
			case 'admin':
				$perms = $txt['p_admin'];
			break;
			default:
				// Explode the groups.
				$groups = explode(',', $row['page_perms']);
				
				// Now query all the groups used.
				$result = $smcFunc['db_query']('', '
					SELECT group_name 
					FROM {db_prefix}membergroups
					WHERE id_group IN ({array_int:id})
					ORDER BY id_group ASC ' ,
					array(
						'id' => $groups,
					)
				);
                
				// Empty perms
                $perms = '';
                
				// Loop through and make the $perms value.
				while($row2 = $smcFunc['db_fetch_assoc']($result))
					$perms .= $row2['group_name'].', ';
			
				// I am free!
				$smcFunc['db_free_result']($result);
			
				// Remove the last comma.
				$perms = substr($perms,0,strlen($perms)-2);
			break;
		}
		
		// Now lets make the array to be returned.
		$return[] = array(
			'id' => htmlspecialchars($row['id_page']),
			'title' => htmlspecialchars($row['page_title']),
			'perms' => htmlspecialchars(empty($perms) || !isset($perms) ? '<i>none</i>' : $perms),
            'views' => substr($row['page_settings'], 0, 1) ? $row['page_views'] : '<i>' . $txt['disabled'] . '</i>',
		);
	}	
	
	// FREE!
	$smcFunc['db_free_result']($request);
	
	// Now return the data.
	if(isset($return))
        return $return;
}

// Edit a page
function editPage()
{
	global $context, $smcFunc, $db_prefix, $sourcedir, $txt, $settings, $modSettings;
	
	// Need to require Subs-CustomPages for the loadPage function.
	require_once($sourcedir . '/Subs-CustomPages.php');
	
    // How many chars the page id can be
    $max = 30;
    
	// Get the groups to choose from.
	$groups = loadGroups();
	
    // Disable the WYSIWYG editor
    $modSettings['disable_wysiwyg'] = true;
    
	// Work out the perms
	if(isset($_POST['everyone']))
		$perms = 'everyone';
	elseif(isset($_POST['guest']))
		$perms = 'guest';
	elseif(isset($_POST['member']))
		$perms = 'member';
	elseif(isset($_POST['admin']))
		$perms = 'admin';
	elseif(isset($_POST['post']) || isset($_POST['preview']))
	{		
		for($n=0, $t = count($groups['r.groups']) + count($groups['p.groups']); $n <= $t; $n++){
			if(isset($_POST['p'.$n]))
				$perms[] = $n;
		}
        if(isset($perms))
            $perms = implode(',', $perms);
        else
            $perms = 'error';
	}

	// Validate if post or preview was pressed.
	if(isset($_POST['post']) || isset($_POST['preview']))
	{        
		// Make sure this id doesn't already exist.
		if(loadPage($_POST['page_id']) !== false && $_GET['pid'] !== $_POST['page_id'])
			$context['errors']['id'] = $txt['error_id'];
            
        // Check the id isn't empty.
		if(empty($_POST['page_id']))
			$context['errors']['id'] = $txt['error_id2'];
        
        // Make sure the id is not more then $max chars long
        elseif(strlen($_POST['page_id']) > $max)
            $context['errors']['id'] = sprintf($txt['error_id3'], $max);
        
        // Make sure the id is a-z0-9_ and less then $max chars long.
        elseif(!preg_match('/^[0-9A-Za-z_]{1,' . $max . '}$/', $_POST['page_id']))
            $context['errors']['id'] = $txt['error_id4'];
		
		// Check the title.
		if(empty($_POST['title']))
			$context['errors']['title'] = $txt['error_title'];
			
		// Check the perms.
		if($perms == 'error')
			$context['errors']['perms'] = $txt['error_perms'];
			
		// Check the body.
		if(empty($_POST['body']))
			$context['errors']['body'] = $txt['error_body'];
	}
	
	// Let's start with any form submissions.
	if(isset($_POST['post']) && !isset($context['errors']))
	{
        checkSession();
        
		// If new then make $id false.
		$id = empty($_GET['pid']) ? false : $_GET['pid'];
        
        // Get the settings ready
        $settings = (int) isset($_POST['count']) . ':' . (int) isset($_POST['display_title']) . ':' . (int) isset($_POST['display_views']);
        
		// Update in the database.
		updatePage($id, $_POST['page_id'], $_POST['title'], $_POST['body'], $perms, $_POST['code'], $settings,
                   $_POST['page_class'], $_POST['page_styles'], $_POST['title_class'], $_POST['title_styles'], $_POST['body_class'], $_POST['body_styles']);
		
		// Redirect to the main pages section.
		redirectexit('action=admin;area=pages');
	}
	// Delete a page?
	elseif(isset($_POST['delete']) && !isset($context['errors']))
	{
        checkSession();
        
		// Remove the page
		removePage($_GET['pid']);
		
		// Redirect.
		redirectexit('action=admin;area=pages');
	}
	
	// Load the template
	loadTemplate('CustomPages');
	
	// Page title and template
	$context['sub_template'] = 'edit_page';

	// If this is a new page
	if(empty($_GET['pid']))
    {
		$context['page_title'] = $txt['new_page'];
        
        $page= array(
            'id' => '',
            'body' => '',
            'title' => '',
            'perms' => 'everyone',
            'code' => 'bbcode',
            'count_views' => 1,
            'display_title' => 1,
            'display_views' => 0,
            'page_class' => 'tborder',
            'page_styles' => '',
            'title_class' => 'titlebg',
            'title_styles' => '',
            'body_class' => 'windowbg',
            'body_styles' => '',
        );
    }
	//Otehrwise we are editing a page
	else
	{
		// Load the current page.
		$page = loadPage($_GET['pid']);
		
		// If page is false then the id is invalid
		if($page === false)
			fatal_lang_error('invalid_page');
		
		// Page title.
		$context['page_title'] = sprintf($txt['edit_page'], $page['title']);
	}
	
	// Set the context data.
	$context['page'] = array(
		'body' => un_htmlspecialchars(isset($_POST['body']) ? $_POST['body'] : $page['body']),
		'id' => htmlspecialchars(isset($_POST['page_id']) ? $_POST['page_id'] : $page['id']),
		'title' => htmlspecialchars(isset($_POST['title']) ? $_POST['title'] : $page['title']),
		'perms' => isset($perms) ? $perms : $page['perms'],
        'code' => isset($_POST['code']) ? $_POST['code'] : $page['code'],
        'count_views' => isset($_POST['body']) ? isset($_POST['count']) : $page['count_views'],
        'display_title' => isset($_POST['body']) ? isset($_POST['display_title']) : $page['display_title'],
        'display_views' => isset($_POST['body']) ? isset($_POST['display_views']) : $page['display_views'],
        'page_class' => isset($_POST['page_class']) ? $_POST['page_class'] : $page['page_class'],
        'page_styles' => isset($_POST['page_styles']) ? $_POST['page_styles'] : $page['page_styles'],
        'title_class' => isset($_POST['title_class']) ? $_POST['title_class'] : $page['title_class'],
        'title_styles' => isset($_POST['title_styles']) ? $_POST['title_styles'] : $page['title_styles'],
        'body_class' => isset($_POST['body_class']) ? $_POST['body_class'] : $page['body_class'],
        'body_styles' => isset($_POST['body_styles']) ? $_POST['body_styles'] : $page['body_styles'],
	);
    
	// If the preview button was pressed then get data ready for that
	if(isset($_POST['preview']))
		$context['preview'] = $context['page'];

	// Setup the data to go into the editor.
	$context['page']['body'] = str_replace(array('"', '<', '>', '&nbsp;'), array('&quot;', '&lt;', '&gt;', ' '), $smcFunc['htmlspecialchars']($context['page']['body'], ENT_QUOTES));

	// Needed for the editor and message icons.
	require_once($sourcedir . '/Subs-Editor.php');
	
	// Now create the editor.
	$editorOptions = array(
		'id' => 'body',
		'value' => $context['page']['body'],
		// We do XML preview here.
		'preview_type' => 2,
	);
	create_control_richedit($editorOptions);

	// Store the ID.
	$context['post_box_name'] = $editorOptions['id'];

	// Add the groups to the context array.
	$context['r.groups'] = $groups['r.groups'];
	$context['p.groups'] = $groups['p.groups'];
    
    // Some javascript.
    $context['html_headers'] .= '
        <script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[                        
            // Text
            var txt_bbcode = \'' . $txt['use'] . ' ' . $txt['bbcode'] . '\';
            var txt_html = \'' . $txt['use'] . ' ' . $txt['html'] . '\';
            
            // Array of perms.
            var perms = new Array
            
            // Populate the perms with the hardcoded options.
            perms[0] = \'everyone\';
            perms[1] = \'admin\';
            perms[2] = \'member\';
            perms[3] = \'guest\';
            
            // Var for last keys
            var lastKey = 3;
            
            // Now all the other groups.';
    $i = 4;
    foreach($context['r.groups'] as $id => $name)
    {
        $context['html_headers'] .= '
            perms['.$i.'] = \'p'.$id.'\';';
        $i++;
    }
    foreach($context['p.groups'] as $id => $name)
    {
        $context['html_headers'] .= '
            perms['.$i.'] = \'p'.$id.'\';';
        $i++;
    }
    
    $context['html_headers'] .= '
        // ]]></script>';
}

// Get the groups.
function loadGroups()
{
	global $smcFunc, $db_prefix, $context;
	
	// Query all the groups groups.
	$request = $smcFunc['db_query']('', '
		SELECT group_name, id_group, min_posts
		FROM {db_prefix}membergroups',
		array()
	);
	
	// Loop through the rows and add to the array.
	while($row = $smcFunc['db_fetch_assoc']($request))
	{
		// If min_posts equals -1 then it is a regular group
		if($row['min_posts'] == -1)
			$pre = 'r';
		// Otherwise it is a post based group
		else
			$pre = 'p'; 
		
		// Add the group to the array
		$return[$pre . '.groups'][$row['id_group']] = $row['group_name'];
	}
		
	// Free the result.
	$smcFunc['db_free_result']($request);
	
	// Return the data.
	return $return;
}

// Update the page in the datbase.
function updatePage($id, $page_id, $page_title, $page_body, $page_perms, $page_format, $page_settings,
                    $page_class, $page_styles, $title_class, $title_styles, $body_class, $body_styles)
{
	global $context, $smcFunc, $db_prefix;
	
    if($page_format == 'bbcode')
        $page_body = htmlspecialchars($page_body);
    
	// If id is false then insert the new row.
	if($id === false)
    {
        $smcFunc['db_insert']('normal', '{db_prefix}pages',
            //	Columns to insert.
            array('id_page' => 'string', 'page_title' => 'string', 'page_body' => 'string', 'page_perms' => 'string', 'page_format' => 'int', 'page_time' => 'int',
                  'page_settings' => 'string', 'page_class' => 'string', 'page_styles' => 'string', 'title_class' => 'string', 'title_styles' => 'string', 'body_class' => 'string', 'body_styles' => 'string'),
            //	Data to put in.
            array($page_id, $page_title, $page_body, $page_perms, $page_format == 'bbcode' ? 0 : 1, time(),
                  $page_settings, $page_class, $page_styles, $title_class, $title_styles, $body_class, $body_styles),
            //	Teh key
            array('id_page')
        );
        
        // Log the new page
        logAction('add_page', array('page' => $page_id), 'admin');
    }
	// Otherwise update the row.
	else
    {
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}pages
			SET id_page = {string:id_page},
				page_title = {string:title},
				page_body = {string:body},
				page_perms = {string:perms},
                page_format = {int:format},
                page_settings = {string:settings},
                page_class = {string:page_class},
                page_styles = {string:page_styles},
                title_class = {string:title_class},
                title_styles = {string:title_styles},
                body_class = {string:body_class},
                body_styles = {string:body_styles}
			WHERE id_page = {string:id}',
			array (
				'id_page' => $page_id,
				'title' => $page_title,
				'body' => $page_body,
				'perms' => $page_perms,
				'id' => $id,
                'format' => $page_format == 'bbcode' ? 0 : 1,
                'settings' => $page_settings,
                'page_class' => $page_class,
                'page_styles' => $page_styles,
                'title_class' => $title_class,
                'title_styles' => $title_styles,
                'body_class' => $body_class,
                'body_styles' => $body_styles,
			)
		);
        
        // Log the page update
        logAction('edit_page', array('page' => $page_id), 'admin');
    }
}

// Delete a row
function removePage($id)
{
	global $smcFunc, $db_prefix;
	
	// Simply remove the page...
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}pages
		WHERE id_page = {string:id}',
		array(
			'id' => $id,
		)
	);
    
    // Log the page being removed
    logAction('remove_page', array('page' => $id), 'admin');
    
}

?> 