<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!$_G['inajax']){
	showmessage('非法请求，你爸不是李刚吧！');
}
//判断是否登录
if(!$_G['uid']){
	showmessage('to_login', '', '', array('login' => 1));
}

//判断操作类型
$do	= trim(dhtmlspecialchars($_G['gp_do']));
if(!in_array($do,array('sendegg','sendflower','getfeelog','del'))){
	showmessage('非法请求，你爸不是李刚吧！');
}
$uid				= intval($_G['gp_uid']);
$pid				= intval($_G['gp_pid']);
$tid				= intval($_G['gp_tid']);

//登录用户
$discuz_uid			= $_G['uid'];
$discuz_user		= $_G['username'];
$discuz_groupid     = $_G['groupid'];//管理员不受时间限制

$fegg_config	    = $_G['cache']['plugin']['floweregg'];
$ifadmindel= $discuz_groupid == 1 && $fegg_config['delfeggsay'] ? 1 : 0;

//删除回复记录
if($do == "del"){
	!$ifadmindel && showmessage('后台没有开启删除功能或者您不是管理员！');
	$logid = intval($_G['gp_logid']);

	if(!$_G['gp_feggdelsubmit']){
		include template("floweregg:fegg_del");
		exit;
	}else{
		$query = DB::query("delete FROM ".DB::table("common_plugin_fegglog")." WHERE id='$logid'");

		$redirecturl = 'forum.php?mod=redirect&goto=findpost&ptid='.$tid.'&pid='.$pid;
		$magvalues = array();
		$magvalues['pid'] = $pid;
		showmessage('do_success', $redirecturl, $magvalues);
	}
}

//获取鲜花鸡蛋操作记录
if($do == 'getfeelog'){
	$limitnum = intval($fegg_config['limitnum']);

	$fegglist = array();
	if($limitnum > 0){
		$page = max(1, $_G['page']);
		$start_limit = ($page - 1) * $limitnum;
		$count = DB::result_first('SELECT count(*) FROM '.DB::table('common_plugin_fegglog')." WHERE pid='$pid'");
		$multi = multi($count, $limitnum, $page, "plugin.php?id=floweregg:fegg&do=getfeelog&pid=$pid");

		$query = DB::query("SELECT * FROM ".DB::table("common_plugin_fegglog")." WHERE pid='$pid' ORDER BY id DESC LIMIT $start_limit, $limitnum");
	}else{
		$query = DB::query("SELECT * FROM ".DB::table("common_plugin_fegglog")." WHERE pid='$pid' ORDER BY id DESC");
	}
	while($fegg = DB::fetch($query)) {
		$fegg['avatar'] = avatar($fegg['fromuid'], 'small');
		$fegg['dateline'] = dgmdate($fegg['feggdate'], 'u');
		$fegg['do'] = $fegg['type'] ? '砸了鸡蛋' : '送朵鲜花';
		$fegglis[] = $fegg;
	}

	include template("floweregg:fegg_log");
	exit();
}


if($uid == $discuz_uid){
	showmessage('不能自己玩自己吧！');
}
//插件配置
$credittype		    = $fegg_config['credittype'];
$sendflower			= $fegg_config['sendflower'] ? $fegg_config['sendflower'] : 10;
$sendegg			= $fegg_config['sendegg'] ? $fegg_config['sendegg'] : 10;
$getflower		    = $fegg_config['getflower'] ? $fegg_config['getflower'] : 5;
$getegg			    = $fegg_config['getegg'] ? $fegg_config['getegg'] : 5;
$flowertime			= $fegg_config['flowertime'] ? $fegg_config['flowertime'] : 1800;
$eggtime			= $fegg_config['eggtime'] ? $fegg_config['eggtime'] : 1800;
$flowergroup		= unserialize($fegg_config['flowergroup']);
$egggroup			= unserialize($fegg_config['egggroup']);

$flower_allow = $egg_allow = false;
if(!$flowergroup || !$flowergroup[0]){
	$flower_allow = true;
}
if(!$egggroup || !$egggroup[0]){
	$egg_allow = true;
}

if(!$credittype){
	$credittype = 2;//默认为金钱
}
$fegg_credit = 'extcredits'.$credittype;
$creditname = $_G['setting']['extcredits'][$credittype]['title'];
$creditcunit= $_G['setting']['extcredits'][$credittype]['unit'];
if($_G['member'][$fegg_credit]){
	$usercredit = $_G['member'][$fegg_credit];
}else{
	$usercredit = getuserprofile($fegg_credit);
}

//合法性验证
$msg = $docontent = '';
$filed = 'flower';
$dotitle = '鲜花鸡蛋';
$msg_subject = $msg_content = '';

