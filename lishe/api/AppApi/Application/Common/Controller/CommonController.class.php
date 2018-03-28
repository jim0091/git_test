<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[公共服务];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Common/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[IndexController.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-1-4 15:06
 * +----------------------------------------------------------------------
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2016
 * +----------------------------------------------------------------------
 *  */
namespace Common\Controller;
use Think\Cache\Driver\Redis;

use Think\Controller;
class CommonController extends Controller {
	public function _initialize(){
		header ( "content-type:text/html;charset=utf-8" );
		//开始记录接口请求日志
		if(IS_GET){
			$getData = I('get.');
			$receiveData = $getData;
		}else{
			$postData = I('post.');
			$receiveData = $postData;
		}
		$saveLogInfo = $this->appPortRequestlog($receiveData);
		$_SESSION['appPortRequestlogID'] = $saveLogInfo;
	}
	
	
	/**
	 * @name 接口返回成功信息
	 * @param int $successInfo['status'] //状态
	 * @param string $successInfo['message'] //提示信息
	 * @example status:状态；message:提示信息
	 */
	public function retSuccess($successInfo){
		$result['result'] = 100;
		$result['errcode'] = 0;
		$result['msg'] = '接口通讯成功，程序执行成功';//接口通讯失败
		if(!isset($successInfo['status'])){
			$successInfo['status'] = 1000;
		}
		$result['status'] = $successInfo['status'];//状态
		if(!isset($successInfo['message'])){
			$successInfo['message'] = "操作成功";
		}
		$result['message'] = $successInfo['message'];//提示信息
		if(empty($successInfo['data'])){
			$successInfo['data'] = '';
		}
		$result['data'] = $successInfo['data'];//提示信息
		$this->jsonEncode($result);
	}

	
	/**
	 * @name 接口返回错误信息
	 * @param int $errorInfo['status'] //状态
	 * @param string $errorInfo['message'] //提示信息
	 * @example status:状态；message:提示信息
	 */
	public function retError($errorInfo){
		$result['result'] = 0;
		$result['errcode'] = 100;
		if(!isset($errorInfo['msg'])){
			$result['msg'] = '接口通讯成功，程序执行失败，失败原因未知';
		}else{
			$result['msg'] = '接口通讯成功，程序执行失败，失败原因：'.$errorInfo['msg'];
		}
		if(!isset($errorInfo['status'])){
			$errorInfo['status'] = 0000;
		}
		$result['status'] = $errorInfo['status'];
		if(!isset($errorInfo['message'])){
			$errorInfo['message'] = "服务繁忙，请稍后再试";
		}
		$result['message'] = $errorInfo['message'];
		$this->jsonEncode($result);
	}
	
    //输出成功或失败的结果给客户端
    protected function jsonEncode($result){
    	$jsonData = json_encode($result);
    	echo $jsonData;
    	//记录返回数据到日志表
    	$logObj = M('app_portrequest_log');
    	if($result['result']==100){
    		$saveLogData['requestStatus'] = 1;
    	}else{
    		$saveLogData['requestStatus'] = 0;
    	}
    	$saveLogData['returnResultCode'] = $result['result'];
    	$saveLogData['returnErrorCode'] = $result['errcode'];
    	$saveLogData['returnMessage'] = $result['message'];
    	$saveLogData['returnMsg'] = $result['msg'];
    	$saveLogData['returnStatus'] = $result['status'];
    	$jsonData_strlen = mb_strlen($jsonData,'UTF8');//json长度
    	if($jsonData_strlen>65534){//text文本最大长度为65535
    		$jsonData = mb_substr($jsonData,0,65530,'UTF8');
    	}
    	$saveLogData['returnjsonData'] = $jsonData;
    	$saveLogData['updateTime'] = date('Y-m-d H:i:s');
    	$where['id'] = $_SESSION['appPortRequestlogID'];
    	$findLog = $logObj->where($where)->find();
    	if($findLog){
    		$bool = $logObj->where($where)->save($saveLogData);
    		if($bool){
    			//销毁$_SESSION['appPortRequestlogID']
    			session('appPortRequestlogID',null); 
    		}
    	}
    	//本次程序执行完毕
    	exit;
    }
    
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
    
    //京东提交
    public function requestJdPost($url='', $data=''){
    	if(empty($url) || empty($data)){
    		return false;
    	}
    	$ch=curl_init();//初始化curl
    	curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
    	curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    	curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
    	curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
    	$return=curl_exec($ch);//运行curl
    	curl_close($ch);
    	return $return;
    }

	//App接口请求日志
    public function appPortRequestlog($receiveData){
    	$logObj = M('app_portrequest_log');
    	$logData['moduleName'] = MODULE_NAME;//请求的模块名称
    	$logData['controllerName'] = CONTROLLER_NAME;//请求的控制器名称
    	$logData['actionName'] = ACTION_NAME;//请求的方法名称
    	$logData['requestUrl'] = 'http://'.$_SERVER['HTTP_HOST'].__SELF__;
    	if(!empty($receiveData)){
    		$logData['receivejsonData'] = json_encode($receiveData);
    	}
    	if(!empty($receiveData['token'])){
    		$logData['token'] = $receiveData['token'];
    		$Redis = new Redis();
    		$userInfo = $Redis->get($receiveData['token']);
    		if($userInfo){
    			$logData['userID'] = $userInfo['id'];
    			$logData['comID'] = $userInfo['comId'];
    			$logData['account'] = $userInfo['account'];
    		}
    	}
    	$logData['requestMethod'] = $_SERVER["REQUEST_METHOD"];//请求接口的方式
    	$logData['requestStatus'] = 0;//默认0，返回成功改为1
    	$logData['status'] = null;
    	$logData['returnjsonData'] =null;//返回的整个json数据
    	$logData['requestIp'] = $_SERVER['REMOTE_ADDR'];
    	$logData['createTime'] = date('Y-m-d H:i:s');
    	$logData['updateTime'] = date('Y-m-d H:i:s');
    	$logbool = $logObj->add($logData);
    	return $logbool;
    }
}