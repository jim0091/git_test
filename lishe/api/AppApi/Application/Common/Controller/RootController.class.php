<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[权限控制];							@version:1.0
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
class RootController extends CommonController {
	public function _initialize(){
		header ( "content-type:text/html;charset=utf-8" );
		$Redis = new Redis();
		if(IS_GET){
			$getData = I('get.');
			$token = $getData['token'];
			$receiveData = $getData;
		}else{
			$postData = I('post.');
			$token = $postData['token'];
			$receiveData = $postData;
		}
		//存储日志(返回日志ID)
		$saveLogInfo = $this->appPortRequestlog($receiveData);
		$_SESSION['appPortRequestlogID'] = $saveLogInfo;
		//检测登录
		if(empty($token)){
			$errorInfo['status'] = 0;
			$errorInfo['message'] = "亲，您还没有登录呢";
			$this->retError($errorInfo);
		}else{
			$userInfo = $Redis->get($token);
			if(empty($userInfo)){
				$errorInfo['status'] = 0;
				$errorInfo['message'] = "亲，登录过期了";
				$this->retError($errorInfo);
			}else{
				$_SESSION['uid'] = $userInfo['id'];
				$_SESSION['user_id'] = $userInfo['id'];
				$_SESSION['com_id'] = $userInfo['comId'];
				$_SESSION['account'] = $userInfo['account'];
				$_SESSION['userName'] = $userInfo['userName'];
			}
		}
	}
}