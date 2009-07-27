<?php

if(!defined('IN_NOWHERE')) {
    exit('Access Denied');
}
class GlobalCore{

    public static function authcode($string, $operation, $key = '') {

        $key = md5($key ? $key : $GLOBALS['chobits_auth_key']);
        $key_length = strlen($key);

        $string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string.$key), 0, 8).$string;
        $string_length = strlen($string);

        $rndkey = $box = array();
        $result = '';

        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($key[$i % $key_length]);
            $box[$i] = $i;
        }

        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if($operation == 'DECODE') {
            if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8)) {
                return substr($result, 8);
            } else {
                return '';
            }
        } else {
            return str_replace('=', '', base64_encode($result));
        }

    }

    public static function debuginfo() {
        if($GLOBALS['debug']) {
            global $db, $nowhere_starttime, $debuginfo;
            $mtime = explode(' ', microtime());
            $debuginfo = array('time' => number_format(($mtime[1] + $mtime[0] - $nowhere_starttime), 6), 'queries' => $db->querynum, 'detail' => $db->sql_detail);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public static function chobits_exit($message = '') {
        echo $message;
        GlobalCore::output();
        exit();
    }

    public static function nwHtmlspecialchars($string) {
        if(is_array($string)) {
            foreach($string as $key => $val) {
                $string[$key] = GlobalCore::nwHtmlspecialchars($val);
            }
        } else {
            $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
                str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
        }
        return $string;
    }

    public static function nwHeader($string, $replace = true, $http_response_code = 0) {
        $string = str_replace(array("\r", "\n"), array('', ''), $string);
        if(empty($http_response_code) || PHP_VERSION < '4.3' ) {
            @header($string, $replace);
        } else {
            @header($string, $replace, $http_response_code);
        }
        if(preg_match('/^\s*location:/is', $string)) {
            exit();
        }
    }

    public static function chobits_addslashes($string, $force = 0) {
        !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
        if(!MAGIC_QUOTES_GPC || $force) {
            if(is_array($string)) {
                foreach($string as $key => $val) {
                    $string[$key] = GlobalCore::chobits_addslashes($val, $force);
                }
            } else {
                $string = addslashes($string);
            }
        }
        return $string;
    }

    public static function datecheck($ymd, $sep='-') {
        if(!empty($ymd)) {
            list($year, $month, $day) = explode($sep, $ymd);
            return checkdate($month, $day, $year);
        } else {
            return FALSE;
        }
    }

    public static function checktplrefresh($maintpl, $subtpl, $timecompare, $templateid, $tpldir) {
        global $tplrefresh;
        if(empty($timecompare) || $tplrefresh == 1 || ($tplrefresh > 1 && !($GLOBALS['timestamp'] % $tplrefresh))) {
            if(empty($timecompare) || @filemtime($subtpl) > $timecompare) {
                require_once NOWHERE_ROOT.'./core/TemplateCore.php';
                TemplateCore::parse_template($maintpl, $templateid, $tpldir);
                return TRUE;
            }
        }
        return 	FALSE;
    }

    public static function cutstr($string, $length, $dot = ' ...') {
        global $charset;

        if(strlen($string) <= $length) {
            return $string;
        }

        $string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);

        $strcut = '';
        if(strtolower($charset) == 'utf-8') {

            $n = $tn = $noc = 0;
            while($n < strlen($string)) {

                $t = ord($string[$n]);
                if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $tn = 1; $n++; $noc++;
                } elseif(194 <= $t && $t <= 223) {
                    $tn = 2; $n += 2; $noc += 2;
                } elseif(224 <= $t && $t < 239) {
                    $tn = 3; $n += 3; $noc += 2;
                } elseif(240 <= $t && $t <= 247) {
                    $tn = 4; $n += 4; $noc += 2;
                } elseif(248 <= $t && $t <= 251) {
                    $tn = 5; $n += 5; $noc += 2;
                } elseif($t == 252 || $t == 253) {
                    $tn = 6; $n += 6; $noc += 2;
                } else {
                    $n++;
                }

                if($noc >= $length) {
                    break;
                }

            }
            if($noc > $length) {
                $n -= $tn;
            }

            $strcut = substr($string, 0, $n);

        } else {
            for($i = 0; $i < $length - strlen($dot) - 1; $i++) {
                $strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
            }
        }

        $strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

        return $strcut.$dot;
    }

    public static function make_single_safe($value) {
        $value = trim($value);
        $value = str_replace(chr(10), '', $value);
        $value = str_replace(chr(13), '', $value);
        return $value;
    }

    public static function ipaccess($ip, $accesslist) {
        return preg_match("/^(".str_replace(array("\r\n", ' '), array('|', ''), preg_quote($accesslist, '/')).")/", $ip);
    }

    public static function ipbanned($onlineip) {
        global $ipaccess, $timestamp, $cachelost;

        if($ipaccess && !GlobalCore::ipaccess($onlineip, $ipaccess)) {
            return TRUE;
        }

        $cachelost .= (@include NOWHERE_ROOT.'./data/cache/cache_ipbanned.php') ? '' : 'ipbanned';
        if(empty($_NCACHE['ipbanned'])) {
            return FALSE;
        } else {
            if($_NCACHE['ipbanned']['expiration'] < $timestamp) {
                @unlink(NOWHERE_ROOT.'./data/cache/cache_ipbanned.php');
            }
            return preg_match("/^(".$_NCACHE['ipbanned']['regexp'].")$/", $onlineip);
        }
    }

    public static function isemail($email) {
        return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
    }

    public static function language($file,$lang = 'zh_CN') {
        $lang = $lang ? $lang : LANG;
        $languagepack = NOWHERE_ROOT.'./lang/'.$lang.'/'.$file.'.lang.php';
        if(file_exists($languagepack)) {
            return $languagepack;
        } else {
            return FALSE;
        }
    }

    public static function template($file, $subdir= '', $templateid = 1, $tpldir = 'templates') {
        global $inajax;
        $file .= $inajax && ($file == 'header' || $file == 'footer') ? '_ajax' : '';
        $tpldir = $tpldir ? $tpldir : TPLDIR;
        $templateid = TEMPLATEID;

        $objfile = NOWHERE_ROOT.'./data/templates/'.$templateid.'_'.$file.'.tpl.php';
        if ($subdir) {
            $tplfile = NOWHERE_ROOT.'./templates/'.$templateid.'/'.$subdir.'/'.$file.'.htm';	
            if($templateid != 1 && !file_exists($tplfile)) {
                $tplfile = NOWHERE_ROOT.'./templates/'.$templateid.'/'.$subdir.'/'.$file.'.htm';	
            }
        } else {
            $tplfile = NOWHERE_ROOT.'./templates/'.$templateid.'/'.$file.'.htm';	
            if($templateid != 1 && !file_exists($tplfile)) {
                $tplfile = NOWHERE_ROOT.'./templates/'.$templateid.'/'.$file.'.htm';
            }
        }
        GlobalCore::checktplrefresh($tplfile, $tplfile, @filemtime($objfile), $templateid, $tpldir);

        return $objfile;
    }

    public static function strexists($haystack, $needle) {
        return !(strpos($haystack, $needle) === FALSE);
    }

    public static function disuploadedfile($file) {
        return function_exists('is_uploaded_file') && (is_uploaded_file($file) || is_uploaded_file(str_replace('\\\\', '\\', $file)));
    }

    public static function nwReferer($default = '') {
        global $referer;
        $indexname = '/';
        $default = empty($default) ? $indexname : '';
        if (isset($_GET['nwReferer']) && !empty($_GET['nwReferer'])) {
            $referer = $_GET['nwReferer'];
        }elseif (isset($_POST['nwReferer']) && !empty($_POST['nwReferer'])){
            $referer = $_POST['nwReferer'];
        }

        if(empty($referer) && isset($GLOBALS['_SERVER']['HTTP_REFERER'])) {
            $referer = $GLOBALS['_SERVER']['HTTP_REFERER'];
            $referer = substr($referer, -1) == '?' ? substr($referer, 0, -1) : $referer;
        } else {
            $referer = GlobalCore::nwHtmlspecialchars($referer);
        }
        //if(!preg_match("/(\.php|[a-z]+(\-\d+)+\.html)/", $referer) || strpos($referer, 'logging.php')) {
        if(strpos($referer, 'login')) {
            $referer = $default;
        }
        return $referer;
    }

    public static function output() {
        global $sid, $transsidstatus, $rewritestatus;

        if(($transsidstatus = empty($GLOBALS['_DCOOKIE']['sid']) && $transsidstatus) || in_array($rewritestatus, array(2, 3))) {
            if($transsidstatus) {
                $searcharray = array
                    (
                        "/\<a(\s*[^\>]+\s*)href\=([\"|\']?)([^\"\'\s]+)/ies",
                        "/(\<form.+?\>)/is"
                    );
                $replacearray = array
                    (
                        "transsid('\\3','<a\\1href=\\2')",
                        "\\1\n<input type=\"hidden\" name=\"sid\" value=\"$sid\">"
                    );
            } else {
                $searcharray = array();
                $replacearray = array();
            }

            $content = preg_replace($searcharray, $replacearray, ob_get_contents());
            ob_end_clean();
            $GLOBALS['gzipcompress'] ? ob_start('ob_gzhandler') : ob_start();

            echo $content;
        }

        if(defined('CACHE_FILE') && CACHE_FILE && !defined('CACHE_FORBIDDEN')) {
            global $cachethreaddir;
            if(diskfreespace(NOWHERE_ROOT.'./'.$cachethreaddir) > 1000000) {
                $fp = fopen(CACHE_FILE, 'w');
                if($fp) {
                    flock($fp, LOCK_EX);
                    fwrite($fp, empty($content) ? ob_get_contents() : $content);
                }
                @fclose($fp);
            }
        }
    }

    public static function implodeids($array) {
        if(!empty($array)) {
            return "'".implode("','", is_array($array) ? $array : array($array))."'";
        } else {
            return '';
        }
    }

    public static function random($length, $numeric = 0) {
        PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand();
        $seed = base_convert(md5(print_r($_SERVER, 1).microtime()), 16, $numeric ? 10 : 35);
        $seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
        $hash = '';
        $max = strlen($seed) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $seed[mt_rand(0, $max)];
        }
        return $hash;
    }

    public static function fileext($filename) {
        return trim(substr(strrchr($filename, '.'), 1, 10));
    }

    public static function formhash($specialadd = '') {
        global $nw_user, $nw_uid, $nw_pw, $timestamp, $chobits_auth_key;
        $hashadd = defined('IN_ADMINCP') ? 'Only For Admin Control Panel' : '';
        return substr(md5(substr($timestamp, 0, -7).$nw_user.$nw_uid.$nw_pw.$chobits_auth_key.$hashadd.$specialadd), 8, 8);
    }

    public static function updatesession() {
        if(!empty($GLOBALS['sessionupdated'])) {
            return TRUE;
        }

        global $db, $tablepre, $sessionexists, $sessionupdated, $sid, $onlineip, $nw_uid, $nw_user, $timestamp, $lastactivity,
         $oltimespan, $onlinehold, $groupid, $chobits_action;

        $fid = intval($fid);
        $tid = intval($tid);

        if($sessionexists == 1) {
            $db->query("UPDATE {$tablepre}sessions SET uid='$nw_uid', username='$nw_user', groupid='$groupid', action='$chobits_action', lastactivity='$timestamp' WHERE sid='$sid'");
        } else {
            $ips = explode('.', $onlineip);
		
            $db->query("DELETE FROM {$tablepre}sessions WHERE sid='$sid' OR lastactivity<($timestamp-$onlinehold) OR ('$nw_uid'<>'0' AND uid='$nw_uid') OR (uid='0' AND ip1='$ips[0]' AND ip2='$ips[1]' AND ip3='$ips[2]' AND ip4='$ips[3]' AND lastactivity>$timestamp-60)");
            $db->query("INSERT INTO {$tablepre}sessions (sid, ip1, ip2, ip3, ip4, uid, username, groupid, action, lastactivity)
                VALUES ('$sid', '$ips[0]', '$ips[1]', '$ips[2]', '$ips[3]', '$nw_uid', '$nw_user', '$groupid', '$chobits_action', '$timestamp')", 'SILENT');
            if($nw_uid && $timestamp - $lastactivity > 21600) {
                $db->query("UPDATE {$tablepre}members SET lastip='$onlineip', lastvisit=lastactivity, lastactivity='$timestamp' WHERE uid='$nw_uid'", 'UNBUFFERED');
            }
        }

        $sessionupdated = 1;
    }

    public static function logincheck() {
        global $db, $tablepre, $onlineip, $timestamp;
        $query = $db->query("SELECT count, lastupdate FROM {$tablepre}failedlogins WHERE ip='$onlineip'");
        if($login = $db->fetch_array($query)) {
            if($timestamp - $login['lastupdate'] > 900) {
                return 3;
            } elseif($login['count'] < 5) {
                return 2;
            } else {
                return 0;
            }
        } else {
            return 1;
        }
    }

    public static function submitcheck($var, $allowget = 0) {
        if(empty($GLOBALS[$var])) {
            return FALSE;
        } else {
            global $_SERVER, $seclevel, $seccode, $seccodedata, $seccodeverify, $secanswer, $_NCACHE, $_DCOOKIE, $timestamp, $discuz_uid;
            if($allowget || ($_SERVER['REQUEST_METHOD'] == 'POST' && $GLOBALS['formhash'] == GlobalCore::formhash() && empty($_SERVER['HTTP_X_FLASH_VERSION']) && (empty($_SERVER['HTTP_REFERER']) ||
                preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])))) {
                    return TRUE;
                } else {
                    GlobalCore::showmessage('submit_invalid');
                }
        }
    }

    public static function loginfailed($permission) {
        global $db, $tablepre, $onlineip, $timestamp;
        switch($permission) {
        case 1:	$db->query("REPLACE INTO {$tablepre}failedlogins (ip, count, lastupdate) VALUES ('$onlineip', '1', '$timestamp')");
        break;
    case 2: $db->query("UPDATE {$tablepre}failedlogins SET count=count+1, lastupdate='$timestamp' WHERE ip='$onlineip'");
        break;
    case 3: $db->query("UPDATE {$tablepre}failedlogins SET count='1', lastupdate='$timestamp' WHERE ip='$onlineip'");
        $db->query("DELETE FROM {$tablepre}failedlogins WHERE lastupdate<$timestamp-901", 'UNBUFFERED');
        break;
        }
    }

    public static function showmessage($message, $url_forward = '', $extra = '',$others='0') {
        extract($GLOBALS, EXTR_SKIP);
        global $extrahead, $chobits_action, $debuginfo, $fid, $tid, $charset, $show_message, $_NCACHE;
        define('CACHE_FORBIDDEN', TRUE);
        $disable_robot = 1;
        $show_message = $message;
        $msgforward = unserialize($_CHOBITS['settings']['msgforward']);
        $msgforward['refreshtime'] = intval($msgforward['refreshtime']);
        $url_forward = empty($url_forward) ? '' : (empty($_DCOOKIE['sid']) && $transsidstatus ? transsid($url_forward) : $url_forward);

      if($url_forward && empty($_GET['inajax']) && $msgforward['quick'] && $msgforward['messages'] && @in_array($message, $msgforward['messages'])) {
            GlobalCore::updatesession();
            GlobalCore::nwHeader("location: ".str_replace('&amp;', '&', $url_forward));
        }

        if(in_array($extra, array('HALTED', 'NOPERM'))) {
            $fid = $tid = 0;
            $chobits_action = 254;
        } else {
            $chobits_action = 255;
        }

        include GlobalCore::language('messages');

        if(isset($language[$message])) {
            eval("\$show_message = \"".$language[$message]."\";");
        }

        $extrahead .= $url_forward ? '<meta http-equiv="refresh" content="'.$msgforward['refreshtime'].' url='.$url_forward.'">' : '';

        if($extra == 'NOPERM') {
            include GlobalCore::template('nopermission');
        } elseif($extra == 'DONE') {
            include GlobalCore::template('showmessage_done');
        } else {
            if ($others == 0 ){
                $this_page = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                include GlobalCore::template('showmessage');
            } else {
                include GlobalCore::template('showmessage_other');
            }
        }
        GlobalCore::chobits_exit();
    }

    public static function chobits_setcookie($var, $value, $life = 0, $prefix = 1) {
        global $cookiepre, $cookiedomain, $cookiepath, $timestamp, $_SERVER;
        setcookie(($prefix ? $cookiepre : '').$var, $value,
            $life ? $timestamp + $life : 0, $cookiepath,
            $cookiedomain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
    }

    public static function clearcookies() {
        global $nw_uid, $nw_user, $nw_pw, $adminid, $credits;
        GlobalCore::chobits_setcookie('sid', '', -86400 * 365);
        GlobalCore::chobits_setcookie('auth', '', -86400 * 365);
        GlobalCore::chobits_setcookie('visitedfid', '', -86400 * 365);
        GlobalCore::chobits_setcookie('onlinedetail', '', -86400 * 365, 0);

        $nw_uid = $adminid = $credits = 0;
        $nw_user = $nw_pw = '';
    }

    public static function writelog($file, $log) {
        global $timestamp, $_CHOBITS;
        $yearmonth = gmdate('Ym', $timestamp + $_CHOBITS['settings']['timeoffset'] * 3600);
        $logdir = NOWHERE_ROOT.'./forumdata/logs/';
        $logfile = $logdir.$yearmonth.'_'.$file.'.php';
        if(@filesize($logfile) > 2048000) {
            $dir = opendir($logdir);
            $length = strlen($file);
            $maxid = $id = 0;
            while($entry = readdir($dir)) {
                if(GlobalCore::strexists($entry, $yearmonth.'_'.$file)) {
                    $id = intval(substr($entry, $length + 8, -4));
                    $id > $maxid && $maxid = $id;
                }
            }
            closedir($dir);

            $logfilebak = $logdir.$yearmonth.'_'.$file.'_'.($maxid + 1).'.php';
            @rename($logfile, $logfilebak);
        }
        if($fp = @fopen($logfile, 'a')) {
            @flock($fp, 2);
            $log = is_array($log) ? $log : array($log);
            foreach($log as $tmp) {
                fwrite($fp, "<?PHP exit;?>\t".str_replace(array('<?', '?>'), '', $tmp)."\n");
            }
            fclose($fp);
        }
    }

    public static function mkdirs($dir){
        $dirs = str_replace(NOWHERE_ROOT, '', $dir);
        $dirs = explode('/', $dirs);

        $dir = NOWHERE_ROOT;
        foreach($dirs as $subDir){
            $dir .= $subDir.'/';
            !is_dir($dir) && mkdir($dir, 0777);
        }
    }

    public static function mkdir_by_uid($uid, $dir = '.') {
        $uid = sprintf("%09d", $uid);
        $dir1 = substr($uid, 0, 3);
        $dir2  = substr($uid, 3, 2);
        $dir3  = substr($uid, 5, 2);
        GlobalCore::mkdirs($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3);
    /*
    !is_dir($dir) && mkdir($dir, 0777);
    !is_dir($dir.'/'.$dir1) && mkdir($dir.'/'.$dir1, 0777);
    !is_dir($dir.'/'.$dir1.'/'.$dir2) && mkdir($dir.'/'.$dir1.'/'.$dir2, 0777);
    !is_dir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3) && mkdir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3, 0777);
     */
        return $dir1.'/'.$dir2.'/'.$dir3;
    }

    public static function mkdir_hash($s, $dir = '.') {
        $s = md5($s);
        $dir .= "/{$s[0]}{$s[1]}/{$s[2]}{$s[3]}";
        GlobalCore::mkdirs($dir);
        //!is_dir($dir.'/'.$s[0].$s[1]) && mkdir($dir.'/'.$s[0].$s[1], 0777);
        //!is_dir($dir.'/'.$s[0].$s[1].'/'.$s[2].$s[3]) && mkdir($dir.'/'.$s[0].$s[1].'/'.$s[2].$s[3], 0777);
        return $s[0].$s[1].'/'.$s[2].$s[3];
    }

    public static function get_avatar($uid, $size = 'm') {
        $o_uid =$uid;
        $size = in_array($size, array('l', 'm')) ? $size : 'm';
        $uid = abs(intval($uid));
        $uid = sprintf("%09d", $uid);
        $dir1 = substr($uid, 0, 3);
        $dir2 = substr($uid, 3, 2);
        $dir3 = substr($uid, 5, 2);
        return $dir1.'/'.$dir2.'/'.$dir3.'/'.$o_uid.".jpg";
    }

    public static function getdir_uid($uid) {
        $o_uid =$uid;
        $uid = abs(intval($uid));
        $uid = sprintf("%09d", $uid);
        $dir1 = substr($uid, 0, 3);
        $dir2 = substr($uid, 3, 2);
        $dir3 = substr($uid, 5, 2);
        return $dir1.'/'.$dir2.'/'.$dir3;
    }

    public static function getdir_hash($s) {
        $s = md5($s);
        return $s[0].$s[1].'/'.$s[2].$s[3];
    }

    public static function make_desc_time($unix_timestamp) {
        global $dateformat,$timeformat,$timeoffset;
        $now = time();
        $diff = $now - $unix_timestamp;

        if ($diff > 86400) {
            $d_span = intval($diff / 86400);
            $h_diff = $diff % 86400;
            if ($d_span < 3) {
                if ($h_diff > 3600) {
                    $h_span = intval($h_diff / 3600);
                    return $d_span . 'd ' . $h_span . 'h ago';
                } else {
                    return $d_span . 'd  ago';
                }
            } else {
                return gmdate("$dateformat $timeformat",$unix_timestamp + $timeoffset * 3600);
            }
        }

        if ($diff > 3600) {
            $h_span = intval($diff / 3600);
            $m_diff = $diff % 3600;
            if ($m_diff > 60) {
                $m_span = intval($m_diff / 60);
                return $h_span . 'h ' . $m_span . 'm  ago';
            } else {
                return $h_span . 'h  ago';
            }
        }

        if ($diff > 60) {
            $span = intval($diff / 60);
            return $span . 'm ago';
        }

        return $diff . 's  ago';
    }

    public static function make_descriptive_time($unix_timestamp) {
        $now = time();
        $diff = $now - $unix_timestamp;

        if ($diff > (86400 * 30)) {
            $m_span = intval($diff / (86400 * 30));
            $d_diff = $diff % ($m_span * (86400 * 30));
            if ($d_diff > 86400) {
                $d_span = intval($d_diff / 86400);
                return $m_span . '月' . $d_span . '天前';
            } else {
                return $m_span . '月前';
            }
        }

        if ($diff > 86400) {
            $d_span = intval($diff / 86400);
            $h_diff = $diff % 86400;
            if ($h_diff > 3600) {
                $h_span = intval($h_diff / 3600);
                return $d_span . '天' . $h_span . '小时前';
            } else {
                return $d_span . '天前';
            }
        }

        if ($diff > 3600) {
            $h_span = intval($diff / 3600);
            $m_diff = $diff % 3600;
            if ($m_diff > 60) {
                $m_span = intval($m_diff / 60);
                return $h_span . '小时' . $m_span . '分钟前';
            } else {
                return $h_span . '小时前';
            }
        }

        if ($diff > 60) {
            $span = floor($diff / 60);
            $secs = $diff % 60;
            if ($secs > 0) {
                return $span . '分' . $secs . '秒前';
            } else {
                return $span . '分钟前';
            }
        }

        return $diff . '秒前';
    }

    public static function multi($num, $perpage, $curpage, $mpurl, $maxpages = 0, $page = 10, $simple = 0, $onclick = '',$postfix ='',$ajax = '') {
    	$multipage = '';
    	//$mpurl .= strpos($mpurl, '?') ? '&amp;' : '?';
    	$onclick = $onclick ? ' onclick="'.$onclick.'(event)"' : '';
    	$ajax = ' '.$ajax;
    	if($num > $perpage) {
    		$offset = 2;
    
    		$realpages = @ceil($num / $perpage);
    		$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;
    
    		if($page > $pages) {
    			$from = 1;
    			$to = $pages;
    		} else {
    			$from = $curpage - $offset;
    			$to = $from + $page - 1;
    			if($from < 1) {
    				$to = $curpage + 1 - $from;
    				$from = 1;
    				if($to - $from < $page) {
    					$to = $page;
    				}
    			} elseif($to > $pages) {
    				$from = $pages - $page + 1;
    				$to = $pages;
    			}
    		}
            if($simple<=1) {
    		$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="'.$mpurl.'1'.$postfix.'" class="p'.$ajax.'"'.$onclick.'>|&lsaquo;</a>' : '').
    			($curpage > 1 ? '<a href="'.$mpurl.($curpage - 1).$postfix.'" class="p'.$ajax.'">&lsaquo;&lsaquo;上一页</a>' : '');
            }
    		if(!$simple) {
    			for($i = $from; $i <= $to; $i++) {
    				$multipage .= $i == $curpage ? '<strong class="p_cur">'.$i.'</strong>' :
    					'<a href="'.$mpurl.$i.$postfix.'" class="p"'.$onclick.'>'.$i.'</a>';
    			}
    		}
    		if($simple == 2) {
                $next_page = 'More...';
    		} else {
                $next_page = '下一页&rsaquo;&rsaquo;';
    		}
    		$multipage .= ($curpage < $pages ? '<a href="'.$mpurl.($curpage + 1).$postfix.'" class="p'.$ajax.'"'.$onclick.'>'.$next_page.'</a>' : '').
    			($to < $pages && !$simple ? '<a href="'.$mpurl.$pages.$postfix.'" class="p'.$ajax.'"'.$onclick.'>&rsaquo;|</a>' : '').
    			($curpage == $maxpages ? '<a class="p'.$ajax.'" href="misc.php?action=maxpages&amp;pages='.$maxpages.'">&rsaquo;?</a>' : '').
    			(!$simple && $pages > $page ? '<a class="p_pages" style="padding: 0px"><input class="inputtext" style="width:30px;" type="text" name="custompage" onKeyDown="if(event.keyCode==13) {window.location=\''.$mpurl.'\'+this.value + \''.$postfix.'\'; return false;}"></a><span class="p_edge">(&nbsp;'.$curpage.'&nbsp;/&nbsp;'.$realpages.'&nbsp;)</span>' : '');
    
    		$multipage = $multipage ? '<div class="page_inner">'.$multipage.(!$simple ? '' : '').'</div>' : '';
    	}
    	return $multipage;
    }

    public static function FilterPageURL($_page){
        if(isset($_page) && preg_match("%^[1-9]\d*$%i", $_page)) {
            $page = $_page;
        } else {
        	$page =1;
        }
        return $page;
    }

    public static function GetPageURL($url) {
        $url = explode('?page=',$url);
        if (preg_match("%^[1-9]\d*$%i", $url[1])) {
            $page = intval(trim($url[1]));
        }
        return $page;
    }


    public static function SubURL($url) {
        $P2HUrl = $url;
        $P2HUrlStrHtml = str_replace("?", "", strrchr($P2HUrl, "?"));//分离出参数变量
        $P2HUrlStr = str_replace(".html", "", $P2HUrlStrHtml);//去掉.html

        $P2HUrlArr = explode("&", $P2HUrlStr);

        foreach($P2HUrlArr as $P2HUrlArrItem) {
            $P2HUrlArrList = explode("=", $P2HUrlArrItem);
            $o[$P2HUrlArrList[0]] = $P2HUrlArrList[1];
        }
        return $o;
    }

    public static function returnAjaxStatus($addon='ok') {
    	header('Content-type: text/plain; charset=utf-8');
    	header('Cache-control: no-cache, must-revalidate');
    	header("Pragma: no-cache");
    
    	if(is_array($addon)){
    		foreach ($addon as $key => $value) {
    			$e[$key] = $value;
    		}
    	}
    	$e['status'] = 'ok';
    	$encoded = json_encode($e);
    	echo $encoded;
    	die();
    }

    public static function AjaxReferer($location,$referer=1,$addon='') {
        $url = GlobalCore::SubURL($_SERVER['REQUEST_URI']);
        if ($url['ajax']== 1) {
            GlobalCore::returnAjaxStatus($addon);
        } elseif (GlobalCore::nwReferer() && $referer==1) {
            GlobalCore::nwHeader('Location: '.GlobalCore::nwReferer());
        } else {
            GlobalCore::nwHeader('Location: '.$location);
        }
    }

}
