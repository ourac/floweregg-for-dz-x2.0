<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$fromversion = $_GET[fromversion];
if($fromversion == '1.0') {
$sql=<<<EOF
ALTER TABLE `pre_common_plugin_fegglog` ADD `id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
EOF;
runquery($sql);
}
$finish = TRUE;
?>