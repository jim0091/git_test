<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[充值接口];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Home/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[PayController.class.php];	
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
class PayController extends CommonController {
	public function _initialize() {
		header ( "content-type:text/html;charset=utf-8" );
	}
	
	//积分充值：1.首先要生成充值订单，得到payment_id	再调用支付软件去扣款
    public function inteRechDo(){
    	$postData = I('post.');
    	if (!empty($postData)){
    		$token = $postData['token'];
    		$money = $postData['money'];
    		$uid = $this->uid;
    		$account = $this->account;
    		$userName= $this->userName;
    		if (empty($userName)){
    			$userName = $this->account;
    		}
    		if($token){
    			if($userName && $uid && $account){
    				if(floatval($money)){
    					//插入支付表
    					$paymentsModel = M("ectools_payments");//支付表
    					$data['payment_id'] = date(YmdHis).$uid.'1';//支付单号
    					$data['money'] = floatval($money);//需要支付的金额
    					$data['cur_money'] = 0;//支付货币金额
    					$data['status'] = 'ready';//支付状态
    					$data['user_id'] = $uid;
    					$data['user_name'] = $account;
    					$data['pay_type'] = 'recharge';
    					$data['pay_app_id'] = 'alipay';
    					$data['pay_name'] = '支付宝';
    					$data['pay_from'] = 'APP';
    					$data['op_id'] = $uid; //操作员id
    					$data['op_name'] = $userName; //操作员名称
    					$data['bank'] = '预存款';//收款银行
    					$data['pay_account'] = null;//第三方支付账号
    					$data['currency'] = 'CNY';//币种
    					$data['ip'] = $_SERVER['REMOTE_ADDR'];//币种
    					$data['created_time'] = time();
    					$data['modified_time'] = time();
    					$boolData = $paymentsModel->add($data);
    					if($boolData){
    						$result['paymentid'] = $data['payment_id'];
    						$this->retSuccess($result,"充值订单生成成功");
    					}else{
    						$this->retError(0,'服务繁忙，请重新登录或稍后再试');
    					}
    				}else{
    					$this->retError(-4,'服务繁忙，请重新登录或稍后再试');
    				}
    			}else{
    				$this->retError(-3,'服务繁忙，请重新登录或稍后再试');
    			}
    		}else{
    			$this->retError(-2,'服务繁忙，请重新登录或稍后再试');
    		}
    	}else{
    		$this->retError(-1,'参数错误或服务繁忙');
    	}
    } 	
    
    
    //扣款成功后，更新ectools_payments表状态
    public function updatepay(){
    	$postData = I('post.');
    	$uid = $this->uid;
    	if(empty($postData)){
    		$this->retError('服务繁忙');
    	}else{
    		$paymentid = $postData['paymentid'];
    		if($paymentid){
    			$paymentsModel = M("ectools_payments");//支付表
    			$paymentsModel->startTrans();
    			$where['payment_id'] = $paymentid;
    			$where['user_id'] = $uid;
    			$findData = $paymentsModel->where($where)->find();
    			if ($findData){
    				//支付宝返回的数据示例
    				//partner=&quot;2088911943487400&quot;&amp;seller_id=&quot;joy@lishe.cn&quot;&amp;out_trade_no=&quot;20170323154944118141&quot;&amp;subject=&quot;礼舍积分充值&quot;&amp;body=&quot;礼舍积分充值&quot;&amp;total_fee=&quot;30.00&quot;&amp;service=&quot;mobile.securitypay.pay&quot;&amp;payment_type=&quot;1&quot;&amp;_input_charset=&quot;utf-8&quot;&amp;it_b_pay=&quot;30m&quot;&amp;show_url=&quot;m.alipay.com&quot;&amp;success=&quot;true&quot;&amp;sign_type=&quot;RSA&quot;&amp;sign=&quot;FdTXNftULt3E00P9uYfSDjA6RHUAA9ur19zMQ008w0wEbB78FR6DplxvSHif5IZmOt5F/zYYvbk2PnQficeS6/OXadAcdmCv2qWgphwLkiP/GvJoEBz4lnrEtYqd7RH5/5EDCzWlwA0QKzuX11XUc25ME/sj/dmcImiUwMlhDl8=&quot;
    				$alipayRetuenData_json = $postData['alipayRetuenData'];//支付成功返回的所有数据
    				$data1['status'] = 'succ';
    				$data1['payed_time'] = time();//支付完成时间
    				$data1['modified_time'] = time();//最后更新时间
    				$data1['memo'] = "积分充值";
    				$data1['result_memo'] = $alipayRetuenData_json;
    				//处理支付宝返回的数据，拿到订单编号（out_trade_no）
    				$str1 = str_replace("&quot;",'',$alipayRetuenData_json);
    				$str2 = str_replace("&amp",'',$str1);
    				$str3=explode(";", $str2);
    				$out_trade_no_string=explode("=", $str3[2]);
    				$out_trade_no = $out_trade_no_string[1];//支付宝返回的订单编号
    				$data1['trade_no'] =$out_trade_no;//支付宝返回的订单编号
    				$total_fee_string = explode("=", $str3[5]);
    				$total_fee = $total_fee_string[1];//支付宝返回的货币金额
    				$data1['cur_money'] =$total_fee;//支付宝返回的订单编号
    				$bool1 = $paymentsModel->where($where)->save($data1);
    				if($bool1){
    					$paymentsModel->commit();
    					$tradeNo = $out_trade_no;//支付宝返回的订单编号
    					$mobile = $this->account;
    					$addPoint = $findData['money']*100;
    					$yiqiyisheReturn_json=$this->inteRechApi($tradeNo,$mobile,$addPoint);
    					$yiqiyisheReturn_Obj = json_decode($yiqiyisheReturn_json);
    					$result = $yiqiyisheReturn_Obj->result;
    					if($result=='100'){
    						//一企一舍回调成功
    						$data2['ls_trade_no'] = $yiqiyisheReturn_Obj->data->info->transno;
    						$data2['modified_time'] = time();//最后更新时间
    						$bool2 = $paymentsModel->where($where)->save($data2);
    						if($bool2){
    							//同步积分
    							$dataInfo = $yiqiyisheReturn_Obj->data->info;
    							$userPoints = $dataInfo->userPointsList[0]->remainScore;//string
    							$modelDeposit = M('sysuser_user_deposit'); //用户积分表//->field('balance,commonAmount,limitAmount,comName')
    							//->where('user_id='.$uid)
    							$wheres['user_id'] = $uid;
    							$user_Point['balance'] = $userPoints;
    							$user_Point['commonAmount'] = $userPoints;
    							$res = $modelDeposit->where($wheres)->save($user_Point);
    							if(res){
    								$parameter['status'] = 1;
    								$this->retSuccess($parameter,"积分充值成功");
    							}else{
    								$parameter['status'] = 1;
    								$this->retSuccess($parameter,"积分充值成功");
    							}
    						}else{
    							$parameter['status'] = 0;
    							$this->retSuccess($parameter,"积分充值成功");
    						}
    					}else{
    						$this->retError(-4,'一企一舍回调失败');
    					}
    				}else{
    					$paymentsModel->rollback();
    					$this->retError(-3,'充值订单更新失败');
    				}
    			}else{
    				$this->retError(-2,'没有这单充值记录,非法操作');
    			}
    		}else{
    			$this->retError(-1,'参数缺失');
    		}
    	}
    }
    
    
    
    
    //积分充值
    protected function inteRechApi($tradeNo,$mobile,$addPoint){
    	$APP = 'APP';
    	$md5String = 'orderno='.$tradeNo.'&phoneNum='.$mobile.'&pointsAmount='.$addPoint.'&pointsType=1'.'&terminalType='.$APP.C('API_KEY');
    	$sign=md5($md5String);
    	$url=C('API').'mallPoints/rechargeNew';
    	$data['phoneNum'] = $mobile;
    	$data['pointsAmount'] = $addPoint;
    	$data['orderno'] = $tradeNo;
    	$data['pointsType'] = 1;
    	$data['terminalType'] = $APP;
    	$data['sign'] = $sign;
    	$json=$this->requestPost($url,$data);
//     	//模拟json返回成功案例；
    	//$json = '{"result":100,"errcode":0,"msg":"success","data":{"info":{"orderno":"2017021021001004570294180108","userPointsList":[{"id":2725,"userId":123452175,"pointTypeId":1,"totalScore":500000.00,"usedScore":0.00,"remainScore":500000.00,"freezeScore":0.00,"version":2,"pointName":"商城通用积分","pointType":1}],"transno":"1486726114409"}}}';
    	return $json;
    }
    
    //
    
    //模拟提交
    public function requestPost($url='', $data=array()) {
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
	
	//接口返回错误信息
	public function retError($errCode=1,$msg='操作失败'){
		$ret=array(
				'result'=>100,
				'errcode'=>$errCode,
				'msg'=>$msg
		);
		echo json_encode($ret);
		exit;
	}
	
	//接口返回结果
	public function retSuccess($data=array(),$msg='操作成功'){
		$ret=array(
				'result'=>100,
				'errcode'=>0,
				'msg'=>$msg,
				'data'=>$data
		);
		echo json_encode($ret);
		exit;
	}
	
}

