<?php

/**
 * Copyright (C) 2008-2011 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

define('PUN_ROOT', dirname(__FILE__).'/');
require PUN_ROOT.'include/common.php';


if ($pun_user['g_read_board'] == '0')
	message($lang->t('No view'));


// Load the index.php language file
$lang->load('index');

// Get list of forums and topics with new posts since last visit
if (!$pun_user['is_guest'])
{
	$query = $db->select(array('fid' => 't.forum_id AS fid', 'tid' => 't.id AS tid', 'last_post' => 't.last_post'), 'topics AS t');

	$query->innerJoin('f', 'forums AS f', 'f.id = t.forum_id');

	$query->leftJoin('fp', 'forum_perms AS fp', 'fp.forum_id = f.id AND fp.group_id = :group_id');

	$query->where = '(fp.read_forum IS NULL OR fp.read_forum = 1) AND t.last_post > :last_visit AND t.moved_to IS NULL';

	$params = array(':group_id' => $pun_user['g_id'], ':last_visit' => $pun_user['last_visit']);

	$result = $query->run($params);

	$new_topics = array();
	foreach ($result as $cur_topic)
		$new_topics[$cur_topic['fid']][$cur_topic['tid']] = $cur_topic['last_post'];

	unset ($query, $params, $result);

	$tracked_topics = get_tracked_topics();
}

if ($pun_config['o_feed_type'] == '1')
	$page_head = array('feed' => '<link rel="alternate" type="application/rss+xml" href="extern.php?action=feed&amp;type=rss" title="'.$lang->t('RSS active topics feed').'" />');
else if ($pun_config['o_feed_type'] == '2')
	$page_head = array('feed' => '<link rel="alternate" type="application/atom+xml" href="extern.php?action=feed&amp;type=atom" title="'.$lang->t('Atom active topics feed').'" />');

$flux_page['actions'] = array();

// Display a "mark all as read" link
if (!$pun_user['is_guest'])
	$flux_page['actions'][] = '<a href="misc.php?action=markread">'.$lang->t('Mark all as read').'</a>';

$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']));
define('PUN_ALLOW_INDEX', 1);
define('PUN_ACTIVE_PAGE', 'index');
require PUN_ROOT.'header.php';

// Print the categories and forums
$query = $db->select(array('cid' => 'c.id AS cid', 'cat_name' => 'c.cat_name', 'fid' => 'f.id AS fid', 'forum_name' => 'f.forum_name', 'forum_desc' => 'f.forum_desc', 'redirect_url' => 'f.redirect_url', 'moderators' => 'f.moderators', 'num_topics' => 'f.num_topics', 'num_posts' => 'f.num_posts', 'last_post' => 'f.last_post', 'last_post_id' => 'f.last_post_id', 'last_poster' => 'f.last_poster'), 'categories AS c');

$query->innerJoin('f', 'forums AS f', 'c.id = f.cat_id');

$query->leftJoin('fp', 'forum_perms AS fp', 'fp.forum_id = f.id AND fp.group_id = :group_id');

$query->where = 'fp.read_forum IS NULL OR fp.read_forum = 1';
$query->order = array('cposition' => 'c.disp_position ASC', 'cid' => 'c.id ASC', 'fposition' => 'f.disp_position ASC');

$params = array(':group_id' => $pun_user['g_id']);

$result = $query->run($params);

$cur_category = 0;

foreach ($result as $cur_forum)
{
	if ($cur_forum['cid'] != $cur_category) // A new category since last iteration?
	{
		$flux_page['categories'][] = array('cat_id' => $cur_forum['cid'], 'cat_name' => $cur_forum['cat_name'], 'forums' => array());

		$cur_category = $cur_forum['cid'];
	}

	// Are there new posts since our last visit?
	if (!$pun_user['is_guest'] && $cur_forum['last_post'] > $pun_user['last_visit'] && (empty($tracked_topics['forums'][$cur_forum['fid']]) || $cur_forum['last_post'] > $tracked_topics['forums'][$cur_forum['fid']]))
	{
		// There are new posts in this forum, but have we read all of them already?
		foreach ($new_topics[$cur_forum['fid']] as $check_topic_id => $check_last_post)
		{
			if ((empty($tracked_topics['topics'][$check_topic_id]) || $tracked_topics['topics'][$check_topic_id] < $check_last_post) && (empty($tracked_topics['forums'][$cur_forum['fid']]) || $tracked_topics['forums'][$cur_forum['fid']] < $check_last_post))
			{
				$cur_forum['new_posts'] = true;

				break;
			}
		}
	}

	$cur_forum['moderators'] = $cur_forum['moderators'] == '' ? array() : unserialize($cur_forum['moderators']);

	$current_cat = count($flux_page['categories']) - 1;
	$flux_page['categories'][$current_cat]['forums'][] = $cur_forum;
}

unset ($query, $params, $result);

// Collect some board statistics
$flux_page['stats'] = fetch_board_stats();

$query = $db->select(array('total_topics' => 'SUM(f.num_topics) AS total_topics', 'total_posts' => 'SUM(f.num_posts) AS total_posts'), 'forums AS f');
$params = array();


$flux_page['stats'] = array_merge($flux_page['stats'], current($query->run($params)));

if ($pun_config['o_users_online'] == '1')
{
	// Fetch users online info and generate strings for output
	$query = $db->select(array('user_id' => 'o.user_id', 'ident' => 'o.ident'), 'online AS o');
	$query->where = 'idle = 0';
	$query->order = array('ident' => 'o.ident ASC');

	$params = array();

	$result = $query->run($params);

	$flux_page['stats']['num_guests_online'] = 0;
	$flux_page['stats']['users_online'] = array();

	foreach ($result as $pun_user_online)
	{
		if ($pun_user_online['user_id'] > 1)
			$flux_page['stats']['users_online'][$pun_user_online['user_id']] = $pun_user_online['ident'];
		else
			++$flux_page['stats']['num_guests_online'];
	}

	unset ($query, $params, $result);

}

$footer_style = 'index';
require PUN_ROOT.'footer.php';
