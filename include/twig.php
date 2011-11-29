<?php

require PUN_ROOT.'include/Twig/Autoloader.php';
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem(PUN_ROOT.'templates/');
$twig = new Twig_Environment($loader, array(
  'cache'		=> $flux_config['cache']['dir'],
  'auto_reload'	=> true,
  'debug'		=> true,
));

$twig->addFunction('is_array', new Twig_Function_Function('is_array'));
$twig->addFunction('defined', new Twig_Function_Function('defined'));

$twig->addFilter('number', new Twig_Filter_Function('forum_number_format'));
$twig->addFilter('time', new Twig_Filter_Function('format_time'));

