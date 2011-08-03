<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!$_G['inajax']){
	showmessage('�Ƿ�������ֲ�����հɣ�');
}
//�ж��Ƿ��¼
if(!$_G['uid']){
	showmessage('to_login', '', '', array('login' => 1));
}

//�жϲ�������
$do	= trim(dhtmlspecialchars($_G['gp_do']));
if(!in_array($do,array('sendegg','sendflower','getfeelog','del'))){
	showmessage('�Ƿ�������ֲ�����հɣ�');
}
$uid				= intval($_G['gp_uid']);
$pid				= intval($_G['gp_pid']);
$tid				= intval($_G['gp_tid']);

//��¼�û�
$discuz_uid			= $_G['uid'];
$discuz_user		= $_G['username'];
$discuz_groupid     = $_G['groupid'];//����Ա����ʱ������

$fegg_config	    = $_G['cache']['plugin']['floweregg'];
$ifadmindel= $discuz_groupid == 1 && $fegg_config['delfeggsay'] ? 1 : 0;

//ɾ���ظ���¼
if($do == "del"){
	!$ifadmindel && showmessage('��̨û�п���ɾ�����ܻ��������ǹ���Ա��');
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

//��ȡ�ʻ�����������¼
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
		$fegg['do'] = $fegg['type'] ? '���˼���' : '�Ͷ��ʻ�';
		$fegglis[] = $fegg;
	}

	include template("floweregg:fegg_log");
	exit();
}


if($uid == $discuz_uid){
	showmessage('�����Լ����Լ��ɣ�');
}
//�������
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
	$credittype = 2;//Ĭ��Ϊ��Ǯ
}
$fegg_credit = 'extcredits'.$credittype;
$creditname = $_G['setting']['extcredits'][$credittype]['title'];
$creditcunit= $_G['setting']['extcredits'][$credittype]['unit'];
if($_G['member'][$fegg_credit]){
	$usercredit = $_G['member'][$fegg_credit];
}else{
	$usercredit = getuserprofile($fegg_credit);
}

//�Ϸ�����֤
$msg = $docontent = '';
$filed = 'flower';
$dotitle = '�ʻ�����';
$msg_subject = $msg_content = '';

if($do == 'sendflower'){
	$dotitle = '���ʻ�,��Ҫ����'.$sendflower.$creditname;
	$docontent = '�ҷǳ�ͬ����Ĺ۵㣬�Ͷ��ʻ�����һ��';
	$filed = 'flower';
	$msg_subject = '��ϲ�������������ʻ��ˣ�';
	$msg_content = $discuz_user.' �������и��������ʻ�<br>������˵��'.$_G['gp_feggsay'].'<br><br><a href="forum.php?mod=redirect&goto=findpost&ptid='.$tid.'&pid='.$pid.'">���ֱ��</a>';

	//�û���Ȩ���ж�
	if(!$flower_allow && !in_array($discuz_groupid,$flowergroup)){
		$msg ='�����ڵ��û���û�����ʻ���Ȩ�ޣ���������~';
	}

	//����ʱ���ж�
	if(getcookie('sendflower') && $discuz_groupid != 1){
		$msg ='���'.$flowertime.'��֮�������ͻ���';
	}

	//�û����Ļ����ж�
	if($sendflower > $usercredit){
		$msg ='���Ѿ�û���㹻��'.$creditname.'���ͻ���';
	}
}elseif($do == 'sendegg'){
	$dotitle = '�Ҽ���,��Ҫ����'.$sendegg.$creditname;
	$docontent = '�Ҳ�ͬ����Ĺ۵㣬�Ҹ���������һ��';
	$filed = 'egg';
	$msg_subject = '�ǳ����ң����˳����ӳ������ˣ�';
	$msg_content = $discuz_user.' �������и������˼���<br>������˵��'.$_G['gp_feggsay'].'<br><br><a href="forum.php?mod=redirect&goto=findpost&ptid='.$tid.'&pid='.$pid.'">���ֱ��</a>';
	//�û���Ȩ���ж�
	if(!$egg_allow && !in_array($discuz_groupid,$egggroup)){
		$msg ='�����ڵ��û���û���Ҽ�����Ȩ�ޣ���������~';
	}

	//����ʱ���ж�
	if(getcookie('sendegg') && $discuz_groupid != 1){
		$msg ='���'.$eggtime.'��֮�������Ҽ�����';
	}

	//�û����Ļ����ж�
	if($sendegg > $usercredit){
		$msg ='���Ѿ�û���㹻��'.$creditname.'���Ҽ�����';
	}
}

if($msg){
	showmessage($msg);
}

if(submitcheck('feggsubmit', 0, $seccodecheck, $secqaacheck)) {
	if(dstrlen($_G['gp_feggsay']) > 200){
		showmessage('�������ܳ���200���ַ���');
	}

	$flowerinfo = DB::fetch_first("SELECT * FROM ".DB::table("common_plugin_fegg")." WHERE uid='$uid'");
	if(!$flowerinfo){
		$num = 1;
		DB::insert('common_plugin_fegg', array('uid' => $uid,$filed => $num));
	}else{
		$num = $flowerinfo[$filed]+1;
		DB::update('common_plugin_fegg', array($filed => $num), 'uid='.$uid);
	}

	//�ͻ������Ҽ����û��Ļ��ִ���
	$sendcost = ($do == 'sendflower') ?  '-'.$sendflower : '-'.$sendegg;
	updatemembercount($discuz_uid,array($fegg_credit => $sendcost));

	//�յ��ʻ����߼������û��Ļ��ִ���
	$getcost = ($do == 'sendflower') ?  $getflower : '-'.$getegg;
	updatemembercount($uid,array($fegg_credit => $getcost));

	//��¼�û������ʻ�����
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

	//����վ��֪ͨ
	notification_add($uid, 'system', 'system_notice', array('subject' => $msg_subject, 'message' => $msg_content), 1);

	//����cookie
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