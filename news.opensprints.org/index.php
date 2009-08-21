<?php
/*
$HeadURL: https://textpattern.googlecode.com/svn/releases/4.0.8/source/index.php $
$LastChangedRevision: 2885 $
*/

	// Make sure we display all errors that occur during initialization
	error_reporting(E_ALL);
	@ini_set("display_errors","1");

	if (@ini_get('register_globals'))
		foreach ( $_REQUEST as $name => $value )
			unset($$name);
	define("txpinterface", "public");
	if (!defined('txpath'))
		define("txpath", dirname(__FILE__).'/textpattern');

	// Use buffering to ensure bogus whitespace in config.php is ignored
	ob_start(NULL, 2048);
	$here = dirname(__FILE__);
	include txpath.'/config.php';
	ob_end_clean();

	include txpath.'/lib/constants.php';
	include txpath.'/lib/txplib_misc.php';
	if (!isset($txpcfg['table_prefix']))
	{
		txp_status_header('503 Service Unavailable');
		exit('config.php is missing or corrupt.  To install Textpattern, visit <a href="./textpattern/setup/">textpattern/setup/</a>');
	}

	include txpath.'/publish.php';
	textpattern();

?>
