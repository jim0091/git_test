<?php
/* * 
 * 功能：支付宝页面跳转同步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************页面功能说明*************************
 * 该页面可在本机电脑测试
 * 可放入HTML等美化页面的代码、商户业务逻辑程序代码
 * 该页面可以使用PHP开发工具调试，也可以使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyReturn
 */

require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");
//计算得出通知验证结果
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyReturn();

if($verify_result){//验证成功
	//交易状态
    if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
    	$buyer_email = $_GET['buyer_email']; // xxx@qq.com 购买者email
        $buyer_id = $_GET['buyer_id']; //2088222286799218
    	$notify_time = $_GET['notify_time']; // 2016-06-28 17:47:31	 
    	$seller_email = $_GET['seller_email']; // 如 joy@lishe.cn
        $seller_id = $_GET['seller_id']; //2088911943487400
        $total_fee = $_GET['total_fee'];// 交易额
        $payment_type = $_GET['payment_type']; //支付类型
        $sign = $_GET['sign']; //签名
        $sign_type = $_GET['sign_type'];//签名类型
    	$out_trade_no = $_GET['out_trade_no']; //虚拟商品号
        $trade_no = $_GET['trade_no'];//支付宝交易号
    	$out_trade_arr = explode('a',$out_trade_no);
    	$payment_id = $out_trade_arr[0]; // 得到payment_id
    	$user_id = $out_trade_arr[1]; //得到用户id
        $userAccount = $out_trade_arr[2]; //得到当前用户账号       
    }
}else{
	echo "<script>alert('充值失败，返回页面后请重新尝试！');</script>";
}
echo "<script>window.location.href='/payment.html?payment_id=".$payment_id."&merge=1';</script>";
?>