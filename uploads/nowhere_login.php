<?php
define('REQUIRE_CORE', 'login');

require_once './inc/common.midterm.inc.php';

if($m == 'logout' && !empty($formhash) && $formhash == FORMHASH) {
	GlobalCore::clearcookies();
	$groupid = 7;
	$nw_uid = 0;
	$nw_user = $nw_pw = '';
	$styleid = 1;
	GlobalCore::showmessage('logout_succeed', NWDIR, 'DONE');

} elseif($m == 'login') {

	if($nw_uid) {
		GlobalCore::showmessage('login_succeed', NWDIR, 'DONE');
	}

	//get secure code checking status (pos. -2)
	$seccodecheck = substr(sprintf('%05b', $seccodestatus), -2, 1);

	if($seccodecheck && $seccodedata['loginfailedcount']) {
		$seccodecheck = $db->result($db->query("SELECT count(*) FROM {$tablepre}failedlogins WHERE ip='$onlineip' AND count>='$seccodedata[loginfailedcount]' AND $timestamp-lastupdate<=900"), 0);
	}

	if(!GlobalCore::submitcheck('loginsubmit', 1, $seccodecheck)) {

		$chobits_action = 6;

		$referer = GlobalCore::nwReferer();

		$thetimenow = '(GMT '.($timeoffset > 0 ? '+' : '').$timeoffset.') '.
			gmdate("$dateformat $timeformat", $timestamp + $timeoffset * 3600).

		$_DCOOKIE['cookietime'] = isset($_DCOOKIE['cookietime']) ? $_DCOOKIE['cookietime'] : 2592000;
		$cookietimecheck = array((isset($_DCOOKIE['cookietime']) ? intval($_DCOOKIE['cookietime']) : 2592000) => 'checked');

		include GlobalCore::template('login');
		
	} else {

		$nw_uid = 0;
		$nw_user = $nw_pw = $md5_password = '';
		$member = array();

		$loginperm = GlobalCore::logincheck();
		if(!$loginperm) {
			GlobalCore::showmessage('login_strike');
		}

		if(isset($loginauth)) {
			$password = 'VERIFIED';
			list($email, $md5_password) = GlobalCore::chobits_addslashes(explode("\t", GlobalCore::authcode($loginauth, 'DECODE')), 1);
		} else {
			$md5_password = md5($password);
			$password = preg_replace("/^(.{".round(strlen($password) / 4)."})(.+?)(.{".round(strlen($password) / 6)."})$/s", "\\1***\\3", $password);
		}

        if (preg_match("%^[A-Za-z][A-Za-z0-9]*_?[A-Za-z0-9]*$%i", $email)){
            $where = "m.username = '{$email}'"; 
        }else{
            $where = "m.email = '{$email}'"; 
        } 


		$query = $db->query("SELECT m.uid AS nw_uid, m.username AS nw_user, m.nickname AS nw_nick,m.password AS nw_pw,
					m.adminid, m.groupid, m.lastvisit
					FROM {$tablepre}members m
					WHERE {$where}");

		$member = $db->fetch_array($query);

		if($member['nw_uid'] && $member['nw_pw'] == $md5_password) {
			extract($member);

			$nw_userss = $nw_user;
			$nw_user = addslashes($nw_user);
			$nw_nick = addslashes($nw_nick);
			$styleid = 1;

			$cookietime = intval(isset($_POST['cookietime']) ? $_POST['cookietime'] :
					($_DCOOKIE['cookietime'] ? $_DCOOKIE['cookietime'] : 0));

			GlobalCore::chobits_setcookie('cookietime', $cookietime, 31536000);
			GlobalCore::chobits_setcookie('auth', GlobalCore::authcode("$nw_pw\t$nw_uid", 'ENCODE'), $cookietime);

			$sessionexists = 0;
			
            GlobalCore::showmessage('login_succeed', NWDIR, 'DONE');

		}

		$errorlog = GlobalCore::nwHtmlspecialchars(
			$timestamp."\t".
			($member['nw_user'] ? $member['nw_user'] : stripslashes($username))."\t".
			($password)."\t".
			$onlineip);
		GlobalCore::writelog('illegallog', $errorlog);

		GlobalCore::loginfailed($loginperm);

		GlobalCore::showmessage('login_invalid', NWDIR.'/login', 'HALTED');

	}

} else {
	GlobalCore::showmessage('undefined_action');
}

?>
