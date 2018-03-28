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
	
?>
