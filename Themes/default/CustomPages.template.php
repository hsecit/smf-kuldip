<?php

// SMF Version: 2.0; CustomPages Version 1.0.12

// View a page, very simple template.
function template_main()
{
	global $context, $txt;
	
	//Show a table with the page title and body.
	echo '
		<table cellpadding="4" cellspacing="0" border="0" class="', $context['page']['page_class'], '"', empty($context['page']['page_styles']) ? '' : ' style="' . $context['page']['page_styles'] . '" ', 'width="100%">';
        
    if ($context['page']['display_title'])
    {
        echo '
			<tr><td class="', $context['page']['title_class'], '"', empty($context['page']['title_styles']) ? '' : ' style="' . $context['page']['title_styles'] . '"', '>', $context['page']['title'];
        if ($context['page']['display_views'] && $context['page']['count_views'])
            echo '<span class="smalltext" style="padding-left: 8px; font-weight: normal">(', sprintf($txt['cp_viewed'], $context['page']['views']), ')</span></td></tr>';
    }
    
    echo '
			<tr><td class="', $context['page']['body_class'], '"', empty($context['page']['body_styles']) ? '' : ' style="' . $context['page']['body_styles'] . '"', '>', $context['page']['body'], '</td></tr>
		</table>';
}

// Function to limit the length of a string
function shorten_perms($string, $length)
{
    global $smcFunc;
    
    $length = (int) $length;
    if($smcFunc['strlen']($string) > $length)
    {
        $string = $smcFunc['substr']($string, 0, $length);
        
        $cutoff = strrpos($string, ' ');
        
        if($cutoff !== false)
            $string = $smcFunc['substr']($string, 0, $cutoff);
            
        $string = $smcFunc['substr']($string, 0, $smcFunc['strlen']($string) - 1) . '...';
    }
    
    return $string;
}

// Table showing all the custom pages
function template_view_pages()
{
	global $context, $txt, $scripturl, $settings;
    
	// Start the table to show all the pages.
	echo '
		<table cellpadding="4" cellspacing="1" border="0" width="100%" class="bordercolor">
            <tr class="titlebg">
				<th nowrap>', $txt['th_title'], '</th>
				<th nowrap>', $txt['th_URL'], '</th>
				<th nowrap colspan="2">', $txt['th_perms'], '</th>
                <th nowrap>', $txt['th_views'], '</th>
				<th nowrap>', $txt['th_modify'], '</th>
			</tr>';
			
    // Any perms to expand for those pesky non-javascript users
    $ids = array();
    if(!empty($_GET['expand']))
        $ids = explode(',', str_replace(' ', '', $_GET['expand']));
    
    // Remove any empty values
    foreach($ids as $k => $id)
        if(empty($id))
            unset($ids[$k]);
    
	// Are there any pages?
	if(count($context['pages']) > 0)
	{        
		// Loop through all the pages
		foreach($context['pages'] as $page)
        {
            // Has this rows perms been expanded already?
            $expanded = in_array($page['id'], $ids);
            
            // If this hasn't been expanded
            if(!$expanded)
            {
                // Shorten perms if needed
                $perms = shorten_perms($page['perms'], 40);
            
                // If this has been expanded
                if($perms != $page['perms'])
                    $perms .= '<td align="right" valign="top"><a name="plink" id="pl_' . $page['id'] . '" class="smalltext" href="' . $scripturl . '?action=admin;area=pages;expand=' . (count($ids) ? implode(',', $ids) . ',' : '') . $page['id'] . '" onclick="expandPerms(\'' . $page['id'] . '\', \'' . $page['perms'] . '\');"><img src="' . $settings['images_url'] . '/upshrink2.gif" alt="+" /></a></td>';
            }
            else
                $perms = $page['perms'] . '<td align="right" valign="top"><a name="plink" id="pl_' . $page['id'] . '" class="smalltext" href="' . $scripturl . '?action=admin;area=pages;' . (count($ids) <= 1 ? '' : 'expand=' . implode(',', str_replace($page['id'], '', $ids))) . '" onclick="expandPerms(\'' . $page['id'] . '\', \'' . shorten_perms($page['perms'], 40) . '\');"><img src="' . $settings['images_url'] . '/upshrink.gif" alt="-" /></a></td>';
            
			echo '
			<tr class="windowbg">
				<td valign="top" nowrap><a href="', $scripturl, '?action=page;sa=', $page['id'], '" title="', $page['title'], '">', shorten_subject($page['title'], 20), '</a></td>
				<td valign="top" class="windowbg2" nowrap>', $scripturl, '?action=page;sa=', $page['id'], '</td>
				<td valign="top" width="30%"', $expanded ? '' : ' style="white-space: nowrap;"', ' id="p_' . $page['id'] . '"', $perms == $page['perms'] ? ' colspan="2"' : '', '>', $perms, '</td>
                <td valign="top" class="windowbg2" align="center">', $page['views'], '</td>
				<td valign="top" align="center"><a href="', $scripturl, '?action=admin;area=pages;sa=edit;pid=', $page['id'], '">', $txt['modify'], '</td>
			</tr>';
        }
		
	}
	// No pages, so say so...
	else
		echo '
			<tr>
				<td class="windowbg2" colspan="6" align="center" style="font-weight: bold;">', $txt['no_pages'], '</td>
			</tr>';
			
	//End the table with another row to make it look nice and possibly a paginator oneday... maybe :p
	echo '
			<tr><td colspan="6" class="titlebg2">&nbsp;</td></tr>
		</table>';
}

