<?php

if(!defined('IN_NOWHERE')) {
	exit('Access Denied');
}

class TemplateCore{
    public static function parse_template($tplfile, $templateid, $tpldir) {
        global $language, $subtemplates, $timestamp;

        $nest = 5;
        $file = basename($tplfile, '.htm');
        $objfile = NOWHERE_ROOT."./data/templates/{$templateid}_$file.tpl.php";

        if(!@$fp = fopen($tplfile, 'r')) {
            GlobalCore::chobits_exit("Current GlobalCore::template file './$tpldir/{$templateid}/$file.htm' not found or have no access!");
        } elseif($language['chobits_lang'] != 'templates' && !include GlobalCore::language('templates',LANG)) {
            GlobalCore::chobits_exit("<br />Current GlobalCore::template pack do not have a necessary GlobalCore::language file 'templates.lang.php' or have syntax error!");
        }

        $template = @fread($fp, filesize($tplfile));
        fclose($fp);

        $var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
        $const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";

        $subtemplates = array();
        for($i = 1; $i<=3; $i++) {
            if(GlobalCore::strexists($template, '{subtemplate')) {
                if (preg_match("/[\n\r\t]*\{subtemplate\s+([a-z0-9_]+):([a-z0-9_]+)\}[\n\r\t]*/ies", $template)) {
                    $template = preg_replace("/[\n\r\t]*\{subtemplate\s+([a-z0-9_]+):([a-z0-9_]+)\}[\n\r\t]*/ies", "TemplateCore::loadsubtemplate('\\1','\\2')", $template);
                } else {
                    $template = preg_replace("/[\n\r\t]*\{subtemplate\s+([a-z0-9_]+)\}[\n\r\t]*/ies", "TemplateCore::loadsubtemplate('\\1')", $template);
                }
            }
        }

        $template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
        $template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
        $template = preg_replace("/\{lang\s+(.+?)\}/ies", "TemplateCore::languagevar('\\1')", $template);
        $template = preg_replace("/\{faq\s+(.+?)\}/ies", "TemplateCore::faqvar('\\1')", $template);
        $template = str_replace("{LF}", "<?=\"\\n\"?>", $template);

        $template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
        $template = preg_replace("/$var_regexp/es", "TemplateCore::addquote('<?=\\1?>')", $template);
        $template = preg_replace("/\<\?\=\<\?\=$var_regexp\?\>\?\>/es", "TemplateCore::addquote('<?=\\1?>')", $template);

        $headeradd = '';
        if(!empty($subtemplates)) {
            $subtemplates = array_unique($subtemplates);
            $headeradd .= "\n0\n";
            foreach ($subtemplates as $fname) {
                $headeradd .= "|| GlobalCore::checktplrefresh('$tplfile', '$fname', $timestamp, '$templateid', '$tpldir')\n";
            }
            $headeradd .=";";
        }

        $template = "<? if(!defined('IN_NOWHERE')) exit('Access Denied'); {$headeradd}?>\n$template";

        $template = preg_replace("/[\n\r\t]*\{template\s+([a-z0-9_]+)\}[\n\r\t]*/is", "\n<? include GlobalCore::template('\\1'); ?>\n", $template);
        $template = preg_replace("/[\n\r\t]*\{template\s+(.+?)\}[\n\r\t]*/is", "\n<? include GlobalCore::template('\\1'); ?>\n", $template);
        $template = preg_replace("/[\n\r\t]*\{eval\s+(.+?)\}[\n\r\t]*/ies", "TemplateCore::stripvtags('<? \\1 ?>','')", $template);
        $template = preg_replace("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/ies", "TemplateCore::stripvtags('<? echo \\1; ?>','')", $template);
        $template = preg_replace("/([\n\r\t]*)\{elseif\s+(.+?)\}([\n\r\t]*)/ies", "TemplateCore::stripvtags('\\1<? } elseif(\\2) { ?>\\3','')", $template);
        $template = preg_replace("/([\n\r\t]*)\{else\}([\n\r\t]*)/is", "\\1<? } else { ?>\\2", $template);

        for($i = 0; $i < $nest; $i++) {
            $template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r]*(.+?)[\n\r]*\{\/loop\}[\n\r\t]*/ies", "TemplateCore::stripvtags('<? if(is_array(\\1)) { foreach(\\1 as \\2) { ?>','\\3<? } } ?>')", $template);
            $template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/loop\}[\n\r\t]*/ies", "TemplateCore::stripvtags('<? if(is_array(\\1)) { foreach(\\1 as \\2 => \\3) { ?>','\\4<? } } ?>')", $template);
            $template = preg_replace("/([\n\r\t]*)\{if\s+(.+?)\}([\n\r]*)(.+?)([\n\r]*)\{\/if\}([\n\r\t]*)/ies", "TemplateCore::stripvtags('\\1<? if(\\2) { ?>\\3','\\4\\5<? } ?>\\6')", $template);
        }

        $template = preg_replace("/\{$const_regexp\}/s", "<?=\\1?>", $template);
        $template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);

        if(!@$fp = fopen($objfile, 'w')) {
            GlobalCore::chobits_exit("Directory './data/templates/' not found or have no access!");
        }

        $template = preg_replace("/\"(http)?[\w\.\/:]+\?[^\"]+?&[^\"]+?\"/e", "TemplateCore::transamp('\\0')", $template);
        $template = preg_replace("/\<script[^\>]*?src=\"(.+?)\".*?\>\s*\<\/script\>/ise", "TemplateCore::stripscriptamp('\\1')", $template);

        flock($fp, 2);
        fwrite($fp, $template);
        fclose($fp);
    }

    public static function loadsubtemplate($file,$subdir='') {
        global $subtemplates;
        $tpldir = TPLDIR;
        $templateid = TEMPLATEID;
        if ($subdir && $subdir !='main') {
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
        $subtemplates[] = $tplfile;
        return @implode('', file($tplfile));
    }

    public static function transamp($str) {
        $str = str_replace('&', '&amp;', $str);
        $str = str_replace('&amp;amp;', '&amp;', $str);
        $str = str_replace('\"', '"', $str);
        return $str;
    }

    public static function addquote($var) {
        return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
    }

    public static function languagevar($var) {
        if(isset($GLOBALS['language'][$var])) {
            return $GLOBALS['language'][$var];
        } else {
            return "!$var!";
        }
    }

    public static function stripvtags($expr, $statement) {
        $expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
        $statement = str_replace("\\\"", "\"", $statement);
        return $expr.$statement;
    }

    public static function stripscriptamp($s) {
        $s = str_replace('&amp;', '&', $s);
        return "<script src=\"$s\" type=\"text/javascript\"></script>";
    }
}
