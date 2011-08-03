<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$sql = <<<EOF
DROP TABLE IF EXISTS `pre_common_plugin_fegg`;
DROP TABLE IF EXISTS `pre_common_plugin_fegglog`;
EOF;

runquery($sql);
$finish = TRUE;
?>