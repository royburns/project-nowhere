<?php
define('REQUIRE_CORE', 'home');
require_once './inc/common.midterm.inc.php';

if (isset($_GET['m'])) {
	$m = strtolower(trim($_GET['m']));
} else {
	$m = 'home';
}

switch ($m) {
	default:
	case 'home':
	   $_feed = '<link rel="alternate" href="'.NWDIR.'/feed" type="application/rss+xml" title="'.$sitename.'" />';
		$status = NowhereCore::FetchUserStatus(1,16,16,$_GET['page']);
		if($nw_uid) {
            $_init['js'] = 'main';
		}
		$url = GlobalCore::SubURL($_SERVER['REQUEST_URI']);
        if ($url['ajax']== 1) {
            include GlobalCore::template('status_list');
		} else {
            include GlobalCore::template('home');
        }
		break;
	case 'update':
		NowhereCore::UpdateStatus();
        break;
    case 'erase_status':
        NowhereCore::EraseStatus();
    case 'settings':
        if($nw_uid) {
            if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
                NowhereCore::UpdateSettings();
    		}
    		$settings = NowhereCore::FetchSettings();
            include GlobalCore::template('settings');
        } else {
            GlobalCore::nwHeader('Location: '.NWDIR);
        }
		break;
    case 'output':
        if($nw_uid) {
            include GlobalCore::template('output');
        } else {
            GlobalCore::nwHeader('Location: '.NWDIR);
        }
		break;
	case 'js':
		$status = NowhereCore::FetchUserStatus(1,16,16,1);
		$url = GlobalCore::SubURL($_SERVER['REQUEST_URI']);
		$limit = isset($url['limit']) ? intval(trim($url['limit'])) : 1;
        GlobalCore::nwHeader('Content-type: text/javascript; charset=utf-8');
		include GlobalCore::template('js');
		break;
	case 'feed':
		$status = NowhereCore::FetchUserStatus(1,16,16,1);
        GlobalCore::nwHeader("Content-type: application/xml");
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		include GlobalCore::template('feed');
		break;
	case 'login':
		include GlobalCore::template('login');
		break;
}

?>
