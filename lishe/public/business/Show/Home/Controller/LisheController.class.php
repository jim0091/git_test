<?php
/**
  +------------------------------------------------------------------------------
 * LisheController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: LisheController.class.php v001 2016-5-22
 * @description 企业秀-礼舍
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class LisheController extends CommonController {
	public function index(){
		if(!empty($this->uid)){
			header("Location:http://www.lishe.cn/member-index.html");
		}
		header("Location:".__APP__."/Lishe/login");
	}
  
	//用户登录
	public function login(){
		if(!empty($this->uid)){
			header("Location:http://www.lishe.cn/member-index.html");
		}
		$this->assign('refer','http://www.lishe.cn/business/index.php/Activity/moon');
		$this->display();
	}
  
	//退出登录
	public function logout(){
		session(null);
		cookie(null);
		header("Location:".__APP__."/Login");
	}
	
	public function activate(){
		//$ret=A('Sms')->send('18678225115','test');
		//print_r($ret);exit;
		$this->display();
	}
}