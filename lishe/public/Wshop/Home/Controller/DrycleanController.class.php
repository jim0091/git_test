<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[正章干洗];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Businesscircle/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[DrycleanController.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-06-26 16:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2016
 * +----------------------------------------------------------------------
 *  */
namespace Home\Controller;
use Think\Controller;
class DrycleanController extends  CommonController{
	public function __construct(){
		parent::__construct();
		if(empty($this->uid)){
			redirect(__APP__."/Login/login/index");
		}
	}
	
	/**
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:	正章干洗启动扫码页
	 * @method:	POST
	 * @param null
	 * @author Angelljoy@qq.com	lihongqiang	2017-06-28 
	 * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 */	//index	BEGIN
    public function index(){
    	S(array('type'=>'','prefix'=>'lishe_','expire'=>''));//缓存初始化
    	$string = md5($this->account.$this->uid);
    	S($string,null);//初始化的时候清除之前扫描的干洗券
    	$AppId = C('AppId');//访问配置文件的appId
    	$state = md5($string);
    	if(IS_GET){
    		$getData = I('get.');
    		//获得code,用code去换取openid
    		$code = $getData['code'];
    		if(!empty($code)){
    			S('wx_user_code_'.$this->account,$code,300);//缓存code，只能使用一次，5分钟未被使用自动过期。
    			$bool = $this->getOAuthAccessToken();
    		}
    		$openid = S('wx_user_openid_'.$this->account);
    		if(empty($openid)||$openid==false){
    			//请求新的code
    			$Protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    			$link = $Protocol. $_SERVER['HTTP_HOST'].__SELF__;
    			$redirect_uri = urlencode($link);
    			$getCodeUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$AppId.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_base&state='.$state;
    			redirect($getCodeUrl);
    		}else{
    			$WxjsSdkConfigData = $this->getWxjsSdkConfig();
    			$this->assign('WxjsSdkConfigData',$WxjsSdkConfigData);
    			$this->display();
    		}
    	}else{
    		exit;
    	}
    }
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	通过code换取OAuth2 使用的access_token,refresh_token,openid
     * @method:	GET
     * @param null
     * @author Angelljoy@qq.com	lihongqiang	2017-07-06 
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//convert	BEGIN
    public function getOAuthAccessToken(){
    	$AppId = C('AppId');//访问配置文件的appId
    	$AppSecret = C('AppSecret');//访问配置文件的appSecret
    	S(array('type'=>'','prefix'=>'lishe_','expire'=>''));//缓存初始化
    	$wx_user_code = S('wx_user_code_'.$this->account);//读取缓存code
    	$URL = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$AppId.'&secret='.$AppSecret.'&code='.$wx_user_code.'&grant_type=authorization_code';
    	$WxUserInfo = file_get_contents($URL);
    	S('wx_user_code_'.$this->account,null);//code使命完成，清除
    	$WxUserInfo_Data = json_decode($WxUserInfo);
    	$openid = $WxUserInfo_Data->openid;
    	S('wx_user_openid_'.$this->account,$openid);//不设置时间表示缓存永久有效；
    	$wx_access_token = $WxUserInfo_Data->access_token;
    	S('wx_user_access_token_'.$this->account,$wx_access_token,7198);
    	$wx_refresh_token = $WxUserInfo_Data->refresh_token;
    	S('wx_user_refresh_token_'.$this->account,$wx_refresh_token,2592000);//30天有效期，可以用来刷新
    	return true;
    }
        
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name:	正章干洗扫码后的干洗卷兑换页
     * @method:	GET
     * @param null
     * @author Angelljoy@qq.com	lihongqiang	2017-06-28 
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */	//convert	BEGIN
    public function convert(){
    	$WxjsSdkConfigData = $this->getWxjsSdkConfig();
    	$this->assign('WxjsSdkConfigData',$WxjsSdkConfigData);//js-sdk配置
    	S(array('type'=>'','prefix'=>'lishe_','expire'=>''));//缓存初始化
    	$string = md5($this->account.$this->uid);
    	$cacheValue = S($string);
		if($cacheValue){
			$dryclean_idArray = explode(',', $cacheValue);
			$ModleObj = M('zzgx_barcode');
			$dryclean = array();
			for ($i=0;$i<count($dryclean_idArray);$i++){
				$dryclean_id = $dryclean_idArray[$i];
				$where['id'] = $dryclean_id;
				$findData = $ModleObj->where($where)->find();
				$findData['price'] = floatval($findData['price']);
				$findData['integral'] = $findData['price']*100;
				$num = $findData['price']*0.09;
				$findData['poundage'] = sprintf("%.2f", $num);
				array_push($dryclean, $findData);
			}
			$this->assign('dryclean',$dryclean);
		}
    	$this->display();
    }	//convert	END
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name 获取微信js-sdk配置参数
     * @method 传参请求
     * @param	当前执行程序的完整的URL
     * @author Angelljoy@qq.com	lihongqiang	2017-06-28
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    public function getWxjsSdkConfig(){
    	$AppId = C('AppId');//访问配置文件的appId
    	$AppSecret = C('AppSecret');//访问配置文件的appSecret
    	$nonceStr = $this->getRandChar(16);//签名参数1:随机字符串
    	S(array('type'=>'','prefix'=>'lishe_','expire'=>'7200'));// 缓存初始化
    	$jsapi_ticket = S('jsapi_ticket');//签名参数2:读取缓存的jsapi_ticket，如果缓存的jsapi_ticket存在，说明是在有效期内，可以直接用于签名操作
    	if(empty($jsapi_ticket)||$jsapi_ticket==false){//如果缓存的jsapi_ticket不存在，说明jsapi_ticket过期了,则用access_token重新获取新的jsapi_ticket
    		$accessToken = S('jsapi_access_token');//读取缓存里的access_token
    		if(empty($accessToken)||$accessToken==false){//如果缓存里的access_token不存在
    			$accessToken = $this->getAccessToken();//请求一个新的access_token
    		}
    		$jsapi_ticketInfo =  $this->getTicket($accessToken);//用access_token去获取ticket
    		if($jsapi_ticketInfo['errcode']!=0){//说明accessToken是一个失效的accessToken或其它原因
    			$accessToken = $this->getAccessToken();//重新获取新的access_token
    			$jsapi_ticketInfo =  $this->getTicket($accessToken);//重新用新的access_token去获取新的ticket
    		}
    		$jsapi_ticket = $jsapi_ticketInfo['ticket'];//赋值
    	}
    	$timestamp = time();//签名参数3:时间戳
    	$Protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    	$actionUrl = $Protocol. $_SERVER['HTTP_HOST'].__SELF__;////签名参数4:当前网页的URL，不包含#及其后面部分
    	//1.对所有待签名参数按照字段名的ASCII 码从小到大排序（字典序）后，使用URL键值对的格式（即key1=value1&key2=value2…）拼接成字符串signString
    	$signString =stripcslashes('jsapi_ticket='.$jsapi_ticket.'&noncestr='.$nonceStr.'&timestamp='.$timestamp.'&url='.$actionUrl);
    	//$this->assign('signString',$signString);
    	//2.对$signString进行sha1签名，得到signature：
    	//jsapi_ticket=&noncestr=9EEqQz9cFxqvTGhlIOMx×tamp=1498633557&url=http://www.lishe.cn/wshop.php/Dryclean/index
    	$signature = sha1($signString);//sha1签名操作
    
    	$configData['AppId'] = $AppId;
    	$configData['AppSecret'] = $AppSecret;
    	$configData['accessToken'] = $accessToken;
    	$configData['timestamp'] = $timestamp;
    	$configData['nonceStr'] = $nonceStr;
    	$configData['signString'] = $signString;
    	$configData['signature'] = $signature;
    	return $configData;
    }
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name 获取随机字符串（函数）
     * @method 传参请求
     * @param int $length 需要的字符串长度
     * @return string $str 字符串
     * @author Angelljoy@qq.com	lihongqiang	2017-06-28
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    protected function getRandChar($length){
    	$str = null;
    	$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    	$max = strlen($strPol)-1;
    	for($i=0;$i<$length;$i++){
    		$str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
    	}
    	return $str;
    }
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name 获取access_token
     * @method 回调请求
     * @param int $length 需要的字符串长度
     * @return string $cacheValue 字符串
     * @author Angelljoy@qq.com	lihongqiang	2017-06-28
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    protected function getAccessToken(){
    	$AppId = C('AppId');//访问配置文件的appId
    	$AppSecret = C('AppSecret');//访问配置文件的appSecret
    	$grant_type = 'client_credential';
    	$accessTokenUrl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type='.$grant_type.'&appid='.$AppId.'&secret='.$AppSecret;
    	//$accessTokenUrl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxe2877e2098ea7b55&secret=a9ae011da93c7bff2fb79d4dfd3904e1';
    	$json_AccessTokenInfo = file_get_contents($accessTokenUrl);//返回一个json字符串
    	$AccessTokenInfo = json_decode($json_AccessTokenInfo);
    	S(array('type'=>'','prefix'=>'lishe_','expire'=>''));//缓存初始化
    	$cacheValue = $AccessTokenInfo->access_token;
    	S('jsapi_access_token',null);//清除之前的缓存，开始设置新的缓存
    	S('jsapi_access_token',$cacheValue,7198);//微信给定的有效期为7200秒，所以这里要小于7200，不设置时间表示缓存永久有效；
    	$cacheValue = S('jsapi_access_token');//读取缓存
    	return $cacheValue;//string
    }
    

    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name 获取ticket信息
     * @method 传参请求
     * @param int $accessToken 回调微信接口的凭证（access_token）
     * @return array1 数组
     * @author Angelljoy@qq.com	lihongqiang	2017-06-28
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    protected function getTicket($accessToken){
    	$getTicketUrl = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$accessToken.'&type=jsapi';
    	$json_TicketInfo = file_get_contents($getTicketUrl);//返回一个json字符串
    	$TicketInfo = json_decode($json_TicketInfo);//解析出来是个对象，而不是数组
    	if($TicketInfo->errcode==0 && $TicketInfo->errmsg=='ok'){
    		S(array('type'=>'','prefix'=>'lishe_','expire'=>'7200'));//缓存初始化
    		$cacheValue = $TicketInfo->ticket;
    		//$cacheValue = 'sM4AOVdWfPE4DxkXGEs8VHOM1rzIybTjlHDw3BkwUKI-Ajsi34bDFCH6f_10SCrpK1ufesadUpzF-OLCg0IJDg';
    		S('jsapi_ticket',null);//清除之前的缓存，开始设置新的缓存
    		S('jsapi_ticket',$cacheValue,7198);//微信给定的有效期为7200秒，所以这里要小于7200，不设置时间表示缓存永久有效；
    		$cacheValue = S('jsapi_ticket');//读取缓存再赋值返回
    		//return $TicketInfo['ticket'];//string
    		$successInfo['errcode'] = $TicketInfo->errcode;
    		$successInfo['ticket'] = $cacheValue;	//$TicketInfo->ticket;
    		$successInfo['errmsg'] = 'ticket返回成功';
    		return $successInfo;
    	}else{
    		$errorInfo['errcode'] = $TicketInfo->errcode;//42001:token超时
    		$errorInfo['ticket'] = null;
    		$errorInfo['errmsg'] = $TicketInfo->errmsg;
    		return $errorInfo;
    	}
    }
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name 查询条码是否存在，存在就获取条码信息
     * @method POST
     * @param int $accessToken 回调微信接口的凭证（access_token）
     * @return array1 数组
     * @author Angelljoy@qq.com	lihongqiang	2017-06-29
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    public function selectBarCode(){
    	if(IS_GET){
    		$Info['status'] = -1001;
    		$Info['message'] = "服务繁忙，请稍后再试";//请求方式出错，请使用POST方式请求
    		$Info['data'] = false;
    		$this->ajaxReturn($Info);
    	}else{
    		$postData = I('post.');
    		if(empty($postData)){
    			$Info['status'] = -1002;
    			$Info['message'] = "服务繁忙，请稍后再试";//没有提交任何参数
    			$Info['data'] = null;
    			$this->ajaxReturn($Info);
    		}else{
    			$stringCode = $postData['stringCode'];
    			$barcodeArr = explode(',', $stringCode);
    			$barcode = $barcodeArr[1];
    			$gxBarCodeInfo = $this->retZzgxApi($barcode);//调正章的接口查询条码信息
    			if($gxBarCodeInfo['status']!=1000){
    				$Info['status'] = -1003;
    				$Info['message'] = $gxBarCodeInfo['message'];
    				$Info['data'] = '';
    				$this->ajaxReturn($gxBarCodeInfo);
    			}else{
    				//查询条码信息是否存储过（扫描过了），没有存储则存储条码信息并返回，有存储则更新条码信息再返回
    				$barCodeInfo = $this->getBarCodeInfo($gxBarCodeInfo['data']);
    				if(empty($barCodeInfo)||$barCodeInfo==false){
    					$Info['status'] = -1004;
    					$Info['message'] = "服务繁忙，请稍后再试";//条码表读取信息失败
    					$Info['data'] = '';
    					$this->ajaxReturn($Info);
    				}else{
    					S(array('type'=>'','prefix'=>'lishe_','expire'=>''));//缓存初始化
    					$string = md5($this->account.$this->uid);
    					$cacheValue = S($string);
    						
    					if(empty($cacheValue)){
    						$newCache = $barCodeInfo['id'];
    					}else{
    						$dryclean_idArray = explode(',', $cacheValue);
    						if(in_array($barCodeInfo['id'], $dryclean_idArray)){
    							$newCache = $cacheValue;
    						}else{
    							$newCache = $cacheValue.','.$barCodeInfo['id'];
    						}
    					}
    						
    					S($string,$newCache,7198);//微信给定的有效期为7200秒，所以这里要小于7200，不设置时间表示缓存永久有效
    					$Info['status'] = 1000;
    					$Info['message'] = "查询成功";
    					$Info['data'] = $barCodeInfo;
    					$this->ajaxReturn($Info);
    				}
    			}
    		}
    	}
    }
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name 调正章的接口查询条码是否存在，存在就获取条码信息返回
     * @method 传参请求
     * @param int $accessToken 回调微信接口的凭证（access_token）
     * @return array1 or boolean
     * @author Angelljoy@qq.com
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    public  function retZzgxApi($barcode){
    	//$barcode
    	//$barcode = 'TL9RFYGYGT34P1WD';//正确的条码
    	if(empty($barcode)){
    		$Info['status'] = -1;
    		$Info['message']= '当前网络暂忙，请稍后再试';
    		$Info['data'] = null;
    		return $Info;
    	}else{
    		$keyid = $barcode;
    		$billno = time();
    		$URL = 'http://10.116.136.185:8080/lshe.framework.aoserver/api/zzgx/search';//查询
    		$LsZzgxApiUrl = $URL.'?keyid='.$keyid.'&billno='.$billno;
    		//请求url示例：
    		//http://120.25.121.47:8080/lshe.framework.aoserver/api/zzgx/search?keyid=TL9RFYGYGT34P1WD&billno=20170725160850295210647264
    		//$zzgxLsApiUrl = 'http://192.168.1.136:8081/lshe.framework.aoserver/api/zzgx/search?keyid=TL9RFYGYGT34P1WD&billno=20170725160850295210647264';
    		$jsonData = file_get_contents($LsZzgxApiUrl);//此处回来的数据编码格式为gb2312
    		$ut8_jsonData = iconv("gb2312", "utf-8//IGNORE",$jsonData);//把改变gb2312转成utf8
    		////json示例//{"code":100,"data":{"STATUS":"1","DMDM":"AA","DMJC":"","ORDID":"15284641","XFRQ":"","CORP":"深圳市供电局","YXRQ":"2019-12-31","DZQMC":"2019增16元券","KEYID":"KITL82NZCCU2FYS","RESULT":"0","PMJE":"25","XSJE":"16"},"errCode":0,"msg":"操作成功"}
    		$barcodeData = json_decode($ut8_jsonData,true);
    		if($barcodeData['code']!==100){
    			$Info['status'] = -2;
    			$Info['message']= '当前网络暂忙，请稍后再试';
    			$Info['data'] = null;
    			return $Info;
    		}else{
    			if($barcodeData['data']['RESULT']!=0){
    				$Info['status'] = -3;
    				$Info['message']= '没有查询到该相关电子券信息';
    				$Info['data'] = null;
    				return $Info;
    			}else{
    				if($barcodeData['data']['STATUS']==1 || $barcodeData['data']['STATUS']==2){
    					//可以核销
    					$CodeInfo['barcode'] = 'T'.$barcodeData['data']['KEYID'];//条码
    					$CodeInfo['pmje'] = $barcodeData['data']['PMJE'];//票面额
    					$CodeInfo['price'] = $barcodeData['data']['XSJE'];//结算额
    					$CodeInfo['deadline'] = $barcodeData['data']['YXRQ'].' 00:00:00';//有效日期//'2018-12-31 00:00:00';
    					$CodeInfo['status'] = 1;
    					$CodeInfo['barcode_status'] = $barcodeData['data']['STATUS'];
    					$CodeInfo['barcode_dmdm'] = $barcodeData['data']['DMDM'];
    					$CodeInfo['barcode_ordid'] = $barcodeData['data']['ORDID'];
    					$CodeInfo['barcode_corp'] = $barcodeData['data']['CORP'];
    					/////$CodeInfo['failure_cause']状态为1不用传
    				}else{
    					$CodeInfo['barcode'] = 'T'.$barcodeData['data']['KEYID'];//条码
    					$CodeInfo['pmje'] = $barcodeData['data']['PMJE'];//票面额
    					$CodeInfo['price'] = $barcodeData['data']['XSJE'];//结算额
    					$CodeInfo['deadline'] = $barcodeData['data']['YXRQ'].' 00:00:00';//有效日期//'2018-12-31 00:00:00';
    					$CodeInfo['status'] = 0;
    					$CodeInfo['barcode_status'] = $barcodeData['data']['STATUS'];
    					$CodeInfo['barcode_dmdm'] = $barcodeData['data']['DMDM'];
    					$CodeInfo['barcode_ordid'] = $barcodeData['data']['ORDID'];
    					$CodeInfo['barcode_corp'] = $barcodeData['data']['CORP'];
    					if($barcodeData['data']['STATUS']==3||$barcodeData['data']['STATUS']==4){
    						$CodeInfo['failure_cause'] = '已经使用';
    					}elseif($barcodeData['data']['STATUS']==6){
    						$CodeInfo['failure_cause'] = '正章退款';
    					}elseif($barcodeData['data']['STATUS']==7){
    						$CodeInfo['failure_cause'] = '已经作废';
    					}elseif($barcodeData['data']['STATUS']==8){
    						$CodeInfo['failure_cause'] = '使用失效';
    					}else{
    						$CodeInfo['failure_cause'] = '其他原因';
    					}
    				}
    				$Info['status'] = 1000;
    				$Info['message']= '查询成功';
    				$Info['data'] = $CodeInfo;
    				return $Info;
    			}
    		}
    	}
    }
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name 查询条码曾经是否存储，存储过就获取条码信息返回，没有存储过就存储
     * @method 传参请求
     * @param int $accessToken 回调微信接口的凭证（access_token）
     * @return array1 or boolean
     * @author Angelljoy@qq.com
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    protected function getBarCodeInfo($gxBarCodeInfo){
    	if(empty($gxBarCodeInfo)){
    		return false;
    	}else{
    		$ModleObj = M('zzgx_barcode');
    		$where['barcode'] = $gxBarCodeInfo['barcode']; 
    		$barCodeInfo = $ModleObj->where($where)->find();
    		if($barCodeInfo){
    			//存储过-更新条码数据
    			$data['pmje'] = $gxBarCodeInfo['pmje'];
    			$data['price'] = $gxBarCodeInfo['price'];
    			$data['status'] = $gxBarCodeInfo['status'];
    			$data['barcode_status'] = $gxBarCodeInfo['barcode_status'];
    			$data['points'] = $gxBarCodeInfo['price']*100;
    			$data['barcode_dmdm'] = $gxBarCodeInfo['barcode_dmdm'];
    			$data['barcode_ordid'] = $gxBarCodeInfo['barcode_ordid'];
    			$data['barcode_corp'] = $gxBarCodeInfo['barcode_corp'];
    			if($gxBarCodeInfo['status']!=0){
    				$data['failure_cause'] = '暂未失效';
    			}else{
    				$data['failure_cause'] = $gxBarCodeInfo['failure_cause'];
    			}
    			$data['deadline'] = $gxBarCodeInfo['deadline'];
    			$data['user_id'] = $this->uid;
    			$data['user_mobile'] = $this->account;//更新扫描人，防止最后充值的时候发生我扫描，充值成别人积分
    			$data['update_time'] = date("Y-m-d H:i:s");
    			$ModleObj->where($where)->save($data);
    			$barCodeInfo_new = $ModleObj->where($where)->find();
    			return $barCodeInfo_new;
    		}else{
    			$barCodeInfo = $this->addBarCode($gxBarCodeInfo);
    			return $barCodeInfo;
    		}
    	}
    }
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name 存储条码信息
     * @method POST
     * @param int $accessToken 回调微信接口的凭证（access_token）
     * @return array1 数组
     * @author Angelljoy@qq.com
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    protected function addBarCode($gxBarCodeInfo){
    	$ModleObj = M('zzgx_barcode');
    	$data['barcode'] = $gxBarCodeInfo['barcode'];
    	$data['pmje'] = $gxBarCodeInfo['pmje'];
    	$data['price'] = $gxBarCodeInfo['price'];
    	$data['status'] = $gxBarCodeInfo['status'];
    	$data['barcode_status'] = $gxBarCodeInfo['barcode_status'];
    	$data['points'] = $gxBarCodeInfo['price']*100;
    	$data['barcode_dmdm'] = $gxBarCodeInfo['barcode_dmdm'];
    	$data['barcode_ordid'] = $gxBarCodeInfo['barcode_ordid'];
    	$data['barcode_corp'] = $gxBarCodeInfo['barcode_corp'];
    	$data['is_trigger'] = 0;
    	if($gxBarCodeInfo['status']!=0){
    		$data['failure_cause'] = '暂未失效';
    	}else{
    		$data['failure_cause'] = $gxBarCodeInfo['failure_cause'];
    	}
    	$data['deadline'] = $gxBarCodeInfo['deadline'];
    	$data['user_id'] = $this->uid;
    	$data['user_mobile'] = $this->account;
    	$data['create_time'] = date("Y-m-d H:i:s");
    	$data['update_time'] = date("Y-m-d H:i:s");
    	$bool = $ModleObj->add($data);
    	if($bool){
    		$data['id'] = $bool;
    		return $data;
    	}else{
    		return false;//存储失败
    	}
    }
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name getPayData 获取支付数据
     * @method Ajax POST
     * @param string dryclean_id 干洗券逗号分隔的id
     * @return string poundage 支付金额
     * @author Angelljoy@qq.com
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    public function getPayData(){
    	if(IS_GET){
    		$Info['status'] = -1001;
    		$Info['message'] = "服务繁忙，请稍后再试";//请求方式出错，请使用POST方式请求
    		$Info['data'] = false;
    		$this->ajaxReturn($Info);
    	}else{
    		$postData = I('post.');
    		if(empty($postData)){
    			$Info['status'] = -1002;
    			$Info['message'] = "服务繁忙，请稍后再试";//没有提交任何参数
    			$Info['data'] = null;
    			$this->ajaxReturn($Info);
    		}else{
    			$stringCode = $postData['dryclean_id'];
    			//$poundage = substr($postData['poundage'], 3);
    			$poundage = $postData['poundage'];
    			$dryclean_idArray = explode(',', $stringCode);
    			$dryclean_idArray = array_unique($dryclean_idArray);//再去重一次，防止重复值发生
				$ModleObj = M('zzgx_barcode');
				
				$dryclean = array();
				for ($i=0;$i<count($dryclean_idArray);$i++){
					$dryclean_id = $dryclean_idArray[$i];
					$where['id'] = $dryclean_id;
					$where['status'] = 1;
					$findData = $ModleObj->where($where)->find();
					$factorage += $findData['price']*0.09;
					$total_price += $findData['price'];
				}
				$a = sprintf("%.2f",$poundage);
				$b = sprintf("%.2f",$factorage);
				if($a!=$b){
					$Info['status'] = -1003;
					$Info['message'] = "服务繁忙，请稍后再试";//没有提交任何参数
					$Info['data'] = null;
					$this->ajaxReturn($Info);
				}else{
					$ModleObj->startTrans();
					//生成手续费的订单，得到payment_id
					$payment_id = $this->inteRechDo($factorage);
					if(!$payment_id){
						$Info['status'] = -1004;
						$Info['message'] = "服务繁忙，请稍后再试";//没有提交任何参数
						$Info['data'] = null;
						$this->ajaxReturn($Info);
					}else{
						//开始配置微信数据-统一下单//https://api.mch.weixin.qq.com/pay/unifiedorder
						$AppSecret = C('AppSecret');//访问配置文件的appSecret
						$nonceStr = $this->getRandChar(16);//签名参数1:随机字符串
						$payData['appid']= C('AppId');//公众账号ID访问配置文件的appId
						//$payData['mch_id'] = C('MchID');//商户号
						$payData['mch_id'] = '1219437301';
						$payData['device_info'] = 'WEB';//设备号
						$payData['nonce_str'] = $nonceStr;//随机字符串
							
						$payData['body'] ='礼舍-干洗券兑换手续费';
						$payData['out_trade_no'] = $payment_id;//$this->getOutTradeNo();
						$payData['total_fee'] = 1;
						//$payData['total_fee'] = $factorage*100;//支付金额，单位分
						$payData['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
						$isHttps = $this->is_https();
						if($isHttps){
							$protocol = 'https://';
						}else{
							$protocol = 'http://';
						}
						$notify_url = $protocol.'www.lishe.cn/wshop.php/Notify/wxtestNotify';
						$payData['notify_url'] = $notify_url;
						$payData['trade_type'] = 'JSAPI';
						$payData['product_id'] = $stringCode;//#商品ID
						S(array('type'=>'','prefix'=>'lishe_','expire'=>''));//缓存初始化
						//$string = md5($this->account.$this->uid);
						//$cacheName = md5($string);
						$wx_user_openid = S('wx_user_openid_'.$this->account);
						$payData['openid'] = $wx_user_openid;//#用户针对公众号的唯一标识
						$timestamp = time();//签名参数:时间戳
						$payData['sign_type'] ='MD5';
							
						//签名步骤一：按字典序排序参数
						ksort($payData);
						$sign_1 = $this->ToUrlParams($payData);
							
						$sign_2 = $sign_1 . "&key=".C('AppKey');
						//签名步骤三：MD5加密
						$sign = md5($sign_2);
						//签名步骤四：所有字符转为大写
						$wx_sign = strtoupper($sign);
						$payData['sign'] = $wx_sign;
						$Xml = $this->ToXml($payData);
						$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
						$resultXml = $this->postXmlCurl($Xml, $url);
						$result = $this->Init($resultXml);
						if($result){
							$payInfoConfig['appId'] = C('AppId');
							$payInfoConfig['timeStamp'] = $timestamp;
							$payInfoConfig['nonceStr'] = $nonceStr;//$this->getRandChar(16);//
							$payInfoConfig['package'] = 'prepay_id='.$result['prepay_id'];
							$payInfoConfig['signType'] = 'MD5';//'MD5';
							ksort($payInfoConfig);
							$paySign_1 = $this->ToUrlParams($payInfoConfig);
							$paySign_2 = $paySign_1 . "&key=".C('AppKey');
							$paySign = md5($paySign_2);
							//jssdk签名步骤：把所有字符转为大写
							$capital_paySign = strtoupper($paySign);
							$payInfoConfig['paySign'] = $capital_paySign;
							$payInfoConfig['payment_id'] = $payment_id;//返回payment_id，取消支付时需要用到
							$payData['timestamp'] = $timestamp;
							$payData['nonce_str'] = $nonceStr;
							$payData['prepay_id'] = $result['prepay_id'];
							$payData['predict_total_fee'] = $payData['total_fee'];
							$payData['user_openid'] = $wx_user_openid;
							$payData['sign_str'] = $sign_2;
							$payData['dryclean_id'] = $stringCode;
							$payData['dryclean_money'] = $total_price;
							$boolData = $this->wechatProceeds($payData);
							//$boolData = true;
							if($boolData){
								$ModleObj->commit();
								$Info['status'] = 1000;
								$Info['message'] = "微信服务器下单成功";
								$Info['data'] = $payInfoConfig;
								$this->ajaxReturn($Info);
							}else{
								$ModleObj->rollback(); 
								$Info['status'] = -1006;
								$Info['message'] = "服务繁忙，请稍后再试";//微信下单失败
								$Info['data'] = null;
								$this->ajaxReturn($Info);
							}
						}else{
							$Info['status'] = -1005;
							$Info['message'] = "服务繁忙，请稍后再试";//微信下单失败
							$Info['data'] = null;
							$this->ajaxReturn($Info);
						}
					}
				}
    		}
    	}
    }
    

    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name wechatProceeds 入库微信公众号收款表
     * @method 传参请求
     * @param array $ProceedsData 支付信息
     * @author Angelljoy@qq.com
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    private function wechatProceeds($ProceedsData){
    	$Model = M('wechat_public_crowd_proceeds');
    	$ProceedsData['status'] = 'AWAIT';
    	$ProceedsData['type'] = 'ZZGXSXF';
    	$ProceedsData['create_ip'] = $_SERVER['REMOTE_ADDR'];
    	$ProceedsData['create_time'] = date('Y-m-d H:i:s');
    	$ProceedsData['explain'] = '正章干洗券兑换-支付手续费';
    	$ProceedsData['user_id'] = $this->uid;
    	$ProceedsData['user_mobile'] = $this->account;
    	$userName= $this->userName;
    	if (empty($userName)){
    		$userName = $this->account;
    	}
    	$ProceedsData['user_name'] = $userName;
    	$ProceedsData['annotation'] = '等待支付';
    	$ProceedsData['checkout'] = 'N';
    	$boolData = $Model->add($ProceedsData);
    	if($boolData){
    		return $boolData;
    	}else{
    		return false;
    	}
    }
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name inteRechDo 干洗券积分兑换：1.首先要生成手续费的订单，得到payment_id	再调用微信支付去扣款
     * @method 传参请求
     * @param string $money 金额信息
     * @author Angelljoy@qq.com
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    protected function inteRechDo($money){
    	$uid = $this->uid;
    	$account = $this->account;
    	$userName= $this->userName;
    	if (empty($userName)){
    		$userName = $this->account;
    	}
    	if($userName && $uid && $account){
    		if(floatval($money)){
    			//插入支付表
    			$paymentsModel = M("ectools_payments");//支付表
    			$data['payment_id'] = $this->getOutTradeNo();
    			$data['money'] = floatval($money);//需要支付的金额floatval(1/100);//
    			$data['cur_money'] = 0;//支付货币金额
    			$data['cash_fee'] = floatval($money);//需要支付的现金 //floatval($money/100);//需要支付的现金 floatval(1/100)
    			$data['point_fee'] = 0;//需要支付的积分
    			$data['payed_cash'] = 0;//已经支付的现金
    			$data['payed_point'] = 0;//已经支付的积分
    			$data['status'] = 'ready';//支付状态
    			$data['user_id'] = $uid;
    			$data['user_name'] = $account;
    			$data['pay_type'] = 'zzgxsxf';
    			$data['pay_app_id'] = 'wxzzgx';
    			$data['pay_name'] = '微信公众号';
    			$data['pay_from'] = 'wechat';
    			$data['op_id'] = $uid; //操作员id
    			$data['op_name'] = $userName; //操作员名称
    			$data['memo'] = "正章干洗券兑换-支付手续费";
    			$data['bank'] = '预存款';//收款银行
    			$data['pay_account'] = null;//第三方支付账号
    			$data['currency'] = 'CNY';//币种
    			$data['ip'] = $_SERVER['REMOTE_ADDR'];//IP
    			$data['created_time'] = time();
    			$data['modified_time'] = time();
    			$data['point_pay_type'] = 'zzgx';
    			$boolData = $paymentsModel->add($data);
    			if($boolData){
    				return $data['payment_id'];//手续费订单生成成功
    			}else{
    				return false;//服务繁忙，请重新登录或稍后再试
    			}
    		}else{
    			return false;//服务繁忙，请重新登录或稍后再试
    		}
    	}else{
    		return false;//服务繁忙，请重新登录或稍后再试
    	}
    }
    

    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name ToUrlParams 格式化参数格式化成url参数
     * @method 传参请求
     * @param string $data 数组
     * @author Angelljoy@qq.com
     * @return string 
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    public function ToUrlParams($data){
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
     * @name cancelPay 取消支付手续费
     * @method Ajax POST
     * @param payment_id 订单编号
     * @author Angelljoy@qq.com
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    public function cancelPay(){
    	$postData = I('post.');
    	if(empty($postData)){
    		return false;
    	}else{
    		if($postData['action']=='cancel' && $postData['payment_id']){
    			$where1['payment_id'] = $postData['payment_id'];
    			$paymentsModel = M("ectools_payments");//支付表
    			$wechatModel = M("wechat_public_crowd_proceeds");//微信公众号收款表
    			$findPay1 = $paymentsModel->where($where1)->find();
    			$where2['out_trade_no'] = $postData['payment_id'];
    			$findPay2 = $wechatModel->where($where2)->find();
    			if($findPay1 && $findPay2){
    				$save1['status'] = 'cancel';
    				$bool1 = $paymentsModel->where($where1)->save($save1);
    				$save2['status'] = 'CANCEL';
    				$save2['annotation'] = '支付取消';
    				$bool2 = $wechatModel->where($where2)->save($save2);
    				if($bool1 && $bool2){
    					$Info['status'] = 1;
    					$Info['msg'] = '取消成功';
    					$this->ajaxReturn($Info);
    				}else{
    					$Info['status'] = 0;
    					$Info['msg'] = '取消失败';
    					$this->ajaxReturn($Info);
    				}
    			}else{
    				$Info['status'] = 0;
    				$Info['msg'] = '取消失败';
    				$this->ajaxReturn($Info);
    			}
    		}else{
    			$Info['status'] = 0;
    			$Info['msg'] = '取消失败';
    			$this->ajaxReturn($Info);
    		}
    	}
    }
    
    
    
    
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name ToXml 输出xml字符
     * @method POST
     * @param $array 数组信息
     * @author Angelljoy@qq.com
     * @return xml
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    public function ToXml($array){
    	if(!is_array($array) || count($array) <= 0){
    		return false;
    	}else{
    		$xml = "<xml>";
    		foreach ($array as $key=>$val){
    			if (is_numeric($val)){
    				$xml.="<".$key.">".$val."</".$key.">";
    			}else{
    				$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    			}
    		}
    		$xml.="</xml>";
    		return $xml;
    	}
    }
    
    

    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name getMillisecond 获取毫秒级别的时间戳
     * @method 回调请求
     * @param $array 数组信息
     * @author Angelljoy@qq.com
     * @return time
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    private static function getMillisecond()
    {
    	//获取毫秒的时间戳
    	$time = explode ( " ", microtime () );
    	$time = $time[1] . ($time[0] * 1000);
    	$time2 = explode( ".", $time );
    	$time = $time2[0];
    	return $time;
    }
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * 以post方式提交xml到对应的接口url
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param int $second   url执行超时时间，默认30s
     * @author Angelljoy@qq.com
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    private static function postXmlCurl($xml, $url, $second = 30)
    {
    	$ch = curl_init();
    	//设置超时
    	curl_setopt($ch, CURLOPT_TIMEOUT, $second);
    	curl_setopt($ch,CURLOPT_URL, $url);
    	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
    	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
    	//设置header
    	curl_setopt($ch, CURLOPT_HEADER, FALSE);
    	//要求结果为字符串且输出到屏幕上
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    	//post提交方式
    	curl_setopt($ch, CURLOPT_POST, TRUE);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    	//运行curl
    	$data = curl_exec($ch);
    	//返回结果
    	return $data;
    }

    /**
     * 将xml转为array
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @author Angelljoy@qq.com
     */
    public function Init($xml){
    	$arrayXml = $this->FromXml($xml);
    	if($arrayXml['return_code'] == 'SUCCESS'){
    		return $arrayXml;
    	}else {
    		return false;
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
    public function FromXml($xml){
    	//将XML转为array
    	//禁止引用外部xml实体
    	libxml_disable_entity_loader(true);
    	$arrayXml = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    	return $arrayXml;
    }
    
    
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name getOutTradeNo 获取商户单号
     * @method 回调请求
     * @return string number
     * @author lihongqiang	2017-07
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    private function getOutTradeNo(){
    	$randomFileName = date("YmdHis").$this->getMillisecond2(6).mt_rand(100000,999999);
    	return $randomFileName;
    }
    

    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name getMillisecond2 获取毫秒数
     * @method 回调请求
     * @param $digit 需要多少位 
     * @return string number
     * @author lihongqiang	2017-07
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
    public function getMillisecond2($digit=null){
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
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name is_https 判断请求是否走的是httpsxiey
     * @method 回调请求
     * @param $digit 需要多少位
     * @return string number
     * @author lihongqiang	2017-07
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     */
   private function is_https() {
    	if (! empty ( $_SERVER ['HTTPS'] ) && strtolower ( $_SERVER ['HTTPS'] ) !== 'off') {
    		return TRUE;
    	} elseif (isset ( $_SERVER ['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER ['HTTP_X_FORWARDED_PROTO'] === 'https') {
    		return TRUE;
    	} elseif (! empty ( $_SERVER ['HTTP_FRONT_END_HTTPS'] ) && strtolower ( $_SERVER ['HTTP_FRONT_END_HTTPS'] ) !== 'off') {
    		return TRUE;
    	}else{
    		return FALSE;
    	}
    }
   
    
    //测试
	private  function actiontest(){
		/**
		 * socket_accept() 接受一个Socket连接
		 socket_bind() 把socket绑定在一个IP地址和端口上
		 socket_clear_error() 清除socket的错误或者最后的错误代码
		 socket_close() 关闭一个socket资源
		 socket_connect() 开始一个socket连接
		 socket_create_listen() 在指定端口打开一个socket监听
		 socket_create_pair() 产生一对没有区别的socket到一个数组里
		 socket_create() 产生一个socket，相当于产生一个socket的数据结构
		 socket_get_option() 获取socket选项
		 socket_getpeername() 获取远程类似主机的ip地址
		 socket_getsockname() 获取本地socket的ip地址
		 socket_iovec_add() 添加一个新的向量到一个分散/聚合的数组
		 socket_iovec_alloc() 这个函数创建一个能够发送接收读写的iovec数据结构
		 socket_iovec_delete() 删除一个已经分配的iovec
		 socket_iovec_fetch() 返回指定的iovec资源的数据
		 socket_iovec_free() 释放一个iovec资源
		 socket_iovec_set() 设置iovec的数据新值
		 socket_last_error() 获取当前socket的最后错误代码
		 socket_listen() 监听由指定socket的所有连接
		 socket_read() 读取指定长度的数据
		 socket_readv() 读取从分散/聚合数组过来的数据
		 socket_recv() 从socket里结束数据到缓存
		 socket_recvfrom() 接受数据从指定的socket，如果没有指定则默认当前socket
		 socket_recvmsg() 从iovec里接受消息
		 socket_select() 多路选择
		 socket_send() 这个函数发送数据到已连接的socket
		 socket_sendmsg() 发送消息到socket
		 socket_sendto() 发送消息到指定地址的socket
		 socket_set_block() 在socket里设置为块模式
		 socket_set_nonblock() socket里设置为非块模式
		 socket_set_option() 设置socket选项
		 socket_shutdown() 这个函数允许你关闭读、写、或者指定的socket
		 socket_strerror() 返回指定错误号的详细错误
		 socket_write() 写数据到socket缓存
		 socket_writev() 写数据到分散/聚合数组
		 */
	}
	
	
	private function testsocket(){
		//确保在连接客户端时不会超时
		set_time_limit(0);
		//设置IP和端口号
		$address = "127.0.0.1";
		$port = 2046; //调试的时候，可以多换端口来测试程序！
		/**
		 * 创建一个SOCKET
		 * AF_INET=是ipv4 如果用ipv6，则参数为 AF_INET6
		 * SOCK_STREAM为socket的tcp类型，如果是UDP则使用SOCK_DGRAM
		 */
		$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("socket_create() 失败的原因是:" . socket_strerror(socket_last_error()) . "/n");
		//阻塞模式
		socket_set_block($sock) or die("socket_set_block() 失败的原因是:" . socket_strerror(socket_last_error()) . "/n");
		//绑定到socket端口
		$result = socket_bind($sock, $address, $port) or die("socket_bind() 失败的原因是:" . socket_strerror(socket_last_error()) . "/n");
		//开始监听
		$result = socket_listen($sock, 4) or die("socket_listen() 失败的原因是:" . socket_strerror(socket_last_error()) . "/n");
		echo "OK\nBinding the socket on $address:$port ... ";
		echo "OK\nNow ready to accept connections.\nListening on the socket ... \n";
		do { // never stop the daemon
			//它接收连接请求并调用一个子连接Socket来处理客户端和服务器间的信息
			$msgsock = socket_accept($sock) or  die("socket_accept() failed: reason: " . socket_strerror(socket_last_error()) . "/n");
		
			//读取客户端数据
			echo "Read client data \n";
			//socket_read函数会一直读取客户端数据,直到遇见\n,\t或者\0字符.PHP脚本把这写字符看做是输入的结束符.
			$buf = socket_read($msgsock, 8192);
			echo "Received msg: $buf   \n";
		
			//数据传送 向客户端写入返回结果
			$msg = "welcome \n";
			socket_write($msgsock, $msg, strlen($msg)) or die("socket_write() failed: reason: " . socket_strerror(socket_last_error()) ."/n");
			//一旦输出被返回到客户端,父/子socket都应通过socket_close($msgsock)函数来终止
			socket_close($msgsock);
		} while (true);
		socket_close($sock);
	}
    
    
  }
