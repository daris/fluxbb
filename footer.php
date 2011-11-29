<?php

/**
 * Copyright (C) 2008-2011 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;


//ob_end_clean();
// END SUBST - <pun_main>

$template_file = str_replace('.php', '.tpl', basename($_SERVER['SCRIPT_FILENAME']));
if (file_exists(PUN_ROOT.'templates/'.$template_file))
	$template_main = $twig->render($template_file, $flux_page);

// Fallback when file is not rewritten to the twig template system
else
	$template_main = ob_get_clean();

//echo $template_main;
$flux_page['page_output'] = $template_main;

// If no footer style has been specified, we use the default (only copyright/debug info)
$footer_style = isset($footer_style) ? $footer_style : NULL;

if (isset($footer_style))
	$flux_page['footer_style'] = $footer_style;
if (isset($forum_id))
	$flux_page['forum_id'] = $forum_id;
if (isset($is_admmod))
	$flux_page['is_admmod'] = $is_admmod;
if (isset($p))
	$flux_page['p'] = $p;
if (isset($id))
	$flux_page['id'] = $id;
if (isset($cur_topic))
	$flux_page['cur_topic'] = $cur_topic;

// Display the "Jump to" drop list
if ($pun_config['o_quickjump'] == '1')
{
	$quickjump = $cache->get('quickjump');
	if ($quickjump === Cache::NOT_FOUND)
	{
		$quickjump = array();

		// Generate the quick jump cache for all groups
		$query = $db->select(array('gid' => 'g.g_id'), 'groups AS g');
		$query->where = 'g.g_read_board = 1';

		$params = array();

		$result = $query->run($params);
		unset ($query, $params);

		$query_forums = $db->select(array('cid' => 'c.id AS cid', 'cat_name' => 'c.cat_name', 'fid' => 'f.id AS fid', 'forum_name' => 'f.forum_name', 'redirect_url' => 'f.redirect_url'), 'categories AS c');

		$query_forums->innerJoin('f', 'forums AS f', 'c.id = f.cat_id');

		$query_forums->leftJoin('fp', 'forum_perms AS fp', 'fp.forum_id = f.id AND fp.group_id = :group_id');

		$query_forums->where = 'fp.read_forum IS NULL OR fp.read_forum = 1';
		$query_forums->order = array('cposition' => 'c.disp_position ASC', 'cid' => 'c.id ASC', 'fposition' => 'f.disp_position ASC');

		foreach ($result as $cur_group)
		{
			$params = array(':group_id' => $cur_group['g_id']);

			$quickjump[$cur_group['g_id']] = $query_forums->run($params);
			unset ($params);
		}

		unset ($result, $query_forums);

		$cache->set('quickjump', $quickjump);
	}

	$flux_page['quickjump'] = array();

	if (!empty($quickjump[$pun_user['g_id']]))
	{
		$cur_category = 0;
		foreach ($quickjump[$pun_user['g_id']] as $cur_forum)
		{
			if ($cur_forum['cid'] != $cur_category) // A new category since last iteration?
			{
				$flux_page['quickjump'][] = array('cat_name' => $cur_forum['cat_name'], 'forums' => array());
				$cur_category = $cur_forum['cid'];
			}

			$flux_page['quickjump'][count($flux_page['quickjump']) - 1]['forums'][] = $cur_forum;
		}
	}
}

// Display debug info (if enabled/defined)
if (defined('PUN_DEBUG'))
{
	// Calculate script generation time
	$time_diff = sprintf('%.3f', get_microtime() - $pun_start);
	$queries = $db->getExecutedQueries();
	$flux_page['query_time'] = $time_diff;
	$flux_page['num_queries'] = count($queries);

	if (function_exists('memory_get_usage'))
	{
		$flux_page['memory_usage'] = file_size(memory_get_usage());

		if (function_exists('memory_get_peak_usage'))
			$flux_page['peak_usage'] = file_size(memory_get_peak_usage());
	}
}


// End the transaction
$db->commitTransaction();
/*
// Display executed queries (if enabled)
if (defined('PUN_SHOW_QUERIES'))
	display_saved_queries();

$tpl_temp = trim(ob_get_contents());
$tpl_main = str_replace('<pun_footer>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <pun_footer>
*/

// Close the db connection (and free up any result data)
unset ($db);

// Spit out the page
echo $twig->render('main.tpl', $flux_page);
