<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$sql = <<<EOF
CREATE TABLE IF NOT EXISTS `pre_common_plugin_fegg` (
  `uid` mediumint(8) unsigned NOT NULL,
  `flower` int(10) NOT NULL default '0',
  `egg` int(10) NOT NULL default '0',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `pre_common_plugin_fegglog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `tid` mediumint(8) unsigned NOT NULL,
  `fromuid` mediumint(8) unsigned NOT NULL,
  `fromuser` char(15) NOT NULL DEFAULT '',
  `touid` mediumint(8) unsigned NOT NULL,
  `type` tinyint(1) default '0',
  `feggsay` varchar(255) NOT NULL DEFAULT '',
  `feggdate` int(10) NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY (`pid`)
) ENGINE=MyISAM;
EOF;

runquery($sql);
$finish = TRUE;
?>