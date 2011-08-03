<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_floweregg{
	var $ifadmindel = false;
	var $limitshownum = 0;

	function plugin_floweregg() {
		global $_G;

		$this->limitshownum = intval($_G['cache']['plugin']['floweregg']['limitnum']);
		$this->ifadmindel = $_G['cache']['plugin']['floweregg']['delfeggsay'] && $_G['groupid'] ==1 ? 1 :0;
	}
}


class plugin_floweregg_forum extends plugin_floweregg{
	var $viewthread_postfooter_return = array();
	var $viewthread_sidebottom_return = array();

	function viewthread_postfooter_output(){
		global $postlist, $_G;
		foreach ($postlist as $pid => $onePost)
        {
            $touid = $onePost['authorid'];
            $tid = $onePost['tid'];
            $this->viewthread_postfooter_return[] = $this->_html_postfooter($pid, $tid, $touid);
        }

		return $this->viewthread_postfooter_return;
	}

	function _html_postfooter($pid, $tid, $touid)
    {
       static $fegginfo;
       if(!isset($fegginfo[$touid])) {
       		$fegginfo[$touid] = DB::fetch_first("SELECT * FROM ".DB::table("common_plugin_fegg")." WHERE uid='$touid'");
       }
       $flowerinfo = $fegginfo[$touid];
       if(!$flowerinfo){
       		$flowerinfo['flower']=$flowerinfo['egg']=0;
       }
       return '<a href="plugin.php?id=floweregg:fegg&do=sendflower&tid='.$tid.'&pid='.$pid.'&uid='.$touid.'" onclick="showWindow(\'sendlove\', this.href);return false;"><img src="source/plugin/floweregg/images/flower.gif" border="0"> œ ª®(<span name="flower_'.$touid.'">'.$flowerinfo[flower].'</span>)</a><a href="plugin.php?id=floweregg:fegg&do=sendegg&tid='.$tid.'&pid='.$pid.'&uid='.$touid.'" onclick="showWindow(\'sendlove\', this.href);return false;"><img src="source/plugin/floweregg/images/egg.gif" border="0"> º¶µ∞(<span name="egg_'.$touid.'">'.$flowerinfo[egg].'</span>)</a>';
    }


    function viewthread_sidebottom_output(){
    	global $postlist, $_G;

    	$fegginfo = array();
		foreach ($postlist as $pid => $onePost)
        {
            $touid = $onePost['authorid'];
            if(!isset($fegginfo[$touid])){
				$fegginfo[$touid] = DB::fetch_first("SELECT * FROM ".DB::table("common_plugin_fegg")." WHERE uid='$touid'");
            }
            if(!($flowerinfo = $fegginfo[$touid])){
       			$flowerinfo['flower']=$flowerinfo['egg']=0;
      		}
            $this->viewthread_sidebottom_return[] = '<div id="fegginfo_'.$touid.'" style="padding-left:10px"><img src="source/plugin/floweregg/images/flower.gif" border="0"> œ ª®(<span name="flower_'.$touid.'">'.$flowerinfo[flower].'</span>)<img src="source/plugin/floweregg/images/egg.gif" border="0"> º¶µ∞(<span name="egg_'.$touid.'">'.$flowerinfo[egg].'</span>)</div>';
        }

		return $this->viewthread_sidebottom_return;
    }


    function viewthread_postbottom_output() {
		global $_G, $postlist;
		$pids = $fegglist = $feggpid = $saycount = array();

		$pids =array_keys($postlist);
		if($pidstr = dimplode($pids)){
			$query = DB::query("SELECT * FROM ".DB::table("common_plugin_fegglog")." WHERE pid IN ($pidstr) order by id desc");
			while($result = DB::fetch($query)) {
				$saycount[$result['pid']]++;

				if(!$this->limitshownum || ($this->limitshownum && $this->limitshownum > count($feggpid[$result['pid']]))){
					$do = $result['type'] ? '‘“¡Àº¶µ∞' : 'ÀÕ∂‰œ ª®';
					$onelog= '<div class="pstl">' .
							'<div class="psta">'.avatar($result['fromuid'], 'small').'</div>' .
							'<div class="psti">' .
							'<a class="xi2 xw1" href="home.php?mod=space&uid='.$result['fromuid'].'">'.$result['fromuser'].'</a>' .
							'&nbsp;&nbsp;‘⁄'.dgmdate($result['feggdate'], 'u').'&nbsp;&nbsp;<b>'.$do.'</b>&nbsp;&nbsp;' .
							'≤¢Àµ£∫'.$result['feggsay'];
					if($this->ifadmindel){
						$onelog.='&nbsp;<span class="xg1"><a href="plugin.php?id=floweregg:fegg&do=del&logid='.$result['id'].'&pid='.$result['pid'].'" onclick="showWindow(\'delfegg\', this.href);return false;">…æ≥˝</a></span>';
					}
					$onelog.='</div></div>';

					$feggpid[$result['pid']][] = $onelog;
				}
			}
		}
		$feggstr= '';
		foreach($pids as $pid){
			if(isset($feggpid[$pid])){
				$feggstr = '<div class="cm" id="feggdiv_'.$pid.'">' .'<h3 class="psth cm">œ ª®º¶µ∞</h3>'.implode('',$feggpid[$pid]);

				if($this->limitshownum && $saycount[$pid] > $this->limitshownum){
					$feggstr.='<div class="pgs mbm cl"><div class="pg"><a href="javascript:;" class="nxt" onclick="ajaxget(\'plugin.php?id=floweregg:fegg&do=getfeelog&pid='.$pid.'&page=2\', \'feggdiv_'.$pid.'\')">œ¬“ª“≥</a></div></div>';
				}
				$feggstr.='</div>';

			}else{
				 $feggstr= '<div class="cm" id="feggdiv_'.$pid.'"></div>';
			}
			$fegglist[] = $feggstr;
		}
		return $fegglist;
	}
}

?>