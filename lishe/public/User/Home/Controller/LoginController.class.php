<?php
/**
  +------------------------------------------------------------------------------
 * LoginController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: LoginController.class.php v001 2015-10-17
 * @description 用户登录
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class LoginController extends CommonController {
	public function index(){
		$adminId=session('adminId');
		$adminName=session('adminName');
		if(!empty($adminId) and !empty($adminName)){
			header("Location:".__APP__."/Index/profile");
			exit;
		}
		session(null);
		$this->display();
	}
  
	//用户登录
	public function login(){
		if($_POST){
			$userName = I('post.userName');
	  		$password = I('post.password');
			if(empty($userName) or empty($password)){
				echo 0;
				exit;
			}
			$uclogin=D('Admin')->adminLogin($userName,$password);
			if(empty($uclogin['adminId'])){
				echo -1;
				exit;
			}else{
				session('adminId',$uclogin['adminId']);
				session('realName',$uclogin['realName']);
				session('adminName',$userName);
				echo $uclogin['adminId'];
				exit;
			}
		}else{
			echo -3;
		}
	}
  
	//退出登录
	public function logout(){
		session(null);
		cookie(null);
		header("Location:".__APP__."/Login");
	}
}