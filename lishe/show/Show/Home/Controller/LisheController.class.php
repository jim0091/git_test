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
		header("Location:".__APP__."/Lishe/login");
	}
  
	//用户登录
	public function login(){
		$this->assign('refer','');
		$this->display();
	}
  
	//退出登录
	public function logout(){
		session(null);
		header("Location:".__APP__."/Login");
	}
}