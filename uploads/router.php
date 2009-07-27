<?php
$_requestUri = ltrim($_SERVER['REQUEST_URI'],'/');
$_requestUri = explode('?', $_requestUri);
if(isset($_requestUri[1])){
    parse_str($_requestUri[1]);
}
$_requestUri = $_requestUri[0];
require_once('./inc/common.inc.php');

$_requestUri = str_replace(ltrim(NWDIR,'/').'/','',$_requestUri);

switch (true) {
	case (preg_match('#^$#',$_requestUri,$_m)):
		$_GET['m'] = 'home';
		require_once('nowhere.php');
		break;

	case (preg_match('#^([0-9]*).html$#',$_requestUri,$_m)):
		$_GET['m'] = 'home';
		$_GET['page'] = $_m[1];
		require_once('nowhere.php');
		break;
		
	case (preg_match('#^erase/status/([0-9]*)$#',$_requestUri,$_m)):
		$_GET['m'] = 'erase_status';
		$_GET['status_id'] = $_m[1];
		require_once('nowhere.php');
		break;

	case (preg_match('#^update$#',$_requestUri,$_m)):
        $_GET['m'] = 'update';
		require_once('nowhere.php');
		break;
		
	case (preg_match('#^feed$#',$_requestUri,$_m)):
        define(NO_SESSION,true);
        $_GET['m'] = 'feed';
		require_once('nowhere.php');
		break;
		
	case (preg_match('#^ojs$#',$_requestUri,$_m)):
        define(NO_SESSION,true);
        $_GET['m'] = 'js';
		require_once('nowhere.php');
		break;

	case (preg_match('#^output$#',$_requestUri,$_m)):
        $_GET['m'] = 'output';
		require_once('nowhere.php');
		break;

	case (preg_match('#^settings$#',$_requestUri,$_m)):
        $_GET['m'] = 'settings';
		require_once('nowhere.php');
		break;

	case (preg_match('#^login$#',$_requestUri,$_m)):
        $_GET['m'] = 'login';
		require_once('nowhere_login.php');
		break;

	case (preg_match('#^logout/([^\/]+)$#',$_requestUri,$_m)):
		$_GET['m'] = 'logout';
		$_GET['formhash'] = $_m[1];
		require_once('nowhere_login.php');
		break;
		
	default:
		$_GET['m'] = '404';
		require_once('nowhere.php');
		break;

}
