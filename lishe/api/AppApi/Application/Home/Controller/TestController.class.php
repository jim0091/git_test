<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[测试接口];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Home/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[TestController.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-1-4 15:06
 * +----------------------------------------------------------------------
 * |@Email:		<Angelljoy@sina.com>		@OverTime:	2016
 * +----------------------------------------------------------------------
 *  */
namespace Home\Controller;
use Think\Controller;
class TestController extends Controller {
	public function _initialize() {
		header ( "content-type:text/html;charset=utf-8" );
	}
	
	public function updatesql(){
		$ModelObj = M('ectools_payments');
		$where['pay_from'] = 'APP';
		$where['status'] = array('eq','succ');
// 		$where['payed_time'] = array('neq',null);
		$paymentData = $ModelObj->where($where)->order('created_time desc')->select();
// 		var_dump($paymentData);exit;
		foreach ($paymentData as $V){
// 			$data['cur_money'] = 0;
			$data['cur_money'] = $V['money'];
			$data['point_fee'] = $V['money']*100;
// 			$data['payed_point'] = 0;
			$data['payed_point'] = $V['money']*100;

			if(empty($V['pay_app_id'])){
				$data['pay_app_id'] = 'point';
			}
			
			if(empty($V['pay_name'])){
				$data['pay_name'] = '积分支付';
			}
			
			if(empty($V['modified_time'])){
				$data['modified_time'] = $V['created_time'];
			}
			
			$cond['payment_id'] = $V['payment_id'];
			$bool  = $ModelObj->where($cond)->save($data);
			if($bool){
				$boolean +=$bool;
				var_dump($boolean);
			}else{
				var_dump($V['payment_id']);
			}
		}
		
// 		for ($i=0;$i<count($paymentData);$i++){
// 			$data['cur_money'] = 0;
// 			$data['point_fee'] = $paymentData[$i]['money']*100;
// 			$data['payed_point'] = 0;
// 			if(!$paymentData[$i]['money']){
// 				$data['pay_app_id'] = 'point';
// 			}
// 			$bool  = $ModelObj->where($where)->save($data);
// 			if($bool){
// 				var_dump($bool);
// 			}else{
// 				var_dump($paymentData[$i]['payment_id']);
// 			}
// 		}
// 		var_dump($paymentData);
		exit;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function test1(){
// 		$json = '{"result":100,"errcode":0,"msg":"success","data":{"info":{"orderno":"2017021021001004570294180108","userPointsList":[{"id":2725,"userId":123452175,"pointTypeId":1,"totalScore":500000.00,"usedScore":0.00,"remainScore":500000.00,"freezeScore":0.00,"version":2,"pointName":"商城通用积分","pointType":1}],"transno":"1486726114409"}}}';
// 		$yiqiyisheReturn_Obj = json_decode($json);
// // 		$result = $yiqiyisheReturn_Obj;
// 		$dataInfo = $yiqiyisheReturn_Obj->data->info;
// 		var_dump($dataInfo->userPointsList[0]->remainScore);
		
// // 		$result = json_decode($json);
// // 		$dataInfo = $result->data->info;
// // 		var_dump($dataInfo);
// // 		var_dump($dataInfo->userPointsList[0]->remainScore);
		
// 		exit;
		
		
		
		
		
		
		
		
		
		
		$string = 'partner=&quot;2088911943487400&quot;&amp;seller_id=&quot;joy@lishe.cn&quot;&amp;out_trade_no=&quot;2017021515043483091&quot;&amp;subject=&quot;礼舍积分充值&quot;&amp;body=&quot;礼舍积分充值&quot;&amp;total_fee=&quot;0.01&quot;&amp;service=&quot;mobile.securitypay.pay&quot;&amp;payment_type=&quot;1&quot;&amp;_input_charset=&quot;utf-8&quot;&amp;it_b_pay=&quot;30m&quot;&amp;show_url=&quot;m.alipay.com&quot;&amp;success=&quot;true&quot;&amp;sign_type=&quot;RSA&quot;&amp;sign=&quot;TUNRURbK/OPmhlGSt6QLkFPA9zq0hzmRW1fa0TwlE3/kp1Uftefppz44eDmfP3r8q8UQKI8teJI7MDnkkqvWEj2fKLHv8Yp6Q1/uFLd0E4/cyihEXSk345fHaHsZy55pCepb2lbRpwm1WW+j5STwXA48cTYMKEbhJzQZtaHWd6g=&quot;';
		$string1 = 'partner=&quot;2088911943487400&quot;&amp;seller_id=&quot;joy@lishe.cn&quot;&amp;out_trade_no=&quot;20170323154944118141&quot;&amp;subject=&quot;礼舍积分充值&quot;&amp;body=&quot;礼舍积分充值&quot;&amp;total_fee=&quot;30.00&quot;&amp;service=&quot;mobile.securitypay.pay&quot;&amp;payment_type=&quot;1&quot;&amp;_input_charset=&quot;utf-8&quot;&amp;it_b_pay=&quot;30m&quot;&amp;show_url=&quot;m.alipay.com&quot;&amp;success=&quot;true&quot;&amp;sign_type=&quot;RSA&quot;&amp;sign=&quot;FdTXNftULt3E00P9uYfSDjA6RHUAA9ur19zMQ008w0wEbB78FR6DplxvSHif5IZmOt5F/zYYvbk2PnQficeS6/OXadAcdmCv2qWgphwLkiP/GvJoEBz4lnrEtYqd7RH5/5EDCzWlwA0QKzuX11XUc25ME/sj/dmcImiUwMlhDl8=&quot;';
// 		$id = $_GET['id'];
// 		$UserName = $this->test2($id);
// 		var_dump($UserName);
		//17010615191308309
		
		//17010615191308309
		//17010615191308309
// 		$string1 = strpos($string,"&quot;");
// 		$str=substr_replace($string,"",$string1,-1);
// 		var_dump($str);
		$str1 = str_replace("&quot;",'',$string1);
		$str2 = str_replace("&amp",'',$str1);
		$str3=explode(";", $str2);
		$out_trade_no_string=explode("=", $str3[2]);
		$out_trade_no = $out_trade_no_string[1];
		$total_fee_string = explode("=", $str3[5]);
    	$total_fee = $total_fee_string[1];//支付宝返回的货币金额
// 		var_dump($out_trade_no);
		var_dump($str3);
		var_dump($total_fee_string);
		var_dump($total_fee);
		//2017021515043483091
		//2088911943487400
		//54998,53754
		
		///55881
		///55559
	}
	
	
	public function selectItem(){
		$itemid = '53754,37171,51766,30511,30508,8731,8732,30512,30510,30507,12324,54164,52978,52931,52979,50582,45022,13195,8810,53923,957,11477,43017,53959,53908,47986,53925,53909,53903,53848,47464,37456,37427,35822,32959,11481,32570,37174,54989,19817,32572,32562,19928,37196,37193,32561,32560,29844,19842,19833,19822,11649,53753,54167,54229,54228,48520,55881,54176,54174,54173,50678,53320,35573,35567,35617,19212,41759,48349,3489,40419,19099,19213,29609,19214,39928,29656,29649,35632,54974,48373,17953,51055,18036,54998,51032,48370,55559,40834,18002,12580,29664,54966,12587,51033,18044';
		$itemidArr = explode(",", $itemid);
		$Model = M('sysitem_item');
		$Model_app = M('sysitem_item_app');
		for ($i=0;$i<count($itemidArr);$i++){
			$where['item_id'] = $itemidArr[$i];
			$findData = $Model->where($where)->find();
			$boolData = $Model_app->add($findData);
			if($boolData){
				var_dump($findData['item_id']."成功");
			}else{
				var_dump($findData['item_id']."失败");
			}
		}
	}
	
	
	
	
	
	
	public function test2(){
		$expression = '{"result":100,"errcode":0,"msg":"\u8bf7\u91cd\u65b0\u767b\u5f55"}';
		var_dump(json_decode($expression));
		exit;
		var_dump($expression);
		$orderObj = D("order");
		if(empty($id)){
			return "id不能为空噢";
			break;
		}else{
			$name = "lihongqiang";
			return $name;
		}
	}

	
	public function test3(){
// 		var_dump(22);exit;
		//修改密码
		$data['md5_password'] = md5('123456');
		$where['user_id'] = '10452';
		$bool = M('sysuser_user_deposit')->where($where)->save($data);
		var_dump($bool);
	}
	
	
	//测试支付宝
	public function testPay(){
		//http://www.lishe.cn/shop.php/Recharge/aliPayReturnUrl?body=%E7%A7%AF%E5%88%86%E5%85%85%E5%80%BC&buyer_email=276657532%40qq.com&buyer_id=2088112013481573&exterface=create_direct_pay_by_user&is_success=T&notify_id=RqPnCoPT3K9%252Fvwbh3InZdalH7fg2A1uE8GluLpuDrkc5GgwbIRHvWBvFtmVAJU4K%252BeAy&notify_time=2017-02-10+10%3A28%3A20&notify_type=trade_status_sync&out_trade_no=20170210102747104521&payment_type=1&seller_email=joy%40lishe.cn&seller_id=2088911943487400&subject=%E7%A7%AF%E5%88%86%E5%85%85%E5%80%BC&total_fee=1.00&trade_no=2017021021001004570294180108&trade_status=TRADE_SUCCESS&sign=b123a9dd7ebd007334f35c33dae87bc2&sign_type=MD5
		
		//http://www.lishe.cn/shop.php/Recharge/aliPayReturnUrl
		//body=%E7%A7%AF%E5%88%86%E5%85%85%E5%80%BC
		//buyer_email=276657532%40qq.com
		//buyer_id=2088112013481573
		//exterface=create_direct_pay_by_user
		//is_success=T
		//notify_id=RqPnCoPT3K9%252Fvwbh3InZdalH7fg2A1uE8GluLpuDrkc5GgwbIRHvWBvFtmVAJU4K%252BeAy
		//notify_time=2017-02-10+10%3A28%3A20
		//notify_type=trade_status_sync
		//out_trade_no=20170210102747104521
		//payment_type=1
		//seller_email=joy%40lishe.cn
		//seller_id=2088911943487400
		//subject=%E7%A7%AF%E5%88%86%E5%85%85%E5%80%BC
		//total_fee=1.00
		//trade_no=2017021021001004570294180108
		//trade_status=TRADE_SUCCESS
		//sign=b123a9dd7ebd007334f35c33dae87bc2
		//sign_type=MD5
	}
	
}

/*
 .....'',;;::cccllllllllllllcccc:::;;,,,''...'',,'..
..';cldkO00KXNNNNXXXKK000OOkkkkkxxxxxddoooddddddxxxxkkkkOO0XXKx:.
.':ok0KXXXNXK0kxolc:;;,,,,,,,,,,,;;,,,''''''',,''..              .'lOXKd'
.,lx00Oxl:,'............''''''...................    ...,;;'.             .oKXd.
.ckKKkc'...'',:::;,'.........'',;;::::;,'..........'',;;;,'.. .';;'.           'kNKc.
.:kXXk:.    ..       ..................          .............,:c:'...;:'.         .dNNx.
:0NKd,          .....''',,,,''..               ',...........',,,'',,::,...,,.        .dNNx.
.xXd.         .:;'..         ..,'             .;,.               ...,,'';;'. ...       .oNNo
.0K.         .;.              ;'              ';                      .'...'.           .oXX:
.oNO.         .                 ,.              .     ..',::ccc:;,..     ..                lXX:
.dNX:               ......       ;.                'cxOKK0OXWWWWWWWNX0kc.                    :KXd.
.l0N0;             ;d0KKKKKXK0ko:...              .l0X0xc,...lXWWWWWWWWKO0Kx'                   ,ONKo.
.lKNKl...'......'. .dXWN0kkk0NWWWWWN0o.            :KN0;.  .,cokXWWNNNNWNKkxONK: .,:c:.      .';;;;:lk0XXx;
:KN0l';ll:'.         .,:lodxxkO00KXNWWWX000k.       oXNx;:okKX0kdl:::;'',;coxkkd, ...'. ...'''.......',:lxKO:.
oNNk,;c,'',.                      ...;xNNOc,.         ,d0X0xc,.     .dOd,           ..;dOKXK00000Ox:.   ..''dKO,
'KW0,:,.,:..,oxkkkdl;'.                'KK'              ..           .dXX0o:'....,:oOXNN0d;.'. ..,lOKd.   .. ;KXl.
;XNd,;  ;. l00kxoooxKXKx:..ld:         ;KK'                             .:dkO000000Okxl;.   c0;      :KK;   .  ;XXc
'XXdc.  :. ..    '' 'kNNNKKKk,      .,dKNO.                                   ....       .'c0NO'      :X0.  ,.  xN0.
.kNOc'  ,.      .00. ..''...      .l0X0d;.             'dOkxo;...                    .;okKXK0KNXx;.   .0X:  ,.  lNX'
,KKdl  .c,    .dNK,            .;xXWKc.                .;:coOXO,,'.......       .,lx0XXOo;...oNWNXKk:.'KX;  '   dNX.
:XXkc'....  .dNWXl        .';l0NXNKl.          ,lxkkkxo' .cK0.          ..;lx0XNX0xc.     ,0Nx'.','.kXo  .,  ,KNx.
cXXd,,;:, .oXWNNKo'    .'..  .'.'dKk;        .cooollox;.xXXl     ..,cdOKXXX00NXc.      'oKWK'     ;k:  .l. ,0Nk.
cXNx.  . ,KWX0NNNXOl'.           .o0Ooldk;            .:c;.':lxOKKK0xo:,.. ;XX:   .,lOXWWXd.      . .':,.lKXd.
lXNo    cXWWWXooNWNXKko;'..       .lk0x;       ...,:ldk0KXNNOo:,..       ,OWNOxO0KXXNWNO,        ....'l0Xk,
.dNK.   oNWWNo.cXK;;oOXNNXK0kxdolllllooooddxk00KKKK0kdoc:c0No        .'ckXWWWNXkc,;kNKl.          .,kXXk,
'KXc  .dNWWX;.xNk.  .kNO::lodxkOXWN0OkxdlcxNKl,..        oN0'..,:ox0XNWWNNWXo.  ,ONO'           .o0Xk;
.ONo    oNWWN0xXWK, .oNKc       .ONx.      ;X0.          .:XNKKNNWWWWNKkl;kNk. .cKXo.           .ON0;
.xNd   cNWWWWWWWWKOkKNXxl:,'...;0Xo'.....'lXK;...',:lxk0KNWWWWNNKOd:..   lXKclON0:            .xNk.
.dXd   ;XWWWWWWWWWWWWWWWWWWNNNNNWWNNNNNNNNNWWNNNNNNWWWWWNXKNNk;..        .dNWWXd.             cXO.
.xXo   .ONWNWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWNNK0ko:'..OXo          'l0NXx,              :KK,
.OXc    :XNk0NWXKNWWWWWWWWWWWWWWWWWWWWWNNNX00NNx:'..       lXKc.     'lONN0l.              .oXK:
.KX;    .dNKoON0;lXNkcld0NXo::cd0NNO:;,,'.. .0Xc            lXXo..'l0NNKd,.              .c0Nk,
:XK.     .xNX0NKc.cXXl  ;KXl    .dN0.       .0No            .xNXOKNXOo,.               .l0Xk;.
.dXk.      .lKWN0d::OWK;  lXXc    .OX:       .ONx.     . .,cdk0XNXOd;.   .'''....;c:'..;xKXx,
.0No         .:dOKNNNWNKOxkXWXo:,,;ONk;,,,,,;c0NXOxxkO0XXNXKOdc,.  ..;::,...;lol;..:xKXOl.
,XX:             ..';cldxkOO0KKKXXXXXXXXXXKKKKK00Okxdol:;'..   .';::,..':llc,..'lkKXkc.
:NX'    .     ''            ..................             .,;:;,',;ccc;'..'lkKX0d;.
lNK.   .;      ,lc,.         ................        ..,,;;;;;;:::,....,lkKX0d:.
.oN0.    .'.      .;ccc;,'....              ....'',;;;;;;;;;;'..   .;oOXX0d:.
.dN0.      .;;,..       ....                ..''''''''....     .:dOKKko;.
lNK'         ..,;::;;,'.........................           .;d0X0kc'.
.xXO'                                                 .;oOK0x:.
.cKKo.                                    .,:oxkkkxk0K0xc'.
.oKKkc,.                         .';cok0XNNNX0Oxoc,.
.;d0XX0kdlc:;,,,',,,;;:clodkO0KK0Okdl:,'..
.,coxO0KXXXXXXXKK0OOxdoc:,..

* */