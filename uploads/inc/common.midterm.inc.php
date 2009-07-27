<?php

foreach(array('_COOKIE', '_POST', '_GET') as $_request) {
	foreach($$_request as $_key => $_value) {
		$_key{0} != '_' && $$_key = GlobalCore::chobits_addslashes($_value);
	}
}

$sid = GlobalCore::chobits_addslashes(($transsidstatus)&& (isset($_GET['sid']) || isset($_POST['sid'])) ?
	(isset($_GET['sid']) ? $_GET['sid'] : $_POST['sid']) :
	(isset($_DCOOKIE['sid']) ? $_DCOOKIE['sid'] : ''));
$authkey= AUTHKEY;
$chobits_auth_key = md5($authkey.$_SERVER['HTTP_USER_AGENT']);
list($nw_pw, $nw_uid) = empty($_DCOOKIE['auth']) ? array('', '', 0) : GlobalCore::chobits_addslashes(explode("\t", GlobalCore::authcode($_DCOOKIE['auth'], 'DECODE')), 1);

$sessionexists = 0;

if(!defined('NO_SESSION')) {

    $membertablefields = 'm.uid AS nw_uid, m.username AS nw_user, m.nickname AS nw_nick,m.password AS nw_pw,m.avatar AS nw_avatar, m.regdate AS nw_regdate,
    	m.adminid, m.groupid, m.email, m.timeoffset, m.timeformat, m.dateformat, m.lastvisit, m.lastactivity';
    if($sid) {
    	if($nw_uid) {
    		$query = $db->query("SELECT s.sid, s.groupid='6' AS ipbanned, $membertablefields
    			FROM {$tablepre}sessions s, {$tablepre}members m
    			WHERE m.uid=s.uid AND s.sid='$sid' AND CONCAT_WS('.',s.ip1,s.ip2,s.ip3,s.ip4)='$onlineip' AND m.uid='$nw_uid'
    			AND m.password='$nw_pw'");
    			
    	} else {
    		$query = $db->query("SELECT sid, uid AS sessionuid, groupid, groupid='6' AS ipbanned
    			FROM {$tablepre}sessions WHERE sid='$sid' AND CONCAT_WS('.',ip1,ip2,ip3,ip4)='$onlineip'");
    	}
    	if($_DSESSION = $db->fetch_array($query)) {
    		$sessionexists = 1;
    		if(!empty($_DSESSION['sessionuid'])) {
    			$_DSESSION = array_merge($_DSESSION, $db->fetch_first("SELECT $membertablefields
    				FROM {$tablepre}members m WHERE uid='$_DSESSION[sessionuid]'"));
    		}
    	} else {
    		if($_DSESSION = $db->fetch_first("SELECT sid, groupid, groupid='6' AS ipbanned
    			FROM {$tablepre}sessions WHERE sid='$sid' AND CONCAT_WS('.',ip1,ip2,ip3,ip4)='$onlineip'")) {
    			GlobalCore::clearcookies();
    			$sessionexists = 1;
    		}
    	}
    }
    
    if(!$sessionexists) {
    	if($nw_uid) {
    		if(!($_DSESSION = $db->fetch_first("SELECT $membertablefields
    			FROM {$tablepre}members m WHERE m.uid='$nw_uid' AND m.password='$nw_pw'"))) {
    			GlobalCore::clearcookies();
    		}
    	}
    
    	if(GlobalCore::ipbanned($onlineip)) $_DSESSION['ipbanned'] = 1;
    
    	$_DSESSION['sid'] = GlobalCore::random(6);
    	$_DSESSION['seccode'] = GlobalCore::random(6, 1);
    }

    $_DSESSION['dateformat'] = empty($_DSESSION['dateformat']) ? $_CHOBITS['settings']['dateformat'] : $_DSESSION['dateformat'];
    $_DSESSION['timeformat'] = empty($_DSESSION['timeformat']) ? $_CHOBITS['settings']['timeformat'] : ($_DSESSION['timeformat'] == 1 ? 'h:i A' : 'H:i');
    $_DSESSION['timeoffset'] = isset($_DSESSION['timeoffset']) && $_DSESSION['timeoffset'] != 9999 ? $_DSESSION['timeoffset'] : $_CHOBITS['settings']['timeoffset'];
    
    $membertablefields = '';
    @extract($_DSESSION);
    
    $lastvisit = empty($lastvisit) ? $timestamp - 86400 : $lastvisit;
    $timenow = array('time' => gmdate("$dateformat $timeformat", $timestamp + 3600 * $timeoffset),
    	'offset' => ($timeoffset >= 0 ? ($timeoffset == 0 ? '' : '+'.$timeoffset) : $timeoffset));
    
    if(PHP_VERSION > '5.1') {
    	@date_default_timezone_set('Etc/GMT'.($timeoffset > 0 ? '-' : '+').(abs($timeoffset)));
    }
    
    if(empty($nw_uid) || empty($nw_user)) {
    	$show_cloud = 0;
    	$nw_user = $nw_nick = '';
    	$nw_uid = $adminid = 0;
    	$groupid = empty($groupid) || $groupid != 6 ? 7 : 6;
    } else {
    	$nw_userss = $nw_user;
    	$nw_user = addslashes($nw_user);
    	$nw_nick = addslashes($nw_nick);
    }
    
    if($errorreport == 2 || ($errorreport == 1 && $adminid > 0)) {
    	error_reporting(E_ERROR | E_WARNING | E_PARSE);
    }
    
    define('FORMHASH', GlobalCore::formhash());
    
    $rsshead = $navtitle = $navigation = '';
    
    $_DSESSION['groupid'] = $groupid = empty($ipbanned) ? (empty($groupid) ? 7 : intval($groupid)) : 6;
    
    if(empty($_DCOOKIE['sid']) || $sid != $_DCOOKIE['sid']) {
    	GlobalCore::chobits_setcookie('sid', $sid, 604800);
    }

}