if($do == 'sendflower'){
	$dotitle = '送鲜花,需要花费'.$sendflower.$creditname;
	$docontent = '我非常同意你的观点，送朵鲜花鼓励一下';
	$filed = 'flower';
	$msg_subject = '恭喜您，有人送你鲜花了！';
	$msg_content = $discuz_user.' 在帖子中给你送了鲜花<br>并附言说：'.$_G['gp_feggsay'].'<br><br><a href="forum.php?mod=redirect&goto=findpost&ptid='.$tid.'&pid='.$pid.'">点击直达</a>';

	//用户组权限判断
	if(!$flower_allow && !in_array($discuz_groupid,$flowergroup)){
		$msg ='您所在的用户组没有送鲜花的权限！不给力啊~';
	}

	//操作时间判断
	if(getcookie('sendflower') && $discuz_groupid != 1){
		$msg ='请隔'.$flowertime.'秒之后再来送花吧';
	}

	//用户消耗积分判断
	if($sendflower > $usercredit){
		$msg ='你已经没有足够的'.$creditname.'来送花！';
	}
}elseif($do == 'sendegg'){
	$dotitle = '砸鸡蛋,需要花费'.$sendegg.$creditname;
	$docontent = '我不同意你的观点，砸个鸡蛋反对一下';
	$filed = 'egg';
	$msg_subject = '非常不幸，有人朝你扔臭鸡蛋了！';
	$msg_content = $discuz_user.' 在帖子中给你砸了鸡蛋<br>并附言说：'.$_G['gp_feggsay'].'<br><br><a href="forum.php?mod=redirect&goto=findpost&ptid='.$tid.'&pid='.$pid.'">点击直达</a>';
	//用户组权限判断
	if(!$egg_allow && !in_array($discuz_groupid,$egggroup)){
		$msg ='您所在的用户组没有砸鸡蛋的权限！不给力啊~';
	}

	//操作时间判断
	if(getcookie('sendegg') && $discuz_groupid != 1){
		$msg ='请隔'.$eggtime.'秒之后再来砸鸡蛋吧';
	}

	//用户消耗积分判断
	if($sendegg > $usercredit){
		$msg ='你已经没有足够的'.$creditname.'来砸鸡蛋！';
	}
}

if($msg){
	showmessage($msg);
}

if(submitcheck('feggsubmit', 0, $seccodecheck, $secqaacheck)) {
	if(dstrlen($_G['gp_feggsay']) > 200){
		showmessage('字数不能超过200个字符！');
	}

	$flowerinfo = DB::fetch_first("SELECT * FROM ".DB::table("common_plugin_fegg")." WHERE uid='$uid'");
	if(!$flowerinfo){
		$num = 1;
		DB::insert('common_plugin_fegg', array('uid' => $uid,$filed => $num));
	}else{
		$num = $flowerinfo[$filed]+1;
		DB::update('common_plugin_fegg', array($filed => $num), 'uid='.$uid);
	}

	//送花或者砸鸡蛋用户的积分处理
	$sendcost = ($do == 'sendflower') ?  '-'.$sendflower : '-'.$sendegg;
	updatemembercount($discuz_uid,array($fegg_credit => $sendcost));

	//收到鲜花或者鸡蛋的用户的积分处理
	$getcost = ($do == 'sendflower') ?  $getflower : '-'.$getegg;
	updatemembercount($uid,array($fegg_credit => $getcost));

	//记录用户操作鲜花鸡蛋
	$fegglog = array(
		'pid' => $pid,
		'tid' => $tid,
		'fromuid' => $discuz_uid,
		'fromuser' => $discuz_user,
		'touid' => $uid,
		'type' => ($do == 'sendflower') ? 0 : 1,
		'feggsay' => addslashes($_G['gp_feggsay']),
		'feggdate' => TIMESTAMP
	);
	DB::insert('common_plugin_fegglog', $fegglog);

	//发送站内通知
	notification_add($uid, 'system', 'system_notice', array('subject' => $msg_subject, 'message' => $msg_content), 1);

	//设置cookie
	dsetcookie($do,$do,($do == 'sendflower') ?  $flowertime: $eggtime);


	$redirecturl = 'forum.php?mod=redirect&goto=findpost&ptid='.$tid.'&pid='.$pid;
	$showmessagecontent = 'do_success';

	$magvalues = array();
	$magvalues['tid'] = $tid;
	$magvalues['pid'] = $pid;
	$magvalues['uid'] = $uid;
	$magvalues[$filed] = $num;
	showmessage($showmessagecontent, $redirecturl, $magvalues);
}

if(!$_G['gp_feggsubmit']){
	include template("floweregg:fegg");
}
?>