// Show a page to add/edit
function template_edit_page()
{
	global $context, $scripturl, $txt, $settings;
    
	// If the user wants to see how their message looks - the preview table is where it's at!
	echo '
		<div id="preview_section"', isset($context['preview']['body']) ? '' : ' style="display: none;"', '>
			<table border="0" width="100%" cellspacing="1" cellpadding="3" class="bordercolor" align="center" style="table-layout: fixed;">
				<tr class="titlebg">
					<td id="preview_subject">', empty($context['preview']['title']) ? '(<i>' . $txt['no_title'] . '</i>)' : $context['preview']['title'], '</td>
				</tr>
				<tr class="windowbg">
					<td class="post" width="100%" id="preview_body">
						', empty($context['preview']['body']) ? str_repeat('<br />', 5) : ($_POST['code'] == 'bbcode' ? parse_bbc(htmlspecialchars($context['preview']['body'])) : $context['preview']['body']), '
					</td>
				</tr>
			</table><br />
		</div>';
	
	// Create the form
    $img = '<img src="' . $settings['images_url'] . '/icons/field_invalid.gif" alt="*" />&nbsp;';
	echo '
		<form id="page" action="', $scripturl, '?action=admin;area=pages;sa=edit;pid=', $_REQUEST['pid'], '" method="post">
			<table cellpadding="4" cellspacing="0" class="tborder" width="100%" align="center">
				<tr class="titlebg"><td colspan="4">', $context['page_title'], '</td></tr>
				<tr class="windowbg">
					<th align="right" valign="top" nowrap width="20%">', $txt['page_id'], ':</th>
					<td colspan="3">
                        <input type="text" size="30" maxlength="255" value="', $context['page']['id'], '" name="page_id" />
                        <span id="error_id" style="color: #FF0000">', isset($context['errors']['id']) ? $img . $context['errors']['id'] : '', '</span>
                    </td>
				</tr>
				<tr class="windowbg">
					<th align="right" valign="top" nowrap>', $txt['page_title'], ':</th>
					<td colspan="3">
                        <input type="text" size="30" maxlength="255" value="', $context['page']['title'], '" name="title" />
                        <span id="error_title" style="color: #FF0000">', isset($context['errors']['title']) ? $img . $context['errors']['title'] : '', '</span>
                    </td>
				</tr>
				<tr class="windowbg">
					<th align="right" valign="top" nowrap>', $txt['page_perms'], ':</th>
					<td colspan="3">
						<div style="max-height: 15em; overflow: auto; width: 300px">
							<table border="0" width="100%" cellspacing="1" cellpadding="3" class="bordercolor">
								<tr><th colspan="2" class="titlebg2">', $txt['p_overriding'], '</th></tr>
								<tr class="windowbg">
									<td align="right"><label for="everyone">', $txt['p_everyone'], ':</label></td>
									<td><input onclick="check(this);" type="checkbox" name="everyone" id="everyone" ', $context['page']['perms'] == 'everyone' ? 'checked="checked"' : '', ' /></td>
								</tr>
								<tr class="windowbg">
									<td align="right" width="200"><label for="admin">', $txt['p_admin'], ':</label></td>
									<td width="100"><input onclick="check(this);" type="checkbox" name="admin" id="admin" ', $context['page']['perms'] == 'admin' ? 'checked="checked"' : '', ' /></td>
								</tr>
								<tr class="windowbg">
									<td align="right"><label for="member">', $txt['p_member'], ':</label></td>
									<td><input onclick="check(this);" type="checkbox" name="member" id="member" ', $context['page']['perms'] == 'member' ? 'checked="checked"' : '', ' /></td>
								</tr>
								<tr class="windowbg">
									<td align="right"><label for="guest">', $txt['p_guest'], ':</label></td>
									<td><input onclick="check(this);" type="checkbox" id="guest" name="guest" ', $context['page']['perms'] == 'guest' ? 'checked="checked"' : '', ' /></td>
								</tr>
								<tr><th colspan="2" class="titlebg2">', $txt['p_regular'], '</th></tr>';
	foreach($context['r.groups'] as $id =>  $name)
		echo '
								<tr class="windowbg">
									<td align="right"><label for="p', $id, '">', $name, ':</label></td>
									<td><input onclick="unchecker(\'OVERS\');" type="checkbox" id="p', $id, '" name="p'.$id, '" ', in_array($id, explode(',', $context['page']['perms'])) ? 'checked="checked"' : '', ' /></td>
								</tr>';							
	echo '
								<tr><th colspan="2" class="titlebg2">', $txt['p_post_based'], '</th></tr>';
							
	foreach($context['p.groups'] as $id =>  $name)
		echo '
								<tr class="windowbg">
									<td align="right"><label for="p', $id, '">', $name, ':</label></td>
									<td><input onclick="unchecker(\'OVERS\');" type="checkbox" id="p', $id, '" name="p'.$id, '" ', in_array($id, explode(',', $context['page']['perms'])) ? 'checked="checked"' : '', ' /></td>
								</tr>';
    // Which editor to show
    if(isset($_GET['bbcode']))
        $showHTML = false;
    elseif(isset($_GET['html']))
        $showHTML = true;
    elseif($context['page']['code'] == 'html')
        $showHTML = true;
    else
        $showHTML = false;
                                
	echo '				
							</table>
						</div>
                        <span id="error_perms" style="color: #FF0000">', isset($context['errors']['perms']) ? $img . $context['errors']['perms'] : '', '</span>
					</td>
				</tr>
				<tr class="windowbg">
					<th align="right" valign="top" nowrap>
                        ', $txt['page_body'], ':
                        <div class="smalltext">
                            [<a id="change" href="', $scripturl, '?action=admin;area=pages;sa=edit;pid=', $_REQUEST['pid'], $showHTML ? ';bbcode' : ';html', '">', $txt['use'], ' ', $showHTML ? $txt['bbcode'] : $txt['html'], '</a>]
                        </div>
                    </th>
					<td colspan="3">
                        <div id="html_editor"', $showHTML ? '' : ' style="display: none', '">';
                    
    // Fonts
    $fonts = array(
        'Courier' => 'courier',
        'Arial' => 'arial',
        'Arial Black' => 'arial black',
        'Impact' => 'impact',
        'Verdana' => 'verdana',
        'Times New Roman' => 'times new roman',
        'Georgia' => 'georgia',
        'Andale Mono' => 'andale mono',
        'Trebuchet MS' => 'trebuchet ms',
        'Comic Sans MS' => 'comic sans ms',
    );
    
    // Sizes
    $sizes = array(
        '8pt' => 1,
        '10pt' => 2,
        '12pt' => 3,
        '14pt' => 4,
        '18pt' => 5,
        '24pt' => 6,
        '36pt' => 7,
    );

    // Colours
    $colors = array(
        'black' => 'black',
        'red' => 'red',
        'yellow' => 'yellow',
        'pink' => 'pink',
        'green' => 'green',
        'orange' => 'orange',
        'purple' => 'purple',
        'blue' => 'blue',
        'beige' => 'beige',
        'brown' => 'brown',
        'teal' => 'Teal',
        'navy' => 'navy',
        'maroon' => 'Maroon',
        'lime_green' => 'limegreen',
        'white' => 'white',
    );

    
    // HTML buttons etc
    // 'name' => array('image', 'javascript'),
    //              OR
    // 'txt_key' => array('select', javascript, array), <-- each of that array will have 'name' => 'value'
    // {n} for new line in surround and {t} for tab
    // If more then one thing uses the same key then use {2}, {3} etc to seperate them
    $html_editor = array(
        'bold' => array('bold.gif', "surround('<b>', '</b>')"),
        'italic' => array('italicize.gif', "surround('<i>', '</u>')"),
        'underline' => array('underline.gif', "surround('<u>', '</u>')"),
        'strike' => array('strike.gif', "surround('<s>', '</s>')"),
        'line',
        'preformatted' => array('pre.gif', "surround('<pre>', '</pre>')"),
        'left_align' => array('left.gif', "surround('<div style=\'text-align: left;\'>', '</div>')"),
        'center' => array('center.gif', "surround('<div align=\'center\'>', '</div>')"),
        'right_align' => array('right.gif', "surround('<div style=\'text-align: right;\'>', '</div>')"),
        'font_face' => array('select', "surround('<span class=\'font-family: ' + this.value + '\'>', '</span>')", $fonts),
        'font_size' => array('select', "surround('<font size=\'' + this.value + '\'>', '</font>')", $sizes),
        'change_color' => array('select', "surround('<span style=\'color: ' + this.value + '\'>', '</span>')", $colors),
        'newline',
        'image' => array('img.gif', "surround('<img src=\'', '\' alt=\'\' />')"),
        'hyperlink' => array('url.gif', "surround('<a href=\'', '\' target=\'_blank\' />')"),
        'insert_email' => array('email.gif', "surround('<a href=\'mailto:', '\' />')"),
        'ftp' => array('ftp.gif', "surround('<a href=\'', '\' target=\'_blank\' />')"),
        'line',
        'glow' => array('glow.gif', "surround('<span style=\'background-color: red;\'>', '</span>')"),
        'shadow' => array('shadow.gif', "surround('<span style=\'text-shadow: -2px 0pt 1px red;\'>', '</span>')"),
        'marquee' => array('move.gif', "surround('<marquee>', '</marquee>')"),
        'line',
        'superscript' => array('sup.gif', "surround('<sup>', '</sup>')"),
        'subscript' => array('sub.gif', "surround('<sub>', '</sub>')"),
        'teletype' => array('tele.gif', "surround('<tt>', '</tt>')"),
        'line',
        'table' => array('table.gif', "surround('<table>{n}<tr>{n}<td>', '</td>{n}</tr>{n}</table>')"),
        'code' => array('code.gif', "surround('<div class=\'codeheader\'>" . $txt['code'] . ":</div>{n}<code>{n}', '{n}</code>')"),
        'quote' => array('quote.gif', "surround('<div class=\'quoteheader\'>" . $txt['quote'] . "</div>{n}<blockquote>{n}', '{n}</blockquote>')"),
        'line',
        'list' => array('list.gif', "surround('<ul class=\'bbc_list\'>{n}{t}<li>', '</li>{n}{t}<li></li>{n}</ul>')"),
        'list' . '{2}' => array('orderlist.gif', "surround('<ul class=\'bbc_list\' style=\'list-style-type: decimal;\'>{n}{t}<li>', '</li>{n}{t}<li></li>{n}</ul>')"),
        'horizontal_rule' => array('hr.gif', "replace('<hr />')"),
        'newline',
        
    );
    
    foreach($html_editor as $key => $value)
    {
        if($value == 'line')
            echo '<img style="margin: 0pt 3px;" alt="|" src="', $settings['images_url'], '/bbc/divider.gif"/>';
            
        elseif($value == 'newline')
            echo '<br />';
            
        else
        {
            if($value[0] == 'select')
            {
                echo '
                            <select onchange="', $value[1],'; this.options[0].selected = true" style="margin-bottom: 1ex; font-size: x-small;">
                                <option value="0">', $txt[preg_replace('/\{[0-9]+\}/', '', $key)], '</option>';
                foreach($value[2] as $k => $v)
                    echo '
                                <option value="', $v, '">', isset($txt[$k]) ? $txt[$k] : $k, '</option>';
                echo '
                            </select>';
            }
            else
                echo '<a onclick="', str_replace('{n}', '\n', str_replace('{t}', '\t', $value[1])), '" href="javascript:void(0);"><img height="22" width="23" align="bottom" style="margin: 1px 2px 1px 1px; background-image: url(', $settings['images_url'], '/bbc/bbc_bg.gif);" onmouseover="this.style.backgroundImage = \'url(', $settings['images_url'], '/bbc/bbc_hoverbg.gif)\'" onmouseout="this.style.backgroundImage = \'url(', $settings['images_url'], '/bbc/bbc_bg.gif)\'" title="', $txt[preg_replace('/\{[0-9]+\}/', '', $key)], '" alt="', $txt[preg_replace('/\{[0-9]+\}/', '', $key)], '" src="', $settings['images_url'], '/bbc/', $value[0], '" /></a>';
        }
    }
    
    echo'
                            <div style="height: 24px;">&nbsp;</div>
                        </div>
                        <div id="bbcode_editor"', $showHTML ? ' style="display: none' : '', '">
                            <div>', template_control_richedit($context['post_box_name'], 'bbc'), '</div>
                            <div>', template_control_richedit($context['post_box_name'], 'smileys'), '</div>
                        </div>
                        <div>
                                ', template_control_richedit($context['post_box_name'], 'message'), '
                                <span id="error_body" style="color: #FF0000">', isset($context['errors']['body']) ? $img . $context['errors']['body'] : '', '</span>
                        </div>
					</td>
				</tr>
                <tr class="windowbg"><th align="right"><a href="javascript:advancedOptions();"><img src="', $settings['images_url'], '/expand.gif" id="advanced_image" alt="+" /></a> <a href="javascript:advancedOptions();">', $txt['advanced_options'], '</a></th><td colspan="3"></td></tr>
                <tr class="windowbg" id="advanced_row1">
                    <th align="right" nowrap="nowrap"><label for="count">', $txt['page_count'], ':</label></th>
                    <td style="font-weight: bold;" nowrap="nowrap">
                        <input style="margin-right: 70px;" type="checkbox" name="count" id="count"', $context['page']['count_views'] ? ' checked="checked"' : '', ' />
                        <label for="display_views">', $txt['display_views'], ':<input style="margin-left: 10px;" type="checkbox" id="display_views" name="display_views"', $context['page']['display_views'] ? ' checked="checked"' : '', ' /></label>
                    </td>
                    <th align="right" nowrap="nowrap"><label for="display_title">', $txt['display_title'], ':</label></th>
                    <td><input type="checkbox" id="display_title" name="display_title"', $context['page']['display_title'] ? ' checked="checked"' : '', ' /></label></td>
                </tr>
                <tr class="windowbg" id="advanced_row2">
                    <th align="right" nowrap>', $txt['page_class'], ':</th>
                    <td width="15%"><input type="text" size="30" name="page_class" value="', $context['page']['page_class'], '" /></td>
                    <th width="15%" align="right" nowrap>', $txt['page_styles'], ':</th>
                    <td><input type="text" size="30" name="page_styles" value="', $context['page']['page_styles'], '" /></td>
                </tr>
                <tr class="windowbg" id="advanced_row3">
                    <th align="right" nowrap>', $txt['title_class'], ':</th>
                    <td width="15%"><input type="text" size="30" name="title_class" value="', $context['page']['title_class'], '" /></td>
                    <th width="15%" align="right" nowrap>', $txt['title_styles'], ':</th>
                    <td><input type="text" size="30" name="title_styles" value="', $context['page']['title_styles'], '" /></td>
                </tr>
                <tr class="windowbg" id="advanced_row4">
                    <th align="right" nowrap>', $txt['body_class'], ':</th>
                    <td><input type="text" size="30" name="body_class" value="', $context['page']['body_class'], '" /></td>
                    <th align="right" nowrap>', $txt['body_styles'], ':</th>
                    <td><input type="text" size="30" name="body_styles" value="', $context['page']['body_styles'], '" /></td>
                </tr>
				<tr class="windowbg">
					<th align="center" colspan="4" style="padding-top: 1.5em;">
                        <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
                        <input type="hidden" id="code" name="code" value="', $showHTML ? 'html' : 'bbcode', '" />
						<input type="submit" name="post" value="', empty($_GET['pid']) ? $txt['post'] : $txt['edit'], '" tabindex="2" />
						<input type="submit" name="preview" value="', $txt['preview'], '" tabindex="3" />
						', empty($_GET['pid']) ? '' : '<input tabindex="3" name="delete" type="submit" value="' . $txt['delete'] . '" onclick="return confirm(\'' . $txt['confirm'] . '\')" />', '
					</td>
				</tr>
			</table>
		</form>
        <script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
            advancedOptions();
        // ]]></script>';
}
?>