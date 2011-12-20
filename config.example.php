<?php

$flux_config = array();

$flux_config['db']['type']			= 'mysql';
$flux_config['db']['host']			= 'localhost';
$flux_config['db']['dbname']		= 'fluxbb__2.0';
$flux_config['db']['username']		= 'root';
$flux_config['db']['password']		= '';
$flux_config['db']['prefix']		= 'forum_';
$flux_config['db']['p_connect']		= false;
// TODO: DSN instead of separate values? Change when merging the DB branch

$flux_config['cache']['type']		= 'File';
$flux_config['cache']['dir']		= PUN_ROOT.'cache/';

$flux_config['cookie']['name']		= 'pun_cookie_1';
$flux_config['cookie']['domain']	= '';
$flux_config['cookie']['path']		= '/';
$flux_config['cookie']['secure']	= 0;
$flux_config['cookie']['seed']		= '123456789abc';

$flux_config['base_url'] = 'http://localhost/fluxbb';

define('PUN', 1);
