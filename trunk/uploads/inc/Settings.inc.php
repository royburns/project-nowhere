<?php		
	$GLOBALS['debug'] =1;
	
	$_CHOBITS['settings'] = array(
		'gzipcompress' => 1,
		'transsidstatus' => 0,
		'dateformat' => 'Y-n-j',
		'debug' => '1',
		'timeformat' => 'H:i',
		'timeoffset' => '8',
		'onlinehold' => 900,
		'seccodestatus' => '1',
		'connid' => 0,
		'msgforward' => 'a:3:{s:11:"refreshtime";i:3;s:5:"quick";i:1;s:8:"messages";a:1:{i:0;s:13:"login_succeed";}}',
	);
	$basic_settings = array(
	   'sitename'=>'微博标题',
	   'site_intro'=>'微博介绍',
	   'custom_link'=>'自定义链接',
	   'keywords'=>'关键词设置',
	   'copyright'=>'版权声明',
	);
	
	$webservice_settings = array(
        'twitter'=>'Twitter|http://twitter.com/%1$s',
        'facebook'=>'Facebook|http://facebook.com/%1$s',
        'lastfm'=>'Last.fm|http://cn.last.fm/user/%1$s',
        'bangumi'=>'Bangumi 番组计划|http://chii.in/user/%1$s',
        'dianping'=>'大众点评网|http://www.dianping.com/member/%1$s',
        'dango'=>'Dango 美食分享|http://hoto.cn/people/%1$s',
        'friendfeed'=>'FriendFeed|http://friendfeed.com/%1$s',
        'douban'=>'豆瓣|http://douban.com/people/%1$s',
        'flickr'=>'Flickr|http://www.flickr.com/photos/%1$s',
        'greader'=>'Google Reader|http://www.google.com/reader/shared/%1$s',
        'wakoopa'=>'Wakoopa|http://wakoopa.com/%1$s',
	);
?>