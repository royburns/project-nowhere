-- --------------------------------------------------------

--
-- Table structure for table `nw_failedlogins`
--

CREATE TABLE IF NOT EXISTS nw_failedlogins (
  `ip` char(15) NOT NULL default '',
  `count` tinyint(1) unsigned NOT NULL default '0',
  `lastupdate` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ip`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Table structure for table `nw_members`
--

CREATE TABLE IF NOT EXISTS nw_members (
  `uid` mediumint(8) unsigned NOT NULL auto_increment,
  `email` char(50) NOT NULL default '',
  `username` char(15) NOT NULL default '',
  `nickname` varchar(30) NOT NULL,
  `password` char(32) NOT NULL default '',
  `avatar` varchar(255) NOT NULL,
  `adminid` tinyint(1) NOT NULL default '0',
  `groupid` smallint(6) unsigned NOT NULL default '0',
  `regip` char(15) NOT NULL default '',
  `regdate` int(10) unsigned NOT NULL default '0',
  `lastip` char(15) NOT NULL default '',
  `lastvisit` int(10) unsigned NOT NULL default '0',
  `lastactivity` int(10) unsigned NOT NULL default '0',
  `dateformat` char(10) NOT NULL default '',
  `timeformat` tinyint(1) NOT NULL default '0',
  `timeoffset` char(4) NOT NULL default '',
  PRIMARY KEY  (`uid`),
  UNIQUE KEY `username` (`username`),
  KEY `email` (`email`)
) ENGINE=MyISAM;


-- --------------------------------------------------------

--
-- Table structure for table `nw_sessions`
--

CREATE TABLE IF NOT EXISTS nw_sessions (
  `sid` char(6) character set utf8 collate utf8_bin NOT NULL default '',
  `ip1` tinyint(3) unsigned NOT NULL default '0',
  `ip2` tinyint(3) unsigned NOT NULL default '0',
  `ip3` tinyint(3) unsigned NOT NULL default '0',
  `ip4` tinyint(3) unsigned NOT NULL default '0',
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `username` char(15) NOT NULL default '',
  `groupid` smallint(6) unsigned NOT NULL default '0',
  `action` tinyint(1) unsigned NOT NULL default '0',
  `lastactivity` int(10) unsigned NOT NULL default '0',
  UNIQUE KEY `sid` (`sid`),
  KEY `uid` (`uid`)
) ENGINE=MEMORY;

-- --------------------------------------------------------

--
-- Table structure for table `nw_settings`
--

CREATE TABLE IF NOT EXISTS nw_settings (
  `variable` varchar(32) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`variable`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Table structure for table `nw_status`
--

CREATE TABLE IF NOT EXISTS nw_status (
  `stt_id` mediumint(8) unsigned NOT NULL auto_increment,
  `stt_uid` mediumint(8) unsigned NOT NULL default '0',
  `stt_status` varchar(255) NOT NULL,
  `stt_via` tinyint(3) unsigned NOT NULL default '0',
  `stt_replies` mediumint(8) unsigned NOT NULL default '0',
  `stt_dateline` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`stt_id`),
  KEY `stt_uid` (`stt_uid`)
) ENGINE=MyISAM ;