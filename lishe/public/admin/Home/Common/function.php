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
	

	//字符串解密加密,$operation = 'EN'加密，$operation = 'DE'解密
	function authCode($string,$operation='EN',$key ='',$expiry=0){
		$ckeyLength = 4;// 随机密钥长度 取值 0-32;
		// 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
		// 取值越大，密文变动规律越大，密文变化 = 16 的 $ckeyLength 次方
		// 当此值为 0 时，则不产生随机密钥	
		$key = md5($key?$key:UE_KEY);
		$keya = md5(substr($key,0,16));
		$keyb = md5(substr($key,16,16));
		$keyc = $ckeyLength?($operation=='DE'?substr($string,0,$ckeyLength):substr(md5(microtime()),-$ckeyLength)):'';	
		$cryptkey = $keya.md5($keya.$keyc);
		$keyLength = strlen($cryptkey);	
		$string = $operation == 'DE' ? base64_decode(substr($string, $ckeyLength)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$stringLength = strlen($string);	
		$result = '';
		$box = range(0, 255);	
		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $keyLength]);
		}	
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}	
		for($a = $j = $i = 0; $i < $stringLength; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}	
		if($operation == 'DE') {
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {				
				return substr($result, 26);
			} else {
				return '';
			}
		}else{
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	}
/**
 * 键值反转
 * @param array $array
 * @param string $keyName
 * @param number $model 模式，$model=1时返回值为一维数组，当值为2时 返回值为二维数组, 数组key为$keyName对应的值
 * @author Gaolong
 */
function keyValRev($list, $keyName, $valName='', $model=1){
	if(empty($list)){
		return array();
	}
	$revList = array();
	foreach ($list as $item){
		if(!empty($valName)){
			if($model == 1){
				$revList[$item[$keyName]] = $item[$valName];
			}else if($model == 2){
				$revList[$item[$keyName]][] = $item[$valName];
			}
		}else{
			if($model == 1){
				$revList[$item[$keyName]] = $item;
			}else if($model == 2){
				$revList[$item[$keyName]][] = $item;
			}
			
		}
	}
	return $revList;
}
/**
 *把相同键值的合并生成一个新的二维数组
 * @author Zhangrui
 * @param  [type] $arr [description]
 * @param $keyName比较键值的键
 * @param $valName键值
 **/
 function sameValRegroupArr($arr, $keyName, $valName = ''){
 	if(!is_array($arr) || empty($keyName)){
 		return array();
 	}
	$newArr = array();
	foreach($arr as $key => $val){
		if(empty($valName)){
			$newArr[$val[$keyName]][] = $val;
		}else{
			$newArr[$val[$keyName]][] = $val[$valName];
		}
	}	
	return $newArr;	
 }
/**
 * 分页
 */
function showPage($pageCount, $pageSize){
	$page = new Think\Page($pageCount, $pageSize);
	$page->setConfig('prev', '上一页');
	$page->setConfig('next', '下一页');
	$page->setConfig('first', '首页');
	$page->setConfig('last', '最后一页');
	$page->rollPage = 8;
//	$page->lastSuffix = false;
	return $page->show("pageos","pageon");
}
/**
 * 供应商订单状态直译
 * @author Zhangrui
 * */
function orderSuppStatus($status){
	switch($status){
		case "WAIT_BUYER_PAY":
			$status = "待付款";
			break;
		case "TRADE_CLOSED_BY_SYSTEM":
			$status = "已取消(系统)";
			break;
		case "TRADE_CLOSED_BY_ADMIN":
			$status = "已取消(管理员)";
			break;				
		case "TRADE_CLOSED_BY_USER":
			$status = "已取消(用户)";
			break;				
		case "TRADE_FINISHED":
			$status = "已完成";
			break;
		case "WAIT_SELLER_SEND_GOODS":
			$status = "待发货";
			break;	
		case "WAIT_BUYER_CONFIRM_GOODS":
			$status = "已发货";
			break;	
		case "IN_STOCK":
			$status = "备货中";
			break;
		default:
			$status = "--";
	}
	return $status;	
	
} 
/**
 * 订单状态直译
 * @author Zhangrui
 * */
function orderStatus($status){
	switch($status){
		case "WAIT_BUYER_PAY":
			$status = "待付款";
			break;
		case "TRADE_CLOSED_BY_SYSTEM":
			$status = "已取消(系统)";
			break;
		case "TRADE_CLOSED_BY_ADMIN":
			$status = "已取消(管理员)";
			break;				
		case "TRADE_CLOSED_BY_USER":
			$status = "已取消(用户)";
			break;				
		case "TRADE_FINISHED":
			$status = "已完成";
			break;
		case "WAIT_SELLER_SEND_GOODS":
			$status = "待发货";
			break;	
		case "WAIT_BUYER_CONFIRM_GOODS":
			$status = "待收货";
			break;	
		case "IN_STOCK":
			$status = "备货中";
			break;
		default:
			$status = "--";
	}
	return $status;
}		
/**
 * 售后类型直译
 * @author Zhangrui
 * */
