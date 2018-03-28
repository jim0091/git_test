<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[函数库];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Common/Common/];
 * +----------------------------------------------------------------------
 * |@Name:			[function.php];
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3
 * +----------------------------------------------------------------------
 * |@License:(http://www.Apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-02-27 16:52
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@Overtime:	2016-06-22 
 * +----------------------------------------------------------------------
 *  */

 //获取当前时间
function getNow() {
	return date ( 'Y-m-d H:i:s');
}

 //获取当前日期
function getNowDate() {
	return date ( 'Y-m-d', time () );
}

//获取一个随机数
function getRandomNumber($min,$max){
	if($min&&$max){
		//根据最小值和最大值返回随机数
		 return mt_rand($min, $max);
	}else{
		//返回6位数字
		return mt_rand(100000, 999999);
	}
}

//上传图片文件唯一命名函数
function getFileName($dateTime){
	if (empty($dateTime)){
		$randomFileName = date("YmdHis").getMillisecond(6).mt_rand(100000,999999);
		return $randomFileName;
	}else{
		$randomFileName = $dateTime.getMillisecond(6).mt_rand(100000,999999);
		return $randomFileName;
	}
}

//获取毫秒数
function getMillisecond($digit=null){
	if($digit){
		list($t1,$t2) = explode(' ', microtime(false));
		$Millisecond = substr($t1,2,$digit);
		return $Millisecond;
	}else{
		list($t1,$t2) = explode(' ', microtime(false));
		$Millisecond = substr($t1,2);
		return $Millisecond;
	}
}




//把手机号中间四位变成*
function cutMobile($mobileNumber){
	if(empty($mobileNumber)){
		return false;
	}else{
		//$mobileString = substr_replace($mobileNumber,'****',1,4);
		$mobileString = substr_replace($mobileNumber,'用户',0,7);
		return $mobileString;
	}
	
}

/**
 * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * @name:判断一串数字是不是手机号
 * @author: lihongqiang   2017-03-02
 * @param: int $PhoneNumber
 * @return:boolean
 * @example:
 * 移动：134、135、136、137、138、139、150、151、152、157、158、159、182、183、184、187、188、178(4G)、147(上网卡)；
 * 联通：130、131、132、155、156、185、186、176(4G)、145(上网卡)；
 * 电信：133、153、180、181、189 、177(4G)；
 * 卫星通信：1349
 * 虚拟运营商：170
 */
function isPhoneNumber($PhoneNumber) {
	if (!is_numeric($PhoneNumber)) {
		return false;
	}else{
		if(preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $PhoneNumber)){
			return true;
		}else{
			return false;
		}
	}
}



function wordTime($time) {
	$time = strtotime($time);
	$time = (int) substr($time, 0, 10);
	$int = time() - $time;
	$str = '';
	if ($int <= 60){
		$str = sprintf('刚刚', $int);
// 	}elseif ($int < 60){
// 		$str = sprintf('%d秒前', $int);
	}elseif ($int < 3600){
		$str = sprintf('%d分钟前', floor($int / 60));
	}elseif ($int < 86400){
		$str = sprintf('%d小时前', floor($int / 3600));
	}elseif ($int < 259200){
		$str = sprintf('%d天前', floor($int / 86400));
	}else{
		$str = date('Y-m-d H:i:s', $time);
	}
	return $str;
}


