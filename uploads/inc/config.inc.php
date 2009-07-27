<?php

	$dbhost = 'localhost';
	$dbuser = 'root';
	$dbpw = 'root';
	$dbname = 'now';

	$pconnect = 0;
	$tplrefresh = 1;
	$adminemail = '';
	$dbreport = 0;

	$cookiepre = 'nw_';
	
	$cookiedomain = '';

	$cookiepath = '/';
	$charset = 'utf-8';
	$database = 'mysql';	
	$tablepre = 'nw_';
	
	$errorreport = 1;

    define('NWDIR','/now');
    define('ENABLE_MINIFY', true);
    define('DEBUG_DETAIL', false);

	define('SYSTEM_UPDATE', '0');
	define('NW_VERSION', 'v0.1 pre-alpha');
	define('TEMPLATEID', 'nowhere');
	define('TPLDIR','templates');
	define('LANG','zh_CN');
	
	define('NOWHERE_VER','r1');
	
	define('IMGDIR','/img');
	define('CDN_URL','');


	define('AVATAR_DIR','avatar');
	define('AVATAR_URL','/avatar');
    define('AUTHKEY','');
?>