function aftersaleStatus($serviceStatus){
	switch($serviceStatus){
		case "NO_APPLY":
			$serviceStatus = "无售后";
			break;
		case "REFUND":
			$serviceStatus = "申请退款";
			break;
		case "CANCEL_REFUND":
			$serviceStatus = "取消退款";
			break;				
		case "RETURN":
			$serviceStatus = "申请退货";
			break;
		case "CANCEL_RETURN":
			$serviceStatus = "取消退货";
			break;				
		case "EXCHANGE":
			$serviceStatus = "申请换货";
			break;	
		case "CANCEL_EXCHANGE":
			$serviceStatus = "取消换货";
			break;					
		case "REPAIR":
			$serviceStatus = "申请维修";
			break;		
		case "CANCEL_REPAIR":
			$serviceStatus = "取消维修";
			break;	
		default:
			$serviceStatus = "--";																									
	}		
	return $serviceStatus;
} 
/**
 * 售后进度直译
 * @author Zhangrui
 * */
function aftersaleProgress($progress){
	switch($progress){
		case "NO_APPLY":
			$progress = "无操作";
			break;	
		case "CANCEL_APPLY":
			$progress = "取消售后申请";
			break;					
		case "WAIT_EARLY_PROCESS":
			$progress = "待初审";
			break;						
		case "WAIT_PROCESS":
			$progress = "待审核";
			break;
		case "SELLER_REFUSE":
			$progress = "商家拒绝";
			break;
		case "REFUND_PROCESS":
			$progress = "待退款";
			break;				
		case "SUCCESS":
			$progress = "已完成";
			break;
		case "WAIT_BUYER_SEND_GOODS":
			$progress = "等待用户回寄";
			break;				
		case "WAIT_SELLER_CONFIRM_GOODS":
			$progress = "等待商家收货";
			break;	
		case "WAIT_REFUND":
			$progress = "待退款(商家已收到货)";
			break;					
		case "SELLER_SEND_GOODS":
			$progress = "商家已回寄";
			break;
		default:
			$progress = "--";					
	}		
	return $progress;
} 
/**
 *取出二维数组数组单值数组 
 * @author Zhangrui
 */
function arrGetField($arr,$field){
	if(empty($arr) || !is_array($arr)){
		return array();
	}
	$newArr = array();
	foreach($arr as $key => $val){
		$newArr[] = $val[$field];		
	}
	return array_unique($newArr);
	
}
/**
 * 代发预警比例
 * @author Zhangrui
 * $num1 数值1
 * $num2 数值2
 * $num3 除数
 * */
function getPercentRate($num1,$num2,$num3){
	if(!is_numeric($num1) || $num1<0 || !is_numeric($num2) || $num2<0 || !is_numeric($num3) || $num3<=0){
		return '0%';
	}
	$rate = round(($num1+$num2)/$num3,3)*100;
	return $rate.'%';
}
/*
 *  计算两个时间戳之间相差的日时分秒
 * */	
function timediff($begin_time,$end_time){
    if($begin_time < $end_time){
        $starttime = $begin_time;
        $endtime = $end_time;
    }else{
        $starttime = $end_time;
        $endtime = $begin_time;
    }
    //计算天数
    $timediff = $endtime-$starttime;
    $days = intval($timediff/86400);
    //计算小时数
    $remain = $timediff%86400;
    $hours = intval($remain/3600);
    //计算分钟数
    $remain = $remain%3600;
    $mins = intval($remain/60);
    //计算秒数
    $secs = $remain%60;
//  $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
		return "{$days}天{$hours}小时{$mins}分钟{$secs}秒";		
}
/**
 * 数组去空
 */
function trimArrVal($arr){
 		if(!is_array($arr)){
			return $arr;
 		}
 		foreach($arr as &$v){
 			if(is_string($v)){
 				$v=trim($v);
 			}
 		}
		return $arr;
 	}
	/**
	 * 活动类型
	 */
	function activityType($num){
		$type = array(
			'1' => '特价',
			'2' => '专题',
			'3' => '礼包',
			'4' => '团购',
			'5' => '预售',
			'6' => '集配礼包',
			'7' => '组合购',
		);
		return $type[$num];
	}	 
	 	

?>
