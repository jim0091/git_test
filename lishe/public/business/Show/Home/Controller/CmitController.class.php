<?php
/**
  +------------------------------------------------------------------------------
 * LisheController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: LisheController.class.php v001 2016-5-22
 * @description 企业秀-招商局
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class CmitController extends CommonController {
	public function index(){
		if(!empty($this->uid)){
			header("Location:http://www.lishe.cn/member-index.html");
		}
		header("Location:".__APP__."/Cmit/login");
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
		header("Location:".__APP__."/Login");
	}
}