<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

@set_time_limit(1000);
set_magic_quotes_runtime(0);

if(PHP_VERSION < '4.1.0') {
	$_GET = &$HTTP_GET_VARS;
	$_POST = &$HTTP_POST_VARS;
}

define('IN_NOWHERE', TRUE);
define('NOWHERE_ROOT', '');

$installfile = basename(__FILE__);
$sqlfile = './installer/nowhere.sql';
$lockfile = './data/now_install.lock';
$attachdir = './attachments';
$attachurl = 'attachments';
$quit = FALSE;

@include './installer/install.lang.php';
@include './installer/global.func.php';
@include './inc/config.inc.php';
@include './core/DBCore.php';
@include './inc/DBCore.php';

$inslang = defined('INSTALL_LANG') ? INSTALL_LANG : '';
if(!is_readable($sqlfile)) {
	exit("Please upload all files to install Nowhere");
} elseif(!isset($dbhost) || !isset($cookiepre)) {
	instmsg('config_nonexistence');
} elseif(!ini_get('short_open_tag')) {
	instmsg('short_open_tag_invalid');
} elseif(file_exists($lockfile)) {
	instmsg('lock_exists');
} elseif(!class_exists('dbstuff')) {
	instmsg('database_nonexistence');
}

if(function_exists('instheader')) {
	instheader();
}

if(empty($dbcharset) && in_array(strtolower($charset), array('gbk', 'big5', 'utf-8'))) {
	$dbcharset = str_replace('-', '', $charset);
}

$action = $_POST['action'] ? $_POST['action'] : $_GET['action'];

if(is_writeable('./inc/config.inc.php')) {
	$writeable['config'] = result(1, 0);
	$write_error = 0;
} else {
	$writeable['config'] = result(0, 0);
	$write_error = 1;
}

