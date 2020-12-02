// SMF Version: 2.0; CustomPages Version 1.0.12

/*
 This file is used to handle the html editor and some other functions used throughout custom pages mod.
 
    switchEditor(void) switches between the html and bbcode editor
   
    surround(string text1, string text2) calls surroundText(string text1, string text2, element textarea)
    
    replace(string text) calls replaceText(string text, element textarea)
    
    unchecker(type) unchecks either all options or just the overidding ones
    
    check(el) unchecks everything and checks el if it wasn't already checked
    
    expandPerms(id, string) expands the perms from a certain id to a certain string 
    
*/

// A few global vars
var bbcode_div;
var html_div;
var code_input;
var handler;
var change_link;

window.onload = function (e)
{
    bbcode_div = document.getElementById('bbcode_editor');
    html_div = document.getElementById('html_editor');
    code_input = document.getElementById('code');
    handler = document.getElementById('body');
    change_link = document.getElementById('change');
    if(change_link)
        change_link.href = 'javascript:switchEditor()';
    
    // Change all the links from non-javascript mode
    var links = document.getElementsByName('plink');
    var i, max = links.length;
    for(i = 0; i < max; i++)
        links[i].href = 'javascript:void(0);';
}

// This function switches between each editor
function switchEditor()
{
    if(bbcode_div.style.display == '')
    {
        bbcode_div.style.display = 'none';
        html_div.style.display = '';
        code_input.value = 'html';
        change_link.innerHTML = txt_bbcode;
    }
    else
    {
        html_div.style.display = 'none';
        bbcode_div.style.display = '';
        code_input.value = 'bbcode';
        change_link.innerHTML = txt_html;
    }
}

// New surround text function simply with a redefined handle
function surround(text1, text2)
{
    surroundText(text1, text2, handler);
}

// Same as above for replace text
function replace(text)
{
    replaceText(text, handler);
}

// Uncheck everything depending on what type is. Can be either ALL or OVERS
function unchecker(type)
{
    // Uncheck everything
    if(type == 'ALL')
        var max = perms.length;
    // Unckeck overs
    else if(type == 'OVERS')
        var max = lastKey;
    // Return false
    else{
        throw 'Type sent to the unchecker was invalid. Must be "ALL" or "OVERS". It was "'+type+'"';
        return;
    }

    // Now loop through the perms and uncheck them
    var i;
    for (i = 0; i < max; i++)
        document.getElementById(perms[i]).checked = false;
}

// Uncheck everything except el if not already checked
function check(el)
{
    // Just return if this was already checked
    if(el.checked == false)
        return;
                    
    // Run the unchecker
    unchecker('ALL');
                
    // Check this element
    el.checked = true;    
}

// This function is used to expand the permissions so you can see them all if it was shortened
function expandPerms(id, string)
{
    // Grab the cell
    var cell = document.getElementById('p_' + id);
    
    // Grab the link
    var link = document.getElementById('pl_' + id);
    
    // Store the old string
    var oldString = cell.innerHTML;
    
    // Update the new string
    cell.innerHTML = string;
    
    // Update the link and wrapping
    if(string.length >= oldString.length)
    {
        link.innerHTML = '<img src="' + smf_images_url + '/upshrink.gif" alt="-" />';
        cell.style.whiteSpace = 'normal';
    }
    else
    {
        link.innerHTML = '<img src="' + smf_images_url + '/upshrink2.gif" alt="+" />';
        cell.style.whiteSpace = 'nowrap';
    }
    link.setAttribute('onclick', 'expandPerms(\'' + id + '\', \'' + oldString + '\')');
}

// Expand/shrink the display options
var advanced_rows = new Array();
var advanced_image = false;
var advancedOpen = true;
function advancedOptions()
{
    if (!advanced_rows.length)
    {
        advanced_rows[0] = document.getElementById('advanced_row1');
        advanced_rows[1] = document.getElementById('advanced_row2');
        advanced_rows[2] = document.getElementById('advanced_row3');
        advanced_rows[3] = document.getElementById('advanced_row4');
    }
    
    if (!advanced_image)
        advanced_image = document.getElementById('advanced_image');
    
    for (var i = 0; i < advanced_rows.length; i++)
        advanced_rows[i].style.display = advancedOpen ? 'none' : '';
    
    advanced_image.src = smf_images_url + '/' + (advancedOpen ? 'expand.gif' : 'collapse.gif');
    advanced_image.alt = advancedOpen ? '-' : '+';
    
    advancedOpen = !advancedOpen;
}
