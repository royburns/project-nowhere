<?php

if(!defined('IN_NOWHERE')) {
    exit('Access Denied');
}

class NowhereCore{
    public static function UpdateStatus() {
        global $db, $nw_uid, $tablepre, $timestamp, $adminid;
        if ($nw_uid) {
            if (isset($_POST['status_input'])) {
                $say_input = GlobalCore::chobits_addslashes(GlobalCore::cutstr(GlobalCore::nwHtmlspecialchars(trim($_POST['status_input'])),280));
                if ($say_input!='') {
                    $db->query("INSERT INTO {$tablepre}status (stt_uid, stt_status, stt_via,stt_dateline) VALUES ('$nw_uid','$say_input', '0','$timestamp')");
                    $status_id = $db->insert_id();
                    $json['status_id'] = $status_id;
                    $json['status_content'] = self::addLink(stripslashes($say_input));
                }
                self::FetchUserStatus($nw_uid,15,16,1,1);
                GlobalCore::AjaxReferer(NWDIR,1,$json);
            } else {
                    GlobalCore::nwHeader('Location: '.NWDIR);
            }
        } else {
            GlobalCore::nwHeader('Location: '.NWDIR);
        }
    }
    
    public static function EraseStatus() {
        global $db, $nw_uid, $tablepre, $timestamp, $adminid;
        if ($nw_uid) {
            if (isset($_GET['status_id'])) {
                $status_id = intval(trim($_GET['status_id']));
                $db->query("DELETE FROM {$tablepre}status WHERE stt_id='{$status_id}' LIMIT 1", 'UNBUFFERED');
                GlobalCore::AjaxReferer(NWDIR);
            } else {
                    GlobalCore::nwHeader('Location: '.NWDIR);
            }
        } else {
            GlobalCore::nwHeader('Location: '.NWDIR);
        }
    }
    
    public static function FetchUserStatus($uid,$limit=16,$perpage=0,$page=1,$refresh_cache=0) {
        global $db, $nw_uid, $tablepre, $timestamp, $adminid;

        @include(NOWHERE_ROOT.'./data/cache/cache_status.php');
	
        if((@!include('./data/cache/cache_status.php')) || $page>1 || $refresh_cache==1) {
            $page = GlobalCore::FilterPageURL($page);
            if($refresh_cache==1) {
                $count_query = $db->query("SELECT COUNT(stt_id) FROM {$tablepre}status WHERE stt_uid='{$uid}'");
                $data['status_count'] = $db->result($count_query, 0);
            } else {
                $data['status_count'] = $status_count;
            }
            $itemmaxpages = 999;
    		$data['cur_page'] = $page;
    		$page = $itemmaxpages && $page > $itemmaxpages ? 1 : $page;
    		$start_limit = ($page - 1) * $perpage;
    		if($start_limit < 0) $start_limit=0;
    		$limit = $perpage;
    		$data['multipage'] = GlobalCore::multi($data['status_count'], $perpage, $page, NWDIR.'/', $itemmaxpages,10,2,'','.html','ajax_page');
    		$query = $db->query("SELECT * FROM {$tablepre}status WHERE stt_uid='{$uid}' ORDER BY stt_dateline DESC LIMIT {$start_limit},{$limit}");
            while($item = $db->fetch_array($query)) {
                $item['stt_status'] = self::addLink($item['stt_status']);
                $data['item_list'][] = $item;
            }
             //Write Cache for First Page
            if($page==1) {
                CacheCore::chobits_writetocache('cache_status', '', CacheCore::chobits_getcachevars($data),'','cache');
            }
        } else {
            $data['status_count']=$status_count;
            $data['cur_page']=$cur_page;
            $data['multipage'] = $multipage;
            $data['item_list'] =$item_list;
        }
        return $data;   
    }
    
