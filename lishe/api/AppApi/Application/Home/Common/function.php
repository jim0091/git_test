<?php
/**
  +------------------------------------------------------------------------------
 * ommon.php
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: common.php v001 2015-09-24
 * @description 公共函数库
  +------------------------------------------------------------------------------
 */

	//生成随机字符串
	function randStr($len,$isNum=false){
		if($isNum){
			$chars = array("0","1", "2","3", "4", "5", "6", "7", "8", "9");
		}else{
			$chars = array(
			"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "A", "B","D", "E", "F", "G","H","J","L", "M", "N","Q", "R","T", "U","Y", "2","3", "4", "5", "6", "7", "8", "9"
			);
		}
		$charsLen = count($chars) - 1;
		shuffle($chars);    // 将数组打乱
		$outStr= "";
		for ($i=0; $i<$len; $i++){
			$outStr .= $chars[mt_rand(0, $charsLen)];
		}
		return $outStr;
	}
	
	//UTF8转GB2312/Gbk
	function utf8Gb2312(&$item,$strType='gb2312'){
		if(is_array($item)){
			array_walk($item,'utf8Gb2312');
		}else{
			if($strType=='gb2312'){
				$item = iconv('UTF-8','gb18030//IGNORE',$item);
			}else{
				$item = mb_convert_encoding($item,'GBK','UTF-8');
			}
		}
	}
	
	//GB2312/Gbkז转UTF8
	function gb2312Utf8(&$item,$strType='gb2312'){
		if(is_array($item)){
			array_walk($item,'gb2312Utf8');
		}else{		
			if($strType=='gb2312'){
				$item = iconv('gb18030','UTF-8//IGNORE',$item);
			}else{
				$item = mb_convert_encoding($item,'UTF-8','GBK');
			}
		}
	}
	//根据id查询用户积分记录
	function getUserDeposit($uid){
		if (empty($uid)) {
			return false;
		}
		$userDepositInfo = M('sysuser_user_deposit')->where('user_id ='.$uid)->find();
		if ($userDepositInfo) {
			return $userDepositInfo;
		}else{
			return false;
		}
	}
	//根据用户id查询用户登录表
	function getUserAccount($uid){
		if (empty($uid)) {
			return false;
		}
		$userAccountInfo = M('sysuser_account')->where('user_id ='.$uid)->find();
		if ($userAccountInfo) {
			return $userAccountInfo;
		}else{
			return false;
		}
	}
	//根据用户id查询用户信息表
	function getUser($uid){
		if (empty($uid)) {
			return false;
		}
		$userInfo = M('sysuser_user')->where('user_id ='.$uid)->find();
		if ($userInfo) {
			return $userInfo;
		}else{
			return false;
		}
	}

	
?>
