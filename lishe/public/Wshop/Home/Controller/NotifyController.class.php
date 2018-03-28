<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[微信支付回调控制器];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Home\Controller];
 * +----------------------------------------------------------------------
 * |@Name:			[NotifyController.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://ummai.com) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-7-12 12:06
 * +----------------------------------------------------------------------
 * |@Email:		<Angelljoy@sina.com>		@OverTime:	2016
 * +----------------------------------------------------------------------
 *  */
namespace Home\Controller;
use React\Promise\reject;

use Think\Controller;
class NotifyController extends  Controller{
	//切记：
	//本控制器不能加载构造函数，不能继承有登录验证功能的控制器，否则微信服务器回调不会成功
	//本控制器里的方法不能迁移到其它控制器，否则微信服务器也回调不成功，本人亲测
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name 微信支付成功后的通知地址
	 * @method 传参请求
	 * @param xml $xml
	 * @return array
	 * @author lihongqiang	2017-07
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */
	public function wxtestNotify(){
		$postXml  = $GLOBALS["HTTP_RAW_POST_DATA"];
		//$xmlData = simplexml_load_string($postXml,'SimpleXMLElement', LIBXML_NOCDATA);
		$notifyArray = $this->FromXml($postXml);
		$json = json_encode($notifyArray);
		if($notifyArray['result_code']=='SUCCESS'){
			//$Service = new DrycleanController();////不支持
			S(array('type'=>'','prefix'=>'lishe:','expire'=>''));//缓存初始化
			S($notifyArray['out_trade_no'],$json,2592000);//缓存30天
			$bool = $this->CheckNotify($notifyArray);
			if($bool){
				$returnWx_Xml['return_code'] = 'SUCCESS';
				$returnWx_Xml['return_msg'] = 'OK';
				$this->ajaxReturn($returnWx_Xml,'XML');//同步返回给微信
			}else{
				$returnWx_Xml['return_code'] = 'ERROR';
				$returnWx_Xml['return_msg'] = 'NO';
				$this->ajaxReturn($returnWx_Xml,'XML');//同步返回给微信
			}
		}else{
			$returnWx_Xml['return_code'] = 'ERROR';
			$returnWx_Xml['return_msg'] = 'NO';
			$this->ajaxReturn($returnWx_Xml,'XML');//同步返回给微信
		}
	}
	

	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name 将xml转为array
	 * @method 传参请求,禁止引用外部xml实体
	 * @param xml $xml
	 * @return array
	 * @author lihongqiang	2017-07
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */
	private function FromXml($xml){
		libxml_disable_entity_loader(true);
		$arrayXml = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $arrayXml;
	}
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name 格式化参数格式化成url参数
	 * @method 传参请求
	 * @param array $data
	 * @return string
	 * @author lihongqiang	2017-07
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */
	private function ToUrlParams($data){
		$buff = "";
		foreach ($data as $k => $v)
		{
			if($k != "sign" && $v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
	
		$buff = trim($buff, "&");
		return $buff;
	}


	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name 校验微信公众号收款金额
	 * @method 传参请求
	 * @param array $notifyArray
	 * @return boolean
	 * @author lihongqiang 2017-07
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */
	private function CheckNotify($notifyArray){
		$where1['out_trade_no'] = $notifyArray['out_trade_no'];
		$wechatModel = M("wechat_public_crowd_proceeds");//微信公众号收款表
		$wechatModel->startTrans();//启动事务
		$findPay1 = $wechatModel->where($where1)->find();
		if($findPay1){
			if($findPay1['status']=='SUCCESS'){
				$wechatModel->commit();
				return true;//已经处理
			}else{
				//开始MD5签名校验，防止被三方人为二次篡改
				$w_sign = array();           //参加验签签名的参数数组
				$w_sign['appid']             = $notifyArray['appid'];
				$w_sign['bank_type']         = $notifyArray['bank_type'];
				$w_sign['cash_fee']          = $notifyArray['cash_fee'];
				$w_sign['device_info']       = $notifyArray['device_info'];
				$w_sign['fee_type']          = $notifyArray['fee_type'];
				$w_sign['is_subscribe']      = $notifyArray['is_subscribe'];
				$w_sign['mch_id']            = $notifyArray['mch_id'];
				$w_sign['nonce_str']         = $notifyArray['nonce_str'];
				$w_sign['openid']            = $notifyArray['openid'];
				$w_sign['out_trade_no']      = $notifyArray['out_trade_no'];
				$w_sign['result_code']       = $notifyArray['result_code'];
				$w_sign['return_code']       = $notifyArray['return_code'];
				$w_sign['time_end']          = $notifyArray['time_end'];
				$w_sign['total_fee']         = $notifyArray['total_fee'];
				$w_sign['trade_type']        = $notifyArray['trade_type'];
				$w_sign['transaction_id']    = $notifyArray['transaction_id'];
				ksort($w_sign);//把传递过来的数组排序
				$sign_1 = $this->ToUrlParams($w_sign);
				$sign_2 = $sign_1 . "&key=".C('AppKey');
				//签名步骤三：MD5加密
				$sign = md5($sign_2);
				//签名步骤四：所有字符转为大写
				$sign_result = strtoupper($sign);
				if($notifyArray['sign']==$sign_result){
					//签名验证通过
					//开始第一步：更新公众号手续费收款表
					$json = json_encode($notifyArray);
					$w_save['practical_total_fee'] = $notifyArray['cash_fee'];
					$w_save['status'] = $notifyArray['result_code'];
					$w_save['pay_time'] = date('Y-m-d H:i:s');
					$w_save['time_end'] = $notifyArray['time_end'];
					$w_save['transaction_id'] = $notifyArray['transaction_id'];
					$w_save['checkout'] = "Y";
					$w_save['annotation'] = "支付成功";
					$w_save['callback_json'] = $json;
					$boolData = $wechatModel->where($where1)->save($w_save);
					//开始第二步：更新ectools_payments表的（条手续费记录）的相关参数
					$paymentsModel = M("ectools_payments");//支付表
					$p_save['cur_money'] = floatval($notifyArray['cash_fee']/100);//支付金额 ;
					$p_save['payed_cash'] =floatval($notifyArray['cash_fee']/100);//已经支付的现金
					$p_save['payed_time'] = time();//支付时间
					$p_save['status'] = 'succ';//支付状态
					$p_save['modified_time'] = time();//最后更新时间
					$p_save['result_memo'] = $json;
					$p_save['trade_no'] = $notifyArray['transaction_id'];//微信公众平台支付交易编号
					$where2['payment_id'] = $notifyArray['out_trade_no'];
					$boolPay  = $paymentsModel->where($where2)->save($p_save);
					//开始第三步：生成兑换订单
					$paymentData = $this->createPay($findPay1);
					//开始第四步：调起正章干洗券核销接口去销毁干洗券
					$barcodeArray = array();
					$dryclean_idArray = explode(',', $findPay1['dryclean_id']);
					$ModleObj = M('zzgx_barcode');
					for ($i=0;$i<count($dryclean_idArray);$i++){
						$whereb['id'] = $dryclean_idArray[$i];
						$findData = $ModleObj->where($whereb)->find();
						array_push($barcodeArray, $findData['barcode']);
					}
					$boolDestroy = $this->destroy($barcodeArray,$paymentData['created_time']);//传条码数组//核销电子券
					//开始第五步：更新条码表状态
					$boolBarcode = $this->updateBarcode($barcodeArray,$paymentData['payment_id']);
					//开始第六步：用兑换单号请求一企一舍接口，兑换积分
					$phoneNum = $findPay1['user_mobile'];
					$pointsAmount = $findPay1['dryclean_money']*100;
					$yiqiyisheReturn_json = $this->conversion($phoneNum, $pointsAmount, $paymentData['payment_id']);
					$yiqiyisheReturn_Obj = json_decode($yiqiyisheReturn_json,true);
					$result = $yiqiyisheReturn_Obj['result'];
					if($result=='100'){
						//开始第七步：更新ectools_payments表的（兑换积分记录）的相关参数
						$data2['status'] = 'succ';
						$data2['cur_money'] = $findPay1['dryclean_money'];
						$data2['payed_time'] = time();//兑换完成时间
						$data2['ls_trade_no'] = $yiqiyisheReturn_Obj['data']['info']['transno'];//$yiqiyisheReturn_Obj->data->info->transno;
						$data2['modified_time'] = time();//最后更新时间
						$data2['result_memo'] = $yiqiyisheReturn_json;
						$wherep['payment_id'] = $paymentData['payment_id'];
						$bool2 = $paymentsModel->where($wherep)->save($data2);
						if($bool2){
							$modelPointDet = M('sysuser_user_deposit_detail');
							$balanceDetail = $yiqiyisheReturn_Obj['data']['info']['userPointsList'];//$yiqiyisheReturn_Obj->data->info->userPointsList;
							for ($i=0;$i<count($balanceDetail);$i++){
								$data3['user_id'] = $findPay1['user_id'];
								$data3['deposit'] = $balanceDetail[$i]['remainScore']/100;
								$data3['balance'] = $balanceDetail[$i]['remainScore'];
								$data3['pointsChannel'] = $balanceDetail[$i]['pointsChannel'];
								$data3['pointName'] = $balanceDetail[$i]['pointName'];
								$data3['pointTypeId'] = $balanceDetail[$i]['pointTypeId'];
								$map['user_id'] = $findPay1['user_id'];
								$map['pointTypeId'] = $balanceDetail[$i]['pointTypeId'];
								$isExist = $modelPointDet->where($map)->find();
								if($isExist){
									//更新该种积分
									$Pointbool = $modelPointDet->where($map)->save($data3);
								}else{
									//添加该种积分
									$Pointbool = $modelPointDet->add($data3);
								}
							}
							if($Pointbool){
								$boolConversion = true;
							}else{
								$boolConversion = false;
							}
						}else{
							$boolConversion = false;
						}
					}else{
						$boolConversion = false;
					}
					
					if($boolData && $boolPay && $boolDestroy && $paymentData && $boolBarcode && $boolConversion){
						//核销
						$wechatModel->commit();
						return true;
					}else{
						$wechatModel->rollback();
						return false;
					}
				}else{
					$wechatModel->rollback();
					return false;
				}
			}
		}else{
			$wechatModel->rollback();
			return false;
		}
	}
	
	
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name 干洗券销毁
	 * @method 传参请求
	 * @param array $barcodeArray
	 * @return boolean
	 * @author lihongqiang
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */
	private function destroy($barcodeArray,$boolpayment_time){
		$URL = 'http://10.116.136.185:8080/lshe.framework.aoserver/api/zzgx/consum';//核销
		$billno = $boolpayment_time;
		$consum = 0;
		for ($i=0;$i<count($barcodeArray);$i++){
			$keyid = $barcodeArray[$i];
			$zzgxLsApiUrl = $URL.'?keyid='.$keyid.'&billno='.$billno;
			$jsonData = file_get_contents($zzgxLsApiUrl);
			$ut8_jsonData = iconv("gb2312", "utf-8//IGNORE",$jsonData);//把改变gb2312转成utf8
			$barcodeData = json_decode($ut8_jsonData,true);
			if($barcodeData['code']!==100){
				//记录核销失败的日志
				$this->consumError($barcodeData);
				$consum = 0;
				continue;//结束本次循环
			}else{
				if($barcodeData['data']['RESULT']==0){
					$consum = 1;
				}else{
					$consum = 0;
					//记录核销失败的日志
					$this->consumError($barcodeData);
					//$myfile = fopen("/home/test/dryclean.txt", "w");
					continue;//结束本次循环
				}
			}
		}
		////正常有效的条码：
		////查询返回json示例//{"code":100,"data":{"STATUS":"1","DMDM":"AA","DMJC":"","ORDID":"15284641","XFRQ":"","CORP":"深圳市供电局","YXRQ":"2019-12-31","DZQMC":"2019增16元券","KEYID":"KITL82NZCCU2FYS","RESULT":"0","PMJE":"25","XSJE":"16"},"errCode":0,"msg":"操作成功"}
		////核销返回json示例//{"code":100,"data":{"STATUS":"1","DMDM":"AA","DMJC":"","ORDID":"15284641","XFRQ":"","CORP":"深圳市供电局","YXRQ":"2019-12-31","DZQMC":"2019增16元券","KEYID":"KITL82NZCCU2FYS","RESULT":"0","PMJE":"25","XSJE":"16"},"errCode":0,"msg":"操作成功"}
		////已核销的再核销返回//{"code":100,"data":{"STATUS":"4","DMDM":"AA","DMJC":"","ORDID":"15284641","XFRQ":"","CORP":"深圳市供电局","YXRQ":"2019-12-31","DZQMC":"2019增16元券","KEYID":"KITL82NZCCU2FYS","RESULT":"8","PMJE":"25","XSJE":"16"},"errCode":0,"msg":"操作成功"}
		
		if($consum==1){
			return true;
		}else{
			return false;
		}
	}
	
	private function consumError($logData){
		$ApplogModel = M('app_receive_data');
		$log['postData'] = $logData;
		$log['createTime'] = date('Y-m-d H:i:s');
		$ApplogModel->add($log);
	}
	
	
	
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name updateBarcode 更新条码表的兑换状态等信息
     * @method 传参请求
	 * @param array1 $barcodeArray 条码编号的数组
	 * @param string $payment_id 兑换积分的单号
	 * @return boolean
	 * @author lihongqiang
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */
	private function updateBarcode($barcodeArray,$payment_id){
		if(empty($barcodeArray)){
			return false;
		}else{
			$barcodeModel = M('zzgx_barcode');
			$data['status'] = 0;
			$data['failure_cause'] = '兑换失效';//失效原因
			$data['is_trigger'] = 1;
			$data['conversion'] = 'SUCCESS';//兑换结果
			$data['payment_id'] = $payment_id;//兑换的payment_id
			$data['conversion_time'] = date('Y-m-d H:i:s');
			for ($i=0;$i<count($barcodeArray);$i++){
				$wherec['barcode'] = $barcodeArray[$i];
				$bool = $barcodeModel->where($wherec)->save($data);
			}
			if($bool){
				return true;
			}else{
				return false;
			}
		}
	}
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name conversion 更新条码表的兑换状态等信息
	 * @method 传参请求
	 * @param array1 $barcodeArray 条码编号的数组
	 * @param string $payment_id 兑换积分的单号
	 * @return boolean
	 * @author lihongqiang
	 */
	private function conversion($phoneNum, $pointsAmount, $boolpayment){
		$orderno = $boolpayment;
		$signArray['phoneNum'] = $phoneNum;
		$signArray['pointsAmount'] = $pointsAmount;
		$signArray['orderno'] = $orderno;
		$signArray['pointsChannel'] = 'zzgx';
		ksort($signArray);
		$singString = $this->ToUrlParams($signArray);
		$singString_new = $singString.C('API_KEY');//'lishe_md5_key_56e057f20f883e';
		$sign=md5($singString_new);//md5签名
		//$API = C('API');//API生产接口地址
		$API = 'http://120.76.159.44:8080/lshe.framework.protocol.http/api/';//API测试接口地址
		$url=$API.'mallPoints/exchange';/////测试环境
		$data['phoneNum'] = $phoneNum;
		$data['pointsAmount'] = $pointsAmount;
		$data['orderno'] = $orderno;
		$data['pointsChannel'] = 'zzgx';
		$data['sign'] = $sign;
		$json=$this->requestPost($url,$data);
		////返回成功的json示例
		////$json = '{"result":100,"errcode":0,"msg":"success","data":{"info":{"amount":1,"orderno":"20170720184329028952306039","userPointsList":[{"id":2725,"userId":123452175,"pointTypeId":1,"totalScore":500005.00,"usedScore":396900.00,"remainScore":103105.00,"freezeScore":0.00,"version":14,"pointName":"商城通用积分","pointType":1,"pointChannel":"www"},{"id":3222,"userId":123452175,"pointTypeId":5,"totalScore":14.00,"usedScore":0.00,"remainScore":14.00,"freezeScore":0.00,"version":14,"pointName":"正章干洗积分","pointType":0,"pointChannel":"zzgx"}],"transno":"1500609197442"}}}';
		return $json;
	}
	
	public function zjzzgxjf(){
		$phoneNum = $_GET['mobile'];
		$points = $_GET['points'];
		$boolpayment = '2017072718561210343'.rand(100, 999);
		if(empty($phoneNum)){
			header("Location:http://test.lishe.cn/wshop.php/Notify/zjzzgxjf?points=1&mobile=");
		}else{
			$pointsAmount = $points;
			$orderno = $boolpayment;
			$signArray['phoneNum'] = $phoneNum;
			$signArray['pointsAmount'] = $pointsAmount;
			$signArray['orderno'] = $orderno;
			$signArray['pointsChannel'] = 'zzgx';
			ksort($signArray);
			$singString = $this->ToUrlParams($signArray);
			$singString_new = $singString.C('API_KEY');//'lishe_md5_key_56e057f20f883e';
			$sign=md5($singString_new);//md5签名
			//$API = C('API');//API生产接口地址
			$API = 'http://120.76.159.44:8080/lshe.framework.protocol.http/api/';//API测试接口地址
			$url=$API.'mallPoints/exchange';/////测试环境
			$data['phoneNum'] = $phoneNum;
			$data['pointsAmount'] = $pointsAmount;
			$data['orderno'] = $orderno;
			$data['pointsChannel'] = 'zzgx';
			$data['sign'] = $sign;
			$yiqiyisheReturn_json=$this->requestPost($url,$data);
			$yiqiyisheReturn_Obj = json_decode($yiqiyisheReturn_json,true);
			$result = $yiqiyisheReturn_Obj['result'];
			if($result=='100'){
				$wherem['mobile'] = $phoneNum;
				$userAccountModel = M('sysuser_account');
				$findUser = $userAccountModel->where($wherem)->find();
				$modelPointDet = M('sysuser_user_deposit_detail');
				$balanceDetail = $yiqiyisheReturn_Obj['data']['info']['userPointsList'];//$yiqiyisheReturn_Obj->data->info->userPointsList;
				for ($i=0;$i<count($balanceDetail);$i++){
					$data3['user_id'] = $findUser['user_id'];
					$data3['deposit'] = $balanceDetail[$i]['remainScore']/100;
					$data3['balance'] = $balanceDetail[$i]['remainScore'];
					$data3['pointsChannel'] = $balanceDetail[$i]['pointsChannel'];
					$data3['pointName'] = $balanceDetail[$i]['pointName'];
					$data3['pointTypeId'] = $balanceDetail[$i]['pointTypeId'];
					$map['user_id'] = $findUser['user_id'];
					$map['pointTypeId'] = $balanceDetail[$i]['pointTypeId'];
					$isExist = $modelPointDet->where($map)->find();
					if($isExist){
						//更新该种积分
						$Pointbool = $modelPointDet->where($map)->save($data3);
					}else{
						//添加该种积分
						$Pointbool = $modelPointDet->add($data3);
					}
				}
				if($Pointbool){
					var_dump($yiqiyisheReturn_json) ;
					echo '<br/><br/><br/><br/>————————————————————————————————————————<br/>';
					echo $phoneNum.'兑换正章干洗积分成功；';
					echo '<br/>————————————————————————————————————————<br/>';
					echo '积分金额：'.$pointsAmount;
					echo '<br/>————————————————————————————————————————<br/>';
					echo '兑换单号：'.$boolpayment;
					echo '<br/>————————————————————————————————————————<br/>';
				}else{
					var_dump($yiqiyisheReturn_json) ;
					echo '<br/><br/><br/><br/><br/>';
					echo '兑换失败';
				}
				
			}else{
				var_dump($yiqiyisheReturn_Obj) ;
				echo '<br/><br/><br/><br/><br/>';
				echo '兑换失败';
			}
		}
		
		
		
		////返回成功的json示例
		////$json = '{"result":100,"errcode":0,"msg":"success","data":{"info":{"amount":1,"orderno":"20170720184329028952306039","userPointsList":[{"id":2725,"userId":123452175,"pointTypeId":1,"totalScore":500005.00,"usedScore":396900.00,"remainScore":103105.00,"freezeScore":0.00,"version":14,"pointName":"商城通用积分","pointType":1,"pointChannel":"www"},{"id":3222,"userId":123452175,"pointTypeId":5,"totalScore":14.00,"usedScore":0.00,"remainScore":14.00,"freezeScore":0.00,"version":14,"pointName":"正章干洗积分","pointType":0,"pointChannel":"zzgx"}],"transno":"1500609197442"}}}';
		////return $json;
	}

	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name requestPost 模拟POST提交
	 * @method 传参请求
	 * @param string	$url	URL地址 
	 * @param array	$data 数组
	 * @author lihongqiang 
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */
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
	

	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name 生成干洗券兑换时的充值单，拿单号调一企一舍的接口兑换
	 * @author lihongqiang
	 * @param string $dryclean_id
	 * @return number|boolean
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */
	private function createPay($findPay1){
		$uid = $findPay1['user_id'];
		$account = $findPay1['user_mobile'];
		$userName= $findPay1['user_name'];
		$total_price = $findPay1['dryclean_money'];
    	//插入支付表
    	$paymentsModel = M("ectools_payments");//支付表
    	$data['payment_id'] = date(YmdHis).$uid.'1';//支付单号
    	$data['money'] = floatval($total_price);//floatval($money/100);//需要支付的金额
    	$data['cur_money'] = 0;//支付货币金额
    	$data['cash_fee'] =  0;//需要支付的现金
    	$data['point_fee'] = 0;//需要支付的积分
    	$data['payed_cash'] = 0;//已经支付的现金
    	$data['payed_point'] = 0;//已经支付的积分
    	$data['status'] = 'ready';//支付状态
    	$data['user_id'] = $uid;
    	$data['user_name'] = $account;
    	$data['pay_type'] = 'zzgxdh';
    	$data['pay_app_id'] = 'wxzzgx';
    	$data['pay_name'] = '微信公众号';
    	$data['pay_from'] = 'wechat';
    	$data['op_id'] = $uid; //操作员id
    	$data['op_name'] = $userName; //操作员名称
    	$data['memo'] = "正章干洗券兑换-增加积分";
    	$data['bank'] = '预存款';//收款银行
    	$data['pay_account'] = null;//第三方支付账号
    	$data['currency'] = 'CNY';//币种
    	$data['ip'] = $_SERVER['REMOTE_ADDR'];//IP
    	$data['created_time'] = time();
    	$data['modified_time'] = time();
    	$data['point_pay_type'] = 'zzgx';
    	$boolData = $paymentsModel->add($data);
    	if($boolData){
    		return $data;//兑换积分的订单生成成功
    	}else{
    		return false;//服务繁忙，请重新登录或稍后再试
    	}	
    }
    
    
    
    
    
    
    
    
    public function testdestroy(){
    
    	$searchURL = 'http://10.116.136.185:8080/lshe.framework.aoserver/api/zzgx/search';//查询
    	$consumURL = 'http://10.116.136.185:8080/lshe.framework.aoserver/api/zzgx/consum';//核销
    
    
    	$keyid1 = 'TL9RFYGYGT34P1WD';
    	$billno1 = '123456';
    
    	$zzgxLsApiUrl1 = $consumURL.'?keyid='.$keyid1.'&billno='.$billno1;
    	$jsonData1 = file_get_contents($zzgxLsApiUrl1);
    	$ut8_jsonData1 = iconv("gb2312", "utf-8//IGNORE",$jsonData1);//把改变gb2312转成utf8
    	var_dump($jsonData1);
    	echo '<br/><br/><br/><br/><br/>';
    	var_dump($ut8_jsonData1);
    	//json示例//{"code":100,"data":{"STATUS":"4","DMDM":"VU","DMJC":"礼舍网","ORDID":"14777223","XFRQ":"","CORP":"中行龙华支行","YXRQ":"2018-12-31","DZQMC":"2018增14元券","KEYID":"L9RFYGYGT34P1WD","RESULT":"0","PMJE":"25","XSJE":"14"},"errCode":0,"msg":"操作成功"}
    	echo '<br/><br/><br/><br/><br/>';
    
    
    
    	$keyid = 'TKITL82NZCCU2FYS';
    	$billno = '1501139305';
    
    	$zzgxLsApiUrl = $consumURL.'?keyid='.$keyid.'&billno='.$billno;
    	$jsonData = file_get_contents($zzgxLsApiUrl);
    	$ut8_jsonData = iconv("gb2312", "utf-8//IGNORE",$jsonData);//把改变gb2312转成utf8
    	var_dump($jsonData);
    	echo '<br/><br/><br/><br/><br/>';
    	var_dump($ut8_jsonData);
    	//查询返回json示例//{"code":100,"data":{"STATUS":"1","DMDM":"AA","DMJC":"","ORDID":"15284641","XFRQ":"","CORP":"深圳市供电局","YXRQ":"2019-12-31","DZQMC":"2019增16元券","KEYID":"KITL82NZCCU2FYS","RESULT":"0","PMJE":"25","XSJE":"16"},"errCode":0,"msg":"操作成功"}
    	//核销返回json示例//{"code":100,"data":{"STATUS":"1","DMDM":"AA","DMJC":"","ORDID":"15284641","XFRQ":"","CORP":"深圳市供电局","YXRQ":"2019-12-31","DZQMC":"2019增16元券","KEYID":"KITL82NZCCU2FYS","RESULT":"245","PMJE":"25","XSJE":"16"},"errCode":0,"msg":"操作成功"}
    	//核销返回json示例//{"code":100,"data":{"STATUS":"1","DMDM":"AA","DMJC":"","ORDID":"15284641","XFRQ":"","CORP":"深圳市供电局","YXRQ":"2019-12-31","DZQMC":"2019增16元券","KEYID":"KITL82NZCCU2FYS","RESULT":"0","PMJE":"25","XSJE":"16"},"errCode":0,"msg":"操作成功"}
    	//已核销的再核销返回//{"code":100,"data":{"STATUS":"1","DMDM":"AA","DMJC":"","ORDID":"15284641","XFRQ":"","CORP":"深圳市供电局","YXRQ":"2019-12-31","DZQMC":"2019增16元券","KEYID":"KITL82NZCCU2FYS","RESULT":"245","PMJE":"25","XSJE":"16"},"errCode":0,"msg":"操作成功"}
    	//已核销的再核销返回//{"code":100,"data":{"STATUS":"4","DMDM":"AA","DMJC":"","ORDID":"15284641","XFRQ":"","CORP":"深圳市供电局","YXRQ":"2019-12-31","DZQMC":"2019增16元券","KEYID":"KITL82NZCCU2FYS","RESULT":"8","PMJE":"25","XSJE":"16"},"errCode":0,"msg":"操作成功"}
    	echo '<br/><br/><br/><br/><br/>';
    	exit;
    
    	$barcodeArray = '';
    	return true;
    	$billno = $boolpayment;
    	$consum = 0;
    	for ($i=0;$i<count($barcodeArray);$i++){
    		$keyid = $barcodeArray[$i];
    		$zzgxLsApiUrl = $URL.'?keyid='.$keyid.'&billno='.$billno;
    		$jsonData = file_get_contents($zzgxLsApiUrl);
    		$ut8_jsonData = iconv("gb2312", "utf-8//IGNORE",$jsonData);//把改变gb2312转成utf8
    		$barcodeData = json_decode($ut8_jsonData,true);
    		if($barcodeData['data']['RESULT']!=0){
    			$consum = 0;
    			//记录核销失败的日志
    			//$myfile = fopen("/home/test/testfile.txt", "w");
    			continue;//结束本次循环
    		}else{
    			$consum = 1;
    		}
    		//var_dump($barcodeData);exit;
    	}
    	if($consum==1){
    		return true;
    	}else{
    		return false;
    	}
    }


}