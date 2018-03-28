<?php
/* *
 * 功能：支付宝服务器异步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */

require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");

//计算得出通知验证结果
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();

if($verify_result) {//验证成功
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//请在这里加上商户的业务逻辑程序代
	//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
	//交易状态
	$trade_status=$_POST['trade_status'];
    if($trade_status=='TRADE_FINISHED' || $trade_status=='TRADE_SUCCESS'){
		//支付宝交易号
		$trade_no=$_POST['trade_no'];
		//商户订单号
		$out_trade_no=$_POST['out_trade_no'];
		//交易额
		$total_fee=$_POST['total_fee'];
		
		$out_trade_arr = explode('a',$out_trade_no);
    	$payment_id = $out_trade_arr[0]; // 得到payment_id
    	$user_id = $out_trade_arr[1]; //得到用户id
        $userAccount = $out_trade_arr[2]; //得到当前用户账号
        
        $data=array(
            'totalFee'=>$total_fee,
            'userId'=>$user_id,
            'mobile'=>$userAccount,
            'tradeNo'=>$trade_no
        );
        $return=requestPost($alipay_config['api'], $data);
        @file_put_contents('logs/'.date('Ymd').'.txt',$return."\n",FILE_APPEND);
        echo "success";
    }
}else{
    //验证失败
    echo "fail";
	@file_put_contents('logs/'.date('Ymd').'.txt','fail:'.date('Y-m-d H:i:s')."\n",FILE_APPEND);
}

//模拟提交
function requestPost($url='', $data=array()){
    if(empty($url) || empty($data)){
        return false;
    }
    $o="";
    foreach($data as $k=>$v){
        $o.="$k=".$v."&";
    }
    $param=substr($o,0,-1);
    $ch=curl_init();//初始化curl
    curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
    curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch,CURLOPT_POSTFIELDS, $param);
    $return=curl_exec($ch);//运行curl
    curl_close($ch);
    return $return;
}
?>