<?php // parser_compile bootstrap
define('PUN_ROOT', dirname(__FILE__).'/');
function get_base_url($str) {
	return 'http://localhost/forums/fluxbb_dev';
//	return 'http://localhost';
}
$lang_common = array('wrote' => 'wrote:');
require_once(PUN_ROOT.'include/bbcd_source.php');	// fetch loose $bbcd array.
// Compile $bbcd and save in include/parser_data.inc.php
require_once(PUN_ROOT.'include/bbcd_compile.php');
?>
