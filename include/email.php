<?php

/**
 * Copyright (C) 2008-2011 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

require PUN_ROOT.'modules/utf8/utils/ascii.php';

//
// Validate an email address
//
function is_valid_email($email)
{
	if (strlen($email) > 80)
		return false;

	return preg_match('%^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|("[^"]+"))@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])|(([a-zA-Z\d\-]+\.)+[a-zA-Z]{2,}))$%', $email);
}


//
// Check if $email is banned
//
function is_banned_email($email)
{
	global $pun_bans;

	foreach ($pun_bans as $cur_ban)
	{
		if ($cur_ban['email'] != '' &&
			($email == $cur_ban['email'] ||
			(strpos($cur_ban['email'], '@') === false && stristr($email, '@'.$cur_ban['email']))))
			return true;
	}

	return false;
}


//
// Only encode with base64, if there is at least one unicode character in the string
//
function encode_mail_text($str)
{
	if (utf8_is_ascii($str))
		return $str;

	return '=?UTF-8?B?'.base64_encode($str).'?=';
}


//
// Make a post email safe
//
function bbcode2email($text, $wrap_length = 72)
{
    static $base_url;

    if (!isset($base_url))
        $base_url = get_base_url();

    $text = pun_trim($text, "\t\n ");

    $shortcut_urls = array(
        'topic' => '/viewtopic.php?id=$1',
        'post' => '/viewtopic.php?pid=$1#p$1',
        'forum' => '/viewforum.php?id=$1',
        'user' => '/profile.php?id=$1',
    );

    // Split code blocks and text so BBcode in codeblocks won't be touched
    list($code, $text) = extract_blocks($text, '[code]', '[/code]');

    // Strip all bbcodes, except the quote, url, img, email, code and list items bbcodes
    $text = preg_replace(array(
        '%\[/?(?!(?:quote|url|topic|post|user|forum|img|email|code|list|\*))[a-z]+(?:=[^\]]+)?\]%i',
        '%\n\[/?list(?:=[^\]]+)?\]%i' // A separate regex for the list tags to get rid of some whitespace
    ), '', $text);

    // Match the deepest nested bbcode
    // An adapted example from Mastering Regular Expressions
    $match_quote_regex = '%
        \[(quote|\*|url|img|email|topic|post|user|forum)(?:=([^\]]+))?\]
        (
            (?>[^\[]*)
            (?>
                (?!\[/?\1(?:=[^\]]+)?\])
                \[
                [^\[]*
            )*
        )
        \[/\1\]
    %ix';

    $url_index = 1;
    $url_stack = array();
    while (preg_match($match_quote_regex, $text, $matches))
    {
        // Quotes
        if ($matches[1] == 'quote')
        {
            // Put '>' or '> ' at the start of a line
            $replacement = preg_replace(
                array('%^(?=\>)%m', '%^(?!\>)%m'),
                array('>', '> '),
                $matches[2]." said:\n".$matches[3]);
        }

        // List items
        elseif ($matches[1] == '*')
        {
            $replacement = ' * '.$matches[3];
        }

        // URLs and emails
        elseif (in_array($matches[1], array('url', 'email')))
        {
            if (!empty($matches[2]))
            {
                $replacement = '['.$matches[3].']['.$url_index.']';
                $url_stack[$url_index] = $matches[2];
                $url_index++;
            }
            else
                $replacement = '['.$matches[3].']';
        }

        // Images
        elseif ($matches[1] == 'img')
        {
            if (!empty($matches[2]))
                $replacement = '['.$matches[2].']['.$url_index.']';
            else
                $replacement = '['.basename($matches[3]).']['.$url_index.']';

            $url_stack[$url_index] = $matches[3];
            $url_index++;
        }

        // Topic, post, forum and user URLs
        elseif (in_array($matches[1], array('topic', 'post', 'forum', 'user')))
        {
            $url = isset($shortcut_urls[$matches[1]]) ? $base_url.$shortcut_urls[$matches[1]] : '';

            if (!empty($matches[2]))
            {
                $replacement = '['.$matches[3].']['.$url_index.']';
                $url_stack[$url_index] = str_replace('$1', $matches[2], $url);
                $url_index++;
            }
            else
                $replacement = '['.str_replace('$1', $matches[3], $url).']';
        }

        // Update the main text if there is a replacment
        if (!is_null($replacement))
        {
            $text = str_replace($matches[0], $replacement, $text);
            $replacement = null;
        }
    }

    // Put code blocks and text together
    if (isset($code))
    {
        $parts = explode("\1", $text);
        $text = '';
        foreach ($parts as $i => $part)
        {
            $text .= $part;
            if (isset($code[$i]))
                $text .= trim($code[$i], "\n\r");
        }
    }

    // Put URLs at the bottom
    if ($url_stack)
    {
        $text .= "\n\n";
        foreach ($url_stack as $i => $url)
            $text .= "\n".' ['.$i.']: '.$url;
    }

    // Wrap lines if $wrap_length is higher than -1
    if ($wrap_length > -1)
    {
        // Split all lines and wrap them individually
        $parts = explode("\n", $text);
        foreach ($parts as $k => $part)
        {
            preg_match('%^(>+ )?(.*)%', $part, $matches);
            $parts[$k] = wordwrap($matches[1].$matches[2], $wrap_length -
                strlen($matches[1]), "\n".$matches[1]);
        }

        return implode("\n", $parts);
    }
    else
        return $text;
}
