<?php
error_reporting(0);
set_magic_quotes_runtime(0);
$mtime = explode(' ', microtime());
$nowhere_starttime = $mtime[1] + $mtime[0];

define('IN_NOWHERE', TRUE);
define('NOWHERE_ROOT', substr(dirname(__FILE__), 0, -3));
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

function __autoload($className){
    if(preg_match('#Core$#', $className) && file_exists($classPath = NOWHERE_ROOT."/core/{$className}.php")){
        require_once $classPath; 
    }elseif(file_exists($classPath = NOWHERE_ROOT."/lib/{$className}.class.php")){
        require_once $classPath; 
    }
}

if(PHP_VERSION < '4.1.0') {
    $_GET = &$HTTP_GET_VARS;
	$_POST = &$HTTP_POST_VARS;
	$_COOKIE = &$HTTP_COOKIE_VARS;
	$_SERVER = &$HTTP_SERVER_VARS;
	$_ENV = &$HTTP_ENV_VARS;
	$_FILES = &$HTTP_POST_FILES;
}

if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS'])) {
	exit('Request tainting attempted.');
}

require_once NOWHERE_ROOT.'./inc/config.inc.php';


foreach(array('_COOKIE', '_POST', '_GET') as $_request) {
	foreach($$_request as $_key => $_value) {
		$_key{0} != '_' && $$_key = GlobalCore::chobits_addslashes($_value);
	}
}

if (!MAGIC_QUOTES_GPC && $_FILES) {
	$_FILES = GlobalCore::chobits_addslashes($_FILES);
}
$dbcharset = $forumfounders = $metakeywords = $extrahead = $seodescription = '';
$plugins = $hooks = $admincp = $jsmenu = $forum = $thread = $language = $actioncode = $modactioncode = $lang = $subject = array();

$_DCOOKIE = $_DSESSION = $_NCACHE = $_DPLUGIN = $_CHOBITS = array();

$prelength = strlen($cookiepre);
foreach($_COOKIE as $key => $val) {
	if(substr($key, 0, $prelength) == $cookiepre) {
		$_DCOOKIE[(substr($key, $prelength))] = MAGIC_QUOTES_GPC ? $val : GlobalCore::chobits_addslashes($val);
	}
}
unset($prelength, $_request, $_key, $_value, $_request, $protected);

$inajax = !empty($inajax);
$timestamp = time();

require_once NOWHERE_ROOT.'./core/DBCore.php';

$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
$SCRIPT_FILENAME = str_replace('\\\\', '/', (isset($_SERVER['PATH_TRANSLATED']) ? $_SERVER['PATH_TRANSLATED'] : $_SERVER['SCRIPT_FILENAME']));

if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
	$onlineip = getenv('HTTP_CLIENT_IP');
} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
	$onlineip = getenv('HTTP_X_FORWARDED_FOR');
} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
	$onlineip = getenv('REMOTE_ADDR');
} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
	$onlineip = $_SERVER['REMOTE_ADDR'];
}

preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
$onlineip = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
unset($onlineipmatches);

require_once NOWHERE_ROOT.'./inc/Settings.inc.php';
@extract($_CHOBITS['settings']);
@include(NOWHERE_ROOT.'./data/cache/nowhere_settings.php');

@extract($nowhere_settings);
//&& AJAX_CORE != 1
if($gzipcompress && function_exists('ob_gzhandler')) {
	ob_start('ob_gzhandler');
} else {
	$gzipcompress = 0;
	ob_start();
}

$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset);

if (SYSTEM_UPDATE == 1 && !in_array($adminid, array(1,2, 3))) {
	include GlobalCore::template('system_update');
	exit();
}


?>