    public static function FetchSettings($output='value') {
        global $db, $nw_uid, $tablepre, $timestamp, $adminid;
        $settings = array();
        $query = $db->query("SELECT * FROM {$tablepre}settings");
        while($setting = $db->fetch_array($query)) {
            if($output=='key') {
                $settings[] = $setting['variable'];
            } else {
        	   $settings[$setting['variable']] = $setting['value'];
        	}
        }
        return $settings;
    }
    
    public static function FetchDefineSettings() {
        global $basic_settings,$webservice_settings;
        foreach($basic_settings as $key=>$value) {
            $basic[] = $key;
        }
        
        foreach($webservice_settings as $key=>$value) {
            $ws[] = $key;
        }
        $define_settings = array_merge($basic,$ws);
        return $define_settings;
    }
    
    public static function GenSettings() {
        global $db, $nw_uid, $tablepre, $timestamp, $adminid,$basic_settings,$webservice_settings;
        $cur_settings = self::FetchSettings('key');
        $define_settings = self::FetchDefineSettings();
        $i = 0;
        foreach($define_settings as $s) {
            if(!in_array(trim($s),$cur_settings)) {
                $i++;
                $s = GlobalCore::chobits_addslashes($s);
                $db->query("REPLACE INTO {$tablepre}settings (variable ,value) VALUES ('{$s}', '')");
                
            }
        }
        echo $i.' setting var inserted';
    }

    public static function UpdateSettings() {
        global $db, $nw_uid,$nw_pw, $tablepre, $timestamp, $adminid,$basic_settings,$webservice_settings,$password_old,$password_new,$password_new2;

        $define_settings = self::FetchDefineSettings();
        foreach ($define_settings as $key) {
            $val = GlobalCore::chobits_addslashes(trim($_POST[$key]));
            $db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('$key', '$val')");
        }
        
        if($_POST['nickname']) {
            $nickname = GlobalCore::chobits_addslashes(GlobalCore::cutstr(GlobalCore::nwHtmlspecialchars($_POST['nickname']), 25,''));
            $avatar = GlobalCore::chobits_addslashes(GlobalCore::nwHtmlspecialchars($_POST['avatar']));
            $db->query("UPDATE {$tablepre}members SET nickname='{$nickname}',avatar='$avatar' WHERE uid = '$nw_uid'");
            $db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('avatar', '$avatar')");
        }
        
		if ($_POST['password_new']) {
			if(md5($password_old) != $nw_pw) {
				GlobalCore::showmessage('profile_passwd_wrong', NULL, 'HALTED');
			}
			if($password_new) {
				if($password_new != addslashes($password_new)) {
					GlobalCore::showmessage('profile_passwd_illegal');
				} elseif($password_new != $password_new2) {
					GlobalCore::showmessage('profile_passwd_notmatch');
				}
				$newpasswd = md5($password_new);
				$db->query("UPDATE {$tablepre}members SET password ='".$newpasswd."' WHERE uid = '$nw_uid'");
				GlobalCore::showmessage('password_set_succeed', NWDIR.'/login', 'DONE');
			}
		}
        
        self::UpdateSettingsCache();
        GlobalCore::nwHeader('Location: '.NWDIR.'/settings');
    }
    
    public static function UpdateSettingsCache() {
        global $db, $nw_uid, $tablepre, $timestamp, $adminid,$basic_settings,$webservice_settings;
        $cur_settings = self::FetchSettings();
        foreach($cur_settings as $key => $value) {
            $data['nowhere_settings'][$key] = $value;
        }
        CacheCore::chobits_writetocache('nowhere_settings', '', CacheCore::chobits_getcachevars($data),'','cache');
    }
    
    public static function addLink($str) {
        preg_match('#https?://[\w-./?%&=~@:$*\\\,+\#]+#i', $str, $m);
    	$o = preg_replace('#(https?://[\w-./?%&=~@:$*\\\,+\#]+)#i', '<a href="\1" rel="nofollow external" target="_blank">\1</a>', $str);
    	return $o;
    }

}