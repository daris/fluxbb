<?php

/**
 * Copyright (C) 2008-2011 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

// Send no-cache headers
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache'); // For HTTP/1.0 compatibility

// Send the Content-type header in case the web server is setup to send something else
header('Content-type: text/html; charset=utf-8');

// Define $p if its not set to avoid a PHP notice
$p = isset($p) ? $p : null;

$flux_page['lang_identifier'] = $lang->t('lang_identifier');
$flux_page['content_direction'] = $lang->t('lang_direction');
$flux_page['style'] = $pun_user['style'];
$flux_page['title'] = generate_page_title($page_title, $p);
$flux_page['page'] = str_replace('.php', '', basename($_SERVER['SCRIPT_FILENAME']));

if (defined('PUN_ADMIN_CONSOLE'))
{
	if (file_exists(PUN_ROOT.'style/'.$pun_user['style'].'/base_admin.css'))
		$flux_page['admin_style_file'] = 'style/'.$pun_user['style'].'/base_admin.css';
	else
		$flux_page['admin_style_file'] = 'style/imports/base_admin.css';
}

if (isset($required_fields))
	$flux_page['required_fields'] = $required_fields;

$flux_page['header_title'] = $pun_config['o_board_title'];
$flux_page['header_desc'] = $pun_config['o_board_desc'];

if (isset($focus_element))
	$flux_page['focus_element'] = $focus_element;


// START SUBST - <pun_navlinks>
$links = array();

// Index should always be displayed
$links[] = '<li id="navindex"'.((PUN_ACTIVE_PAGE == 'index') ? ' class="isactive"' : '').'><a href="index.php">'.$lang->t('Index').'</a></li>';

if ($pun_user['g_read_board'] == '1' && $pun_user['g_view_users'] == '1')
	$links[] = '<li id="navuserlist"'.((PUN_ACTIVE_PAGE == 'userlist') ? ' class="isactive"' : '').'><a href="userlist.php">'.$lang->t('User list').'</a></li>';

if ($pun_config['o_rules'] == '1' && (!$pun_user['is_guest'] || $pun_user['g_read_board'] == '1' || $pun_config['o_regs_allow'] == '1'))
	$links[] = '<li id="navrules"'.((PUN_ACTIVE_PAGE == 'rules') ? ' class="isactive"' : '').'><a href="misc.php?action=rules">'.$lang->t('Rules').'</a></li>';

if ($pun_user['g_read_board'] == '1' && $pun_user['g_search'] == '1')
	$links[] = '<li id="navsearch"'.((PUN_ACTIVE_PAGE == 'search') ? ' class="isactive"' : '').'><a href="search.php">'.$lang->t('Search').'</a></li>';

if ($pun_user['is_guest'])
{
	$links[] = '<li id="navregister"'.((PUN_ACTIVE_PAGE == 'register') ? ' class="isactive"' : '').'><a href="register.php">'.$lang->t('Register').'</a></li>';
	$links[] = '<li id="navlogin"'.((PUN_ACTIVE_PAGE == 'login') ? ' class="isactive"' : '').'><a href="login.php">'.$lang->t('Login').'</a></li>';
}
else
{
	$links[] = '<li id="navprofile"'.((PUN_ACTIVE_PAGE == 'profile') ? ' class="isactive"' : '').'><a href="profile.php?id='.$pun_user['id'].'">'.$lang->t('Profile').'</a></li>';

	if ($pun_user['is_admmod'])
		$links[] = '<li id="navadmin"'.((PUN_ACTIVE_PAGE == 'admin') ? ' class="isactive"' : '').'><a href="admin_index.php">'.$lang->t('Administration').'</a></li>';

	$links[] = '<li id="navlogout"><a href="login.php?action=out&amp;id='.$pun_user['id'].'&amp;csrf_token='.pun_hash($pun_user['id'].pun_hash(get_remote_address())).'">'.$lang->t('Logout').'</a></li>';
}

// Are there any additional navlinks we should insert into the array before imploding it?
if ($pun_user['g_read_board'] == '1' && $pun_config['o_additional_navlinks'] != '')
{
	if (preg_match_all('%([0-9]+)\s*=\s*(.*?)\n%s', $pun_config['o_additional_navlinks']."\n", $extra_links))
	{
		// Insert any additional links into the $links array (at the correct index)
		$num_links = count($extra_links[1]);
		for ($i = 0; $i < $num_links; ++$i)
			array_splice($links, $extra_links[1][$i], 0, array('<li id="navextra'.($i + 1).'">'.$extra_links[2][$i].'</li>'));
	}
}

$flux_page['navigation_links'] = $links;


// START SUBST - <pun_status>
$page_statusinfo = $page_topicsearches = array();

if ($pun_user['is_guest'])
	$page_statusinfo = '<p class="conl">'.$lang->t('Not logged in').'</p>';
else
{
	$page_statusinfo[] = '<li><span>'.$lang->t('Logged in as').' <strong>'.pun_htmlspecialchars($pun_user['username']).'</strong></span></li>';
	$page_statusinfo[] = '<li><span>'.$lang->t('Last visit', format_time($pun_user['last_visit'])).'</span></li>';

	if ($pun_user['is_admmod'])
	{
		if ($pun_config['o_report_method'] == '0' || $pun_config['o_report_method'] == '2')
		{
			$num_reports = $cache->get('num_reports');
			if ($num_reports === Cache::NOT_FOUND)
			{
				$query = $db->select(array('num_reports' => 'COUNT(r.id) AS num_reports'), 'reports AS r');
				$query->where = 'r.zapped IS NULL';

				$params = array();

				$result = $query->run($params);
				$num_reports = $result[0]['num_reports'];
				unset ($result, $query, $params);

				$cache->set('num_reports', $num_reports);
			}

			if ($num_reports > 0)
				$page_statusinfo[] = '<li class="reportlink"><span><strong><a href="admin_reports.php">'.$lang->t('New reports').'</a></strong></span></li>';
		}

		if ($pun_config['o_maintenance'] == '1')
			$page_statusinfo[] = '<li class="maintenancelink"><span><strong><a href="admin_options.php#maintenance">'.$lang->t('Maintenance mode enabled').'</a></strong></span></li>';
	}

	if ($pun_user['g_read_board'] == '1' && $pun_user['g_search'] == '1')
	{
		$page_topicsearches[] = '<a href="search.php?action=show_replies" title="'.$lang->t('Show posted topics').'">'.$lang->t('Posted topics').'</a>';
		$page_topicsearches[] = '<a href="search.php?action=show_new" title="'.$lang->t('Show new posts').'">'.$lang->t('New posts header').'</a>';
	}
}

// Quick searches
if ($pun_user['g_read_board'] == '1' && $pun_user['g_search'] == '1')
{
	$page_topicsearches[] = '<a href="search.php?action=show_recent" title="'.$lang->t('Show active topics').'">'.$lang->t('Active topics').'</a>';
	$page_topicsearches[] = '<a href="search.php?action=show_unanswered" title="'.$lang->t('Show unanswered topics').'">'.$lang->t('Unanswered topics').'</a>';
}

$flux_page['status_info'] = $page_statusinfo;
$flux_page['topic_searches'] = $page_topicsearches;

// START SUBST - <pun_main>
ob_start();


define('PUN_HEADER', 1);
