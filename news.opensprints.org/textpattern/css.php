<?php
/*
$HeadURL: https://textpattern.googlecode.com/svn/releases/4.0.8/source/textpattern/css.php $
$LastChangedRevision: 2772 $
*/

if (@ini_get('register_globals'))
	foreach ( $_REQUEST as $name => $value )
		unset($$name);

header('Content-type: text/css');

ob_start(NULL, 2048);
include './config.php';
ob_end_clean();

$nolog = 1;
define("txpath", dirname(__FILE__));
define("txpinterface", "css");
include txpath.'/publish.php';
$s = gps('s');
$n = gps('n');
output_css($s,$n);
?>
