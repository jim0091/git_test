<?php
/**
  +------------------------------------------------------------------------------
 * ZlhjController
  +------------------------------------------------------------------------------
 * @author   	高龙 <1025264711@qq.com>
 * @version  	$Id: ZlhjController.class.php v001 2017-01-12
 * @description 此接口为助力回家接口，为一企一舍那边提供,微信调用
 * 				*注意，如果接口使用完毕，请及时注释或删除
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
use Think\Controller;
use Think\Log;

class ZlhjController extends Controller{
	public function __construct(){
		parent::__construct();
	}
	
	public function _empty(){
		echo 'don\'t request me!';
	}
	
	/**
	 * 此接口为助力回家接口，为一企一舍那边提供
	 * @author Gaolong
	 */
	public function index(){
		$state = I('get.state','','strip_tags,stripslashes');
		$code = I('get.code','','strip_tags,stripslashes');
		if(empty($state)){
			echo 'empty state';
			//Log::write('empty state','DEBUG');
			exit();
		}
		
		if(empty($code)){
			echo 'empty code';
			//Log::write('empty code','DEBUG');
			exit();
		}
		
		$url = "http://120.76.43.74:8080/lshe.framework.protocol.http/api/h5card/getWxUserInfo?code={$code}&state={$state}";
		//Log::write($url,'DEBUG');
		
		//$result = curl($url);
		$result = file_get_contents($url);
		$resultArr = json_decode($result, true);
		header ( "content-type:text/html;charset=utf-8" );
		//Log::write($result,'DEBUG');
		if($resultArr['errcode'] !== 0){
			//Log::write('stop run and exit','DEBUG');
			exit();
		}
		
		$info = $resultArr['data']['info'];
		//暂时不写到配置文件中
		//$reqAction = 'http://uatv.lishe.cn/gh';//uat环境
		$reqAction = 'http://v.lishe.cn/gh';//生产环境
		
		//测试环境
		//$retAction = 'http://uatv.lishe.cn/wap';//uat环境
		$retAction = 'http://v.lishe.cn/wap';//生产环境
		if($state == 'wxlogin'){
			header('Location: '.$reqAction."/sharemine.html?openid={$info}");
		}else if(stripos($state,'helphim') !== false){
			header('Location: '.$reqAction."/helphim.html?state={$state}&openid={$info}");
		}else if(stripos($state,'xxActive') !== false){
			$linkAction = 'http://v.lishe.cn/lshe.framework.protocol.http/api/activeEmp';
			//header('Location: '.$reqAction."/helphim.html?state={$state}&openid={$info}");
			header('Location: '.$linkAction."/reg?state={$state}&openid={$info}");
		}
	}
		
}