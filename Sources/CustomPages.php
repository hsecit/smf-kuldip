<?php

/**********************************************************************************
* CustomPages.php                                                       	      *
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

/*	This page holds all the functions involved with showing a custom page  
	
	void viewPage(void)
		- calls loadPage
		- shows a page or redirects if invalid page id or not aloud to view it
		- prepares the body
		- loads the required template
		
	void logPageView(void)
        - logs that the user has viewed the page for flood protection
        - increases the total page views if the user hasn't viewed the page in the
        last 30 minutes
*/

// Show the page.
function viewPage()
{
	global $context, $sourcedir;
	
	// We need this for the loadPage function.
	require_once($sourcedir . '/Subs-CustomPages.php');
	
	// Load the page data
	$context['page'] = loadPage($_REQUEST['sa']);
	
	// If the page returned false then redirect.
	if($context['page'] === false)
		redirectexit();
		
	// Check if they are aloud to view this page.
	if(!allowedToViewPage($context['page']['perms']))
		redirectexit();
		
	// Parse the bbc?
    if($context['page']['code'] == 'bbcode')
        $context['page']['body'] = parse_bbc($context['page']['body'], 1, $context['page']['id']);
		
    // Log the page view
    if ($context['page']['count_views'])
        logPageView();
        
	// Get the template ready.
	$context['page_title'] = $context['page']['title'];
	loadTemplate('CustomPages');
}

// Log the page view
function logPageView()
{
    global $context, $smcFunc;
    
    // Delete all page view logs older then 30 minutes
    $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}log_cpfloodcontrol
        WHERE log_time < {int:time} AND id_page = {string:id}',
        array(
            'time' => time() - 108000,
            'id' => $context['page']['id']
        )
    );
    
    // Now check to see if there is still a log of this user
    $request = $smcFunc['db_query']('', '
        SELECT ip FROM {db_prefix}log_cpfloodcontrol
        WHERE ip = {string:ip} AND id_page = {string:id}',
        array(
            'ip' => $_SERVER['REMOTE_ADDR'],
            'id' => $context['page']['id']
        )
    );
    
    // If we have a row just return
    if($smcFunc['db_num_rows']($request))
    {
        $smcFunc['db_free_result']($request);
        return;
    }
    
    // If we get here then insert for flood control
    $smcFunc['db_insert']('normal', '{db_prefix}log_cpfloodcontrol',
        //	Columns to insert.
        array('ip' => 'string', 'log_time' => 'int', 'id_page' => 'string'),
        //	Data to put in.
        array($_SERVER['REMOTE_ADDR'], time(), $context['page']['id']),
        //	Teh key
        array()
    );
    
    // Now log the page view
    $smcFunc['db_query']('', '
        UPDATE {db_prefix}pages
        SET page_views = page_views + 1
        WHERE id_page = {string:id}',
        array(
            'id' => $context['page']['id'],
        )
    );
    
    $context['page']['views']++;
}

?>