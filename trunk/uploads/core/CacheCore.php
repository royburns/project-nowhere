<?php

if(!defined('IN_NOWHERE')) {
    exit('Access Denied');
}

class CacheCore{
    public static function chobits_arrayeval($array, $level = 0) {
        $space = '';
        for($i = 0; $i <= $level; $i++) {
            $space .= "\t";
        }
        $evaluate = "Array\n$space(\n";
        $comma = $space;
        if(is_array($array)) {
            foreach($array as $key => $val) {
                $key = is_string($key) ? '\''.addcslashes($key, '\'\\').'\'' : $key;
                $val = !is_array($val) && (!preg_match("/^\-?[1-9]\d*$/", $val) || strlen($val) > 12) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
                if(is_array($val)) {
                    $evaluate .= "$comma$key => ".CacheCore::chobits_arrayeval($val, $level + 1);
                } else {
                    $evaluate .= "$comma$key => $val";
                }
                $comma = ",\n$space";
            }
        }
        $evaluate .= "\n$space)";
        return $evaluate;
    }

    public static function chobits_writetocache($script, $cachenames, $cachedata = '', $prefix = '',$cachedir= 'cache' ) {
        global $authkey, $timestamp, $cache_flag;
        
        $dir = NOWHERE_ROOT.'./data/'.$cachedir.'/';
        if(!is_dir($dir)) {
            @mkdir($dir, 0777);
        }
        if($fp = @fopen("$dir$prefix$script.php", 'wb')) {
            fwrite($fp, "<?php".
                "\n//Created: ".date("M j, Y, G:i").
                "\n//Identify: ".md5($prefix.$script.'.php'.$cachedata.$authkey)."\n\n$cachedata?>");
            fclose($fp);
        } else {
            GlobalCore::chobits_exit('<strong>Codename.Chobits</strong><br /><br />Can not write to cache files, please check directory ./data/'.$cachedir);
        }
    }

    public static function chobits_getcachevars($data, $type = 'VAR') {
        $evaluate = '';
        foreach($data as $key => $val) {
            if(is_array($val)) {
                $evaluate .= "\$$key = ".CacheCore::chobits_arrayeval($val).";\n";
            } else {
                $val = addcslashes($val, '\'\\');
                $evaluate .= $type == 'VAR' ? "\$$key = '$val';\n" : "define('".strtoupper($key)."', '$val');\n";
            }
        }
        return $evaluate;
    }

}