step($action);
header2();
if(!$action) {
?>
<tr><td>
<?php

	$msg = '';
	$curr_os = PHP_OS;

	if(!function_exists('mysql_connect')) {
		$curr_mysql = $lang['unsupport'];
		$msg .= "<li>$lang[mysql_unsupport]</li>";
		$quit = TRUE;
	} else {
		$curr_mysql = "<strong class=\"green\">".$lang['support']."</strong>";
	}

	if (function_exists('apache_get_version')) {
		$runtime = '<small>' . apache_get_version() . '</small>';
		$_modules = apache_get_modules();
		if (in_array('mod_rewrite', $_modules)) {
			$mod_rewrite="<strong class=\"green\">支持</strong>";
		} else {
			$mod_rewrite="<strong class=\"red\">不支持</strong>";
		}
	} else {
		$runtime = 'CGI/FastCGI';
		$mod_rewrite="<strong class=\"red\">不支持</strong>";
	}

	if (function_exists('json_encode')) {
		$json_support="<strong class=\"green\">支持</strong>";
	} else {
		$json_support="<strong class=\"red\">不支持</strong>";
	}

	$curr_php_version = PHP_VERSION;
	if($curr_php_version < '4.0.6') {
		$msg .= "<li>$lang[php_version_406]</li>";
		$quit = TRUE;
	}

	if(@ini_get(file_uploads)) {
		$max_size = @ini_get(upload_max_filesize);
		$curr_upload_status = $lang['attach_enabled'].$max_size;
	} else {
		$curr_upload_status = $lang['attach_disabled'];
		$msg .= "<li>$lang[attach_disabled_info]</li>";
	}

	$curr_disk_space = intval(diskfreespace('.') / (1024 * 1024)).'M';

	$checkdirarray = array(
				'data' =>'./data',
				'cache' =>'./data/cache',
				'cachetpl' => './data/templates',
				'avatar' => './avatar'

			);

	foreach($checkdirarray as $key => $dir) {
		if(dir_writeable($dir)) {
			$writeable[$key] = result(1, 0);
			
		} else {
			$writeable[$key] = result(0, 0);
			$langkey = $key.'_unwriteable';
			$msg .= "<li>$lang[$langkey]</li>";
			$quit = TRUE;
		}
	}

	if($quit) {
		$submitbutton = '<input type="button" name="submit" value=" '.$lang['recheck_config'].' " style="height: 25" onclick="window.location=\'\'">';
	} else {
		$submitbutton = '<input type="submit" name="submit" value=" '.$lang['new_step'].' " style="height: 25">';
		$msg = $lang['preparation'];
	}

?>
<tr><td align="center">
<table class="datatable">
<tr style="font-weight: bold;" align="center" bgcolor="#f1f1f1"><td width="32%"><?=$lang['tips_message']?></td>
</tr><tr><td><span class="message"><?=$msg?></span></td>
</tr></table>
<table class="datatable">
<tr style="font-weight: bold;" align="center" bgcolor="#f1f1f1">
<td></td><td><?=$lang['env_required']?></td><td><?=$lang['env_best']?></td><td><?=$lang['env_current']?></td>
</tr><tr class="option">
<td class="altbg1">Web Server</td>
<td class="altbg2">Apache Web Server / IIS</td>
<td class="altbg1">Apache Web Server</td>
<td class="altbg2"><?=$runtime?></td>
</tr><tr class="option">
<td class="altbg1"><?=$lang['env_php']?></td>
<td class="altbg2">4.0.6+</td>
<td class="altbg1">5.2.0+</td>
<td class="altbg2"><?=$curr_php_version?></td>
</tr><tr class="option">
<td class="altbg1">MySQL</td>
<td class="altbg2">MySQL 4.1</td>
<td class="altbg1">MySQL 4.1+</td>
<td class="altbg2"><?=$curr_mysql?></td>
</tr>
<tr class="option">
<td class="altbg1"><?=$lang['env_diskspace']?></td>
<td class="altbg2">10M+</td>
<td class="altbg1"><?=$lang['unlimited']?></td>
<td class="altbg2"><?=$curr_disk_space?></td>
</tr>
<tr class="option">
<td class="altbg1">mod_rewrite</td>
<td class="altbg2"><?=$lang['support']?></td>
<td class="altbg1"><?=$lang['support']?></td>
<td class="altbg2"><?=$mod_rewrite?></td>
</tr>

<tr class="option">
<td class="altbg1">JSON</td>
<td class="altbg2"><?=$lang['unlimited']?></td>
<td class="altbg1"><?=$lang['support']?></td>
<td class="altbg2"><?=$json_support?></td>
</tr>
</table>
<table class="datatable">
<tr style="font-weight: bold;" align="center" bgcolor="#f1f1f1"><td width="33%"><?=$lang['check_catalog_file_name']?></td><td width="33%">所需权限</td><td width="33%"><?=$lang['check_currently_status']?></td></tr>
<tr class="option">
<td class="altbg1">./inc/config.inc.php</td>
<td class="altbg2"><?=$lang['readable']?></td>
<td class="altbg1"><?=$writeable['config']?></td>
</tr>

<tr class="option"><td class="altbg1">./data </td><td class="altbg2"><?=$lang['writeable']?></td><td class="altbg1"><?=$writeable['data']?></td></tr>
<tr class="option"><td class="altbg1">./data/cache </td><td class="altbg2"><?=$lang['writeable']?></td><td class="altbg1"><?=$writeable['cache']?></td></tr>

<tr class="option"><td class="altbg1">./data/templates </td><td class="altbg2"><?=$lang['writeable']?></td><td class="altbg1"><?=$writeable['cachetpl']?></td></tr>

<tr class="option">
<td class="altbg1">./avatar</td>
<td class="altbg2"><?=$lang['writeable']?></td>
<td class="altbg1"><?=$writeable['avatar']?></td>
</tr>
</table>
</tr></td>
<tr><td align="center">
<form method="post" action="<?=$installfile?>">
<input type="hidden" name="action" value="install">
<?=$submitbutton?>
</form><br /></td></tr>
<?php
} elseif($action == 'install') {

	$username = htmlspecialchars($_GET['username']);
	$email = htmlspecialchars($_GET['email']);
	$password = htmlspecialchars($_GET['password']);

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);

$extrasql = <<<EOT

EOT;

?>
<tr><td><hr noshade align="center" width="100%" size="1"></td></tr>
<tr><td align="center"><br>
<script type="text/javascript">
	function showmessage(message) {
		document.getElementById('notice').value += message + "\r\n";
	}
</script>
<textarea name="notice" style="width: 80%; height: 400px" readonly id="notice"></textarea>

