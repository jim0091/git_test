<?php
/**
  +------------------------------------------------------------------------------
 * DeruntangController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: DeruntangController.class.php v001 2016-06-30
 * @description 德润堂
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class DeruntangController extends CommonController {
	public function index(){
		if(empty($this->uid)){
			header("Location:".__APP__."/Deruntang/login");
		}else{
			header("Location:".__APP__."/Activity/fifth");
		}		
	}
  
	//用户登录
	public function login(){
		if(!empty($this->uid)){
			header("Location:/member-index.html");
		}else{
			$this->assign('refer',__APP__.'/Deruntang');		
			$this->display();
		}		
	}
  
	//退出登录
	public function logout(){
		session(null);
		cookie(null);
		header("Location:".__APP__."/Deruntang/login");
	}
}