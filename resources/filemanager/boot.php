<?php
require_once __DIR__.'/include/utils.php';

use ResponsiveFileManager\RFM;

// ALLOW Crossscript for resource load
header("content-type: text/html; charset=UTF-8");
header("Access-Control-Allow-Origin: https://code.jquery.com");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');    // cache for 1 day
header('Access-Control-Allow-Headers: X-Requested-With');

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

// Verifier Session par rapport a l'api laravel
if (session_id() == '') {
    session_start();
}

mb_internal_encoding(FM_mb_internal_encoding);
mb_http_output(FM_mb_http_output);
mb_http_input(FM_mb_http_input);
mb_language(FM_mb_language);
mb_regex_encoding(FM_mb_regex_encoding);
ob_start(FM_ob_start);
date_default_timezone_set(FM_date_default_timezone_set);

$config = Config::get('rfm');

/**
 * Language init
 */
if ( ! isset($_SESSION['RF']['language'])
	|| file_exists('lang/' . basename($_SESSION['RF']['language']) . '.php') === false
	|| ! is_readable('lang/' . basename($_SESSION['RF']['language']) . '.php')
)
{
	$lang = $config['default_language'];

	if (isset($_GET['lang']) && $_GET['lang'] != 'undefined' && $_GET['lang'] != '')
	{
		$lang = RFM::fix_get_params($_GET['lang']);
		$lang = trim($lang);
	}

	if ($lang != $config['default_language'])
	{
		$path_parts = pathinfo($lang);
		$lang = $path_parts['basename'];
		$languages = include __DIR__.'/lang/languages.php';
	}

	// add lang file to session for easy include
	$_SESSION['RF']['language'] = $lang;
}
else
{
	if(file_exists(__DIR__.'/lang/languages.php')){
		$languages = include __DIR__.'/lang/languages.php';
	}else{
		$languages = include __DIR__.'/../lang/languages.php';
	}

	if(array_key_exists($_SESSION['RF']['language'],$languages)){
		$lang = $_SESSION['RF']['language'];
	}else{
		RFM::response('Lang_Not_Found'.AddErrorLocation())->send();
		exit;
	}

}
if(file_exists(__DIR__.'/lang/' . $lang . '.php')){
	$GLOBALS['lang_vars'] = include __DIR__.'/lang/' . $lang . '.php';
}else{
	$GLOBALS['lang_vars'] = include __DIR__.'/../lang/' . $lang . '.php';
}

if ( ! is_array($GLOBALS['lang_vars']))
{
	$GLOBALS['lang_vars'] = array();
}