<br><br>
<form method="post" action="<?=$installfile?>">
<input type="hidden" name="action" value="user">
<input type="button" name="submit" value="下一步" disabled style="height: 25" onclick="window.location='installer.php?action=user'" id="laststep">
</form>
<br>
</td></tr>
<?php
	$fp = fopen($sqlfile, 'rb');
	$sql = fread($fp, filesize($sqlfile));
	fclose($fp);

	runquery($sql);
	runquery($extrasql);
	
	dir_clear('./data/templates');
	dir_clear('./data/cache');


	echo '<script type="text/javascript">document.getElementById("laststep").disabled = false; </script>'."\r\n";
	echo '<script type="text/javascript">document.getElementById("laststep").value = \'下一步\'; </script>'."\r\n";
} elseif($action == 'user') {
    $db = new dbstuff;
    $db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
    $db->select_db($dbname);    	
    $query = $db->query("SELECT * FROM {$tablepre}members WHERE uid='1 Babel'");
    if (mysql_num_rows($query)>0) {
    	showjsmessage('用户已经创建，请继续安装');
    } else {
        if($_POST['saveconfig']) {
            $username = strtolower(addslashes(trim(stripslashes($_POST['username']))));
        	$email = htmlspecialchars($_POST['email']);
        	$password = md5($_POST['password']);
            $db->query("INSERT INTO {$tablepre}members (username, password, adminid, groupid, regip, regdate, lastvisit, lastactivity,  email, dateformat, timeformat, timeoffset ,avatar)
				VALUES ('$username', '$password', '1', '1',  'hidden', '$timestamp', '$timestamp', '$timestamp', '$email',  '0000-00-00', '0', '9999', '')");
            redirect("$installfile?action=user");
        }
    }
     if (mysql_num_rows($query)>0) {
?>
    <tr><td><hr noshade align="center" width="100%" size="1"></td></tr>
<tr><td align="center"><br />
用户已创建，请继续安装<br /><br />
<form method="post" action="<?=$installfile?>">
<input type="hidden" name="action" value="final">
<input type="button" name="submit" value="下一步" disabled style="height: 25" onclick="window.location='installer.php?action=final'" id="laststep">
</form>
<?php } else { ?>
<tr><td align="center">
<form method="post" action="<?=$installfile?>">
<table class="datatable">
<tr style="font-weight: bold;" align="center" bgcolor="#f1f1f1">
<td width="20%"><?=$lang['variable']?></td><td width="30%"><?=$lang['value']?></td><td width="50%"><?=$lang['comment']?></td>
</tr><tr>
<td class="altbg1">&nbsp;用户名</td>
<td class="altbg2"><input type="text" name="username" size="30"></td>
<td class="altbg1">&nbsp;用户名将用于登录系统，仅限于字母与下划线的组合</td>
</tr>
<tr>
<td class="altbg1">&nbsp;Email</td>
<td class="altbg2"><input type="text" name="email" size="30"></td>
<td class="altbg1">&nbsp;</td>
</tr>
<tr>
<td class="altbg1">&nbsp;密码</td>
<td class="altbg2"><input type="password" name="password"  size="30"></td>
<td class="altbg1">&nbsp;</td>
</tr>
</table>
<input type="hidden" name="saveconfig" value="1">
<input type="hidden" name="action" value="user">
<input type="submit" name="submit" value="<?=$lang['new_step']?> " style="height: 25px">
</form>
<?php
}
	echo '<script type="text/javascript">document.getElementById("laststep").disabled = false; </script>'."\r\n";
	echo '<script type="text/javascript">document.getElementById("laststep").value = \'下一步\'; </script>'."\r\n";
} elseif ($action =='final') {
?>
<tr><td align="center">
<table class="datatable">
<tr style="font-weight: bold;" align="center" bgcolor="#f1f1f1"><td width="32%">恭喜恭喜！</td></tr>
<tr>
<td><span class="message">&nbsp;&nbsp;&nbsp;&nbsp;可喜可贺，可口可乐。到此为之，属于你的Nowhere已经安装完成了！<br />&nbsp;&nbsp;&nbsp;&nbsp;接下来，你需要做的是：
<ul style="margin: 0px 0px 0px 2em; padding: 10px; list-style: square; list-style-image: none; list-style-position: outside;">
	<li>1. 深呼吸</li>
	<li>2. 微笑</li>
	<li>3. 登录系统后至控制面板进行最后配置</li>	
	<li>4. <strong class="red">为了保证论坛数据安全，请手动删除 installer.php 文件 和 ./installer 文件夹下的所有文件，如果您想重新安装Nowhere，请删除 data/nowhere_install.lock 文件，再次运行安装文件。</strong></li>
</ul>
</span></td>
</tr>
</table>
	<input type="button" name="submit" value="进入Nowhere" style="height: 25px" onclick="window.location='<?=NWDIR?>'" id="laststep">
</td></tr>
<?php
	@touch(NOWHERE_ROOT.$lockfile);
}

instfooter();

?>