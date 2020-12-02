<?php

/**********************************************************************************
* Subs-CustomPages.php                                                       	  *
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

/*	This file has any functions that are used to manage and view custom pages
	
	array loadPage(string $id)
		- gets the row from the database for the specified id.
		- returns an array or false if there was no data.
		
	bool allowedToViewPage($perms)
		- checks to see if the current user is allowed to view the page according to $perms.
		- returns true or false.
*/

// Load the data
function loadPage($id)
{
	global $db_prefix, $smcFunc;
	
	// If id is empty then return false.
	if(empty($id))
		return false;
	
	// Query the page.
	$request = $smcFunc['db_query']('', '
		SELECT
            id_page, page_title, page_body, page_perms, page_views, page_format,
            page_settings, page_class, page_styles, title_class, title_styles, body_class, body_styles
		FROM {db_prefix}pages
		WHERE id_page = {string:id}',
		array(
			'id' => $id,
		)
	);
	
	// We get a row?
	if($smcFunc['db_num_rows']($request) == 0)
	{
		// No. Free the result and return false
		$smcFunc['db_free_result']($request);
		return false;
	}
	
	// Assign the row to a var.
	$row = $smcFunc['db_fetch_assoc']($request);
	
	// Freeage!
	$smcFunc['db_free_result']($request);
	
    $settings = explode(':', $row['page_settings']);
    
	// Now make the data easier to use before we return it.
	$return = array(
		'id' => $row['id_page'],
		'title' => $row['page_title'],
		'body' => $row['page_body'],
		'perms' => $row['page_perms'],
        'views' => $row['page_views'],
        'code' => $row['page_format'] ? 'html' : 'bbcode',
        'count_views' => $settings[0],
        'display_title' => $settings[1],
        'display_views' => $settings[2],
        'page_class' => $row['page_class'],
        'page_styles' => $row['page_styles'],
        'title_class' => $row['title_class'],
        'title_styles' => $row['title_styles'],
        'body_class' => $row['body_class'],
        'body_styles' => $row['body_styles'],
	);
	
	// Return!
	return $return;		
}

// See if the user is allowed to view a certain page.
function allowedToViewPage($perms)
{
	global $context, $user_info;
	
	// If perms is set to everyone then return true.
	if($perms == 'everyone' || $context['user']['is_admin'])
		return true;
		
	// If perms is set to members only then see if the user is logged in.
	elseif($perms == 'member' && $context['user']['is_logged'])
		return true;
				
	// If perms is set to guests then only guests can see the page.
	elseif($perms == 'guest' && $context['user']['is_guest'])
		return true;
			
	// If we get this far then the value must be specifying certain groups.
	elseif(!empty($perms))
	{
		// Break up the $perms.
		$perms = explode(',', $perms);
		
		// Loop through each perm.
		foreach($perms as $val)
		{
			// If the user is in this membergroup then return true.
			if(in_array($val, $user_info['groups']))
				return true;
		}
	}
	
	// If we get this far then the user isn't aloud to view the page so return false.
	return false;
}

?>