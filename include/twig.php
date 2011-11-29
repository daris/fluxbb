<?php

require PUN_ROOT.'include/Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem(PUN_ROOT.'templates/');
$twig = new Twig_Environment($loader, array(
  'cache'		=> FORUM_CACHE_DIR,
  'auto_reload'	=> true,
));

$twig->addFunction('is_array', new Twig_Function_Function('is_array'));
$twig->addFunction('defined', new Twig_Function_Function('defined'));

$twig->addFilter('number', new Twig_Filter_Function('forum_number_format'));
$twig->addFilter('time', new Twig_Filter_Function('format_time'));

$twig->addFunction('user_link', new Twig_Function_Function('user_link'));

function user_link($user_id, $username)
{
	global $pun_user;

	if ($pun_user['g_view_users'] == '1')
		return '<a href="profile.php?id='.$user_id.'">'.pun_htmlspecialchars($username).'</a>';

	return pun_htmlspecialchars($username);
}