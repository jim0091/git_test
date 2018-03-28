<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[Index模块接口];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Index/Controller/];
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
 * |@Email:		<lhq@lishe.cn>				@OverTime:	2016
 * +----------------------------------------------------------------------
 *  */
namespace Index\Controller;
use Common\Common\Classlib\Sendmsg\SendMsg;

use Common\Controller\CommonController;

use Common\Controller\RootController;
use Index\Service\IndexService;
use Think\Controller;
class TestController extends RootController {
	
	public function index(){
		exit("这是测试控制器");
	}
	
    public function testSend(){
    	$PhoneNumber = 13839685202;
    	$MsgContent = "礼舍APP短信测试";
    	$SendMsg = new SendMsg();
    	$SindInfo = $SendMsg->MsgSend($PhoneNumber, $MsgContent);
    	var_dump($SindInfo);
    }
    
    
    public function test_strlen(){
    	$string = "亲，您还没有登录呢a__/";
    	$strlen = strlen($string);
    	$strlen1 = mb_strlen($string,'UTF8');
    	var_dump($string);
    	var_dump($strlen);
    	var_dump($strlen1);
    	$substr = mb_substr($string,0,65530,'UTF8');
    	var_dump($substr);
    }
    
    //控制器常量输出测试
    public function testpath(){
    	var_dump('__ROOT__输出=>     '.__ROOT__);
    	var_dump('__APP__输出=>     '.__APP__);
    	var_dump("__MODULE__输出=>     ".__MODULE__);
    	var_dump("__CONTROLLER__输出=>     ".__CONTROLLER__);
    	var_dump('__ACTION__输出=>     '.__ACTION__);
    	var_dump('__SELF__输出=>     '.__SELF__);
    	var_dump('__INFO__输出=>     '.__INFO__);
    	var_dump('__EXT__输出=>     '.__EXT__);
    	var_dump('MODULE_NAME输出=>     '.MODULE_NAME);
    	var_dump('MODULE_PATH输出=>     '.MODULE_PATH);
    	var_dump('CONTROLLER_NAME输出=>     '.CONTROLLER_NAME);
    	var_dump('CONTROLLER_PATH输出=>     '.CONTROLLER_PATH);
    	var_dump('ACTION_NAME输出=>     '.ACTION_NAME);
    	echo '<br/>';
    	
    	var_dump('$_SERVER["DOCUMENT_ROOT"]输出=>     '.$_SERVER['DOCUMENT_ROOT']);
    	var_dump('$_SERVER["GATEWAY_INTERFACE"]输出=>     '.$_SERVER['GATEWAY_INTERFACE']);
    	var_dump('$_SERVER["HTTP_ACCEPT"]输出=>     '.$_SERVER['HTTP_ACCEPT']);
    	var_dump('$_SERVER["HTTP_ACCEPT_ENCODING"]输出=>     '.$_SERVER['HTTP_ACCEPT_ENCODING']);
    	var_dump('$_SERVER["HTTP_ACCEPT_LANGUAGE"]输出=>     '.$_SERVER['HTTP_ACCEPT_LANGUAGE']);
    	var_dump('$_SERVER["HTTP_CONNECTION"]输出=>     '.$_SERVER['HTTP_CONNECTION']);
    	var_dump('$_SERVER["HTTP_HOST"]输出=>     '.$_SERVER['HTTP_HOST']);
    	var_dump('$_SERVER["HTTP_USER_AGENT"]输出=>     '.$_SERVER['HTTP_USER_AGENT']);
    	var_dump('$_SERVER["PATH"]输出=>     '.$_SERVER['PATH']);
    	var_dump('$_SERVER["PATH_TRANSLATED"]输出=>     '.$_SERVER['PATH_TRANSLATED']);
    	var_dump('$_SERVER["PHP_SELF"]输出=>     '.$_SERVER['PHP_SELF']);
    	var_dump('$_SERVER["QUERY_STRING"]输出=>     '.$_SERVER['QUERY_STRING']);
    	var_dump('$_SERVER["REMOTE_ADDR"]输出=>     '.$_SERVER['REMOTE_ADDR']);
    	var_dump('$_SERVER["REMOTE_PORT"]输出=>     '.$_SERVER['REMOTE_PORT']);
    	var_dump('$_SERVER["REQUEST_METHOD"]输出=>     '.$_SERVER['REQUEST_METHOD']);
    	var_dump('$_SERVER["REQUEST_TIME"]输出=>     '.$_SERVER['REQUEST_TIME']);
    	var_dump('$_SERVER["REQUEST_URI"]输出=>     '.$_SERVER['REQUEST_URI']);
    	var_dump('$_SERVER["SCRIPT_FILENAME"]输出=>     '.$_SERVER['SCRIPT_FILENAME']);
    	var_dump('$_SERVER["SCRIPT_NAME"]输出=>     '.$_SERVER['SCRIPT_NAME']);
    	var_dump('$_SERVER["SERVER_ADDR"]输出=>     '.$_SERVER['SERVER_ADDR']);
    	var_dump('$_SERVER["SERVER_ADMIN"]输出=>     '.$_SERVER['SERVER_ADMIN']);
    	var_dump('$_SERVER["SERVER_NAME"]输出=>     '.$_SERVER['SERVER_NAME']);
    	var_dump('$_SERVER["SERVER_PORT"]输出=>     '.$_SERVER['SERVER_PORT']);
    	var_dump('$_SERVER["SERVER_PROTOCOL"]输出=>     '.$_SERVER['SERVER_PROTOCOL']);
    	var_dump('$_SERVER["SERVER_SIGNATURE"]输出=>     '.$_SERVER['SERVER_SIGNATURE']);
    	var_dump('$_SERVER["SERVER_SOFTWARE"]输出=>     '.$_SERVER['SERVER_SOFTWARE']);
    	
    	var_dump('$_SERVER["HTTP_HOST"].__SELF__输出=>     输出=>     '.$_SERVER['HTTP_HOST'].__SELF__);
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	echo "<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>";
    	
    }
    
    
    
    
    
    
    
    
}