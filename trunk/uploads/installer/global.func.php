<?php
function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
		(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}

function daddslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = daddslashes($val, $force);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}


function dir_writeable($dir) {
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = @fopen("$dir/test.test", 'w')) {
			@fclose($fp);
			@unlink("$dir/test.test");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
}

function dir_clear($dir) {
	global $lang;

	showjsmessage($lang['clear_dir'].' '.$dir);
	$directory = dir($dir);
	while($entry = $directory->read()) {
		$filename = $dir.'/'.$entry;
		if(is_file($filename)) {
			@unlink($filename);
		}
	}
	$directory->close();
	result(1, 1, 0);
}

function instheader() {
	global $charset;

	echo "<html><head>".
		"<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$charset\">".
		"<title>Project Parasy Installer </title>".
		"<link rel=\"stylesheet\" type=\"text/css\" id=\"css\" href=\"installer/style.css\"></head>".
		"<body>".
		"<div id=\"main\" align=\"center\"><div id=\"panel\" align=\"left\">".
		"<div class=\"install\">Nowhere Personal Microblog ".NW_VERSION." Installer</div>";
}
function header2() {
	echo "<table width=\"98%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">";
}
function step($action) {
		if (!$action) {
			$start ="current";
		} elseif ($action =='user') {
			$user ="current";
		} elseif ($action == 'install'){
			$install ="current";
		} elseif ($action == 'final'){
			$final ="current";
		}
			
		echo"<table id=\"menu\">
			<tr>
			<td class=\"".$start."\">安装开始</td>
			<td class=\"".$install."\">创建数据库结构</td>
			<td class=\"".$user."\">管理猿设置</td>
			<td class=\"".$final."\">安装完成</td>
			</tr>
			</table>";
}
function instfooter() {
	global $version;

	echo "<tr><td><hr size=\"1\" color=\"#EEE\" style=\"color: #EEE; background-color: #EEE; height: 1px; border: 0;\" /></td></tr>".
        	"<tr><td align=\"center\">".
            	"<b style=\"font-size: 11px\">Powered by <a href=\"http://code.google.com/p/project-nowhere/\" target=\"_blank\">Nowhere Personal Microblog".
          	"</a>".
          	"</td></tr></table>".
		"</div></div></body></html>";
}

function instmsg($message, $url_forward = '') {
	global $lang, $msglang;

	instheader();

	$message = $msglang[$message] ? $msglang[$message] : $message;

	if($url_forward) {
		$message .= "<br><br><br><a href=\"$url_forward\">$message</a>";
		$message .= "<script>setTimeout(\"redirect('$url_forward');\", 1250);</script>";
	} elseif(strpos($message, $lang['return'])) {
		$message .= "<br><br><br><a href=\"javascript:history.go(-1);\" class=\"mediumtxt\">$lang[message_return]</a>";
	}

	echo 	"<tr><td>
		<hr size=\"1\" color=\"#EEE\" style=\"color: #EEE; background-color: #EEE; height: 1px; border: 0;\" />
		<table width=\"560\" class=\"datatable\" align=\"center\">".
		"<tr bgcolor=\"#f1f1f1\"><td width=\"20%\" style=\"padding-left: 10px\">错误信息</td></tr>".
  		"<tr align=\"center\"><td class=\"message\">$message</td></tr></table></tr></td>";

	instfooter();
	exit;
}

function loginit($logfile) {
	global $lang;
	showjsmessage($lang['init_log'].' '.$logfile);
	$fp = @fopen('./forumdata/logs/'.$logfile.'.php', 'w');
	@fwrite($fp, '<'.'?PHP exit(); ?'.">\n");
	@fclose($fp);
	result(1, 1, 0);
}

function showjsmessage($message) {
	echo '<script type="text/javascript">showmessage(\''.addslashes($message).' \');</script>'."\r\n";
	flush();
	ob_flush();
}

function random($length) {
	$hash = '';
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$max = strlen($chars) - 1;
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}

function result($result = 1, $output = 1, $html = 1) {
	global $lang;

	if($result) {
		$text = $html ? '<font color="#0000EE">'.$lang['writeable'].'</font><br>' : $lang['writeable']."\n";
		if(!$output) {
			return $text;
		}
		echo $text;
	} else {
		$text = $html ? '<font color="#FF0000">'.$lang['unwriteable'].'</font><br>' : $lang['unwriteable']."\n";
		if(!$output) {
			return $text;
		}
		echo $text;
	}
}

function redirect($url) {

	echo "<script>".
		"function redirect() {window.location.replace('$url');}\n".
		"setTimeout('redirect();', 0);\n".
		"</script>";
	exit();

}

function alter_query($table,$field,$query_info) {
	global $lang, $dbcharset, $tablepre, $db;
	$query = $db->query("Describe {$tablepre}".$table." ".$field);
	if (mysql_num_rows($query)>0) {
		showjsmessage($tablepre.$table.' 字段 '.$field.' 已经存在');
	} else {
		$db->query("{$query_info}");
		showjsmessage('创建 '.$tablepre.$table.' 字段 '.$field.'...成功');
	}
}

function runquery($sql) {
	global $lang, $dbcharset, $tablepre, $db;

	$sql = str_replace("\r", "\n", str_replace(' cdb_', ' '.$tablepre, $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		$query = trim($query);
		if($query) {

			if(substr($query, 0, 26) == 'CREATE TABLE IF NOT EXISTS') {
				$name = preg_replace("/CREATE TABLE IF NOT EXISTS ([a-z0-9_]+) .*/is", "\\1", $query);
				showjsmessage($lang['create_table'].' '.$name.' ... '.$lang['succeed']);
				$db->query(createtable($query, $dbcharset));

			} elseif(substr($query, 0, 10) == 'ALTER TABLE') {
				$name = preg_replace("/ALTER TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
				showjsmessage('升级数据表 '.$name.' ... '.$lang['succeed']);
				$db->query(createtable($query, $dbcharset));
			} else {
				$db->query($query);
			}

		}
	}
}

function installplugin($plugindata,$plugin_info) {
	global $lang, $dbcharset, $tablepre, $db;
	$plugindata = preg_replace("/(#.*\s+)*/", '', $plugindata);
	$pluginarray = daddslashes(unserialize(base64_decode($plugindata)), 1);

	$query = $db->query("SELECT pluginid FROM {$tablepre}plugins WHERE identifier='{$pluginarray[plugin][identifier]}' LIMIT 1");
	if($db->num_rows($query)) {
		showjsmessage('插件 '.$plugin_info.' 已安装 ... 跳过');
	} else {
		$sql1 = $sql2 = $comma = '';
		foreach($pluginarray['plugin'] as $key => $val) {
			if($key == 'directory') {
				//compatible for old versions
				$val .= (!empty($val) && substr($val, -1) != '/') ? '/' : '';
			}
			$sql1 .= $comma.$key;
			$sql2 .= $comma.'\''.$val.'\'';
			$comma = ',';
		}
		$db->query("INSERT INTO {$tablepre}plugins ($sql1) VALUES ($sql2)");
		$pluginid = $db->insert_id();

		foreach(array('hooks', 'vars') as $pluginconfig) {
			if(is_array($pluginarray[$pluginconfig])) {
				foreach($pluginarray[$pluginconfig] as $config) {
					$sql1 = 'pluginid';
					$sql2 = '\''.$pluginid.'\'';
					foreach($config as $key => $val) {
						$sql1 .= ','.$key;
						$sql2 .= ',\''.$val.'\'';
					}
					$db->query("INSERT INTO {$tablepre}plugin$pluginconfig ($sql1) VALUES ($sql2)");
				}
			}
		}
		showjsmessage('正在安装插件 '.$plugin_info.'  ... 成功');
	}
}
function setconfig($string) {
	if(!get_magic_quotes_gpc()) {
		$string = str_replace('\'', '\\\'', $string);
	} else {
		$string = str_replace('\"', '"', $string);
	}
	return $string;
}

?>