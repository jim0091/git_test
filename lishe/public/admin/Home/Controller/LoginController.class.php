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
		if(!empty($this->adminId)){
			header("Location:".__APP__."/Index");
			exit;
		}
		$this->display();
	}
  
	//用户登录
	public function login(){
		if($_POST){
			$userName = trim($_POST['userName']);
	  		$password = trim($_POST['password']);
			if(empty($userName) or empty($password)){
				echo json_encode(array(-1,'用户名和密码不能为空！'));
				exit;
			}

			$uclogin=D('Admin')->adminLogin($userName,$password);

			if(empty($uclogin['admin_id'])){
				echo json_encode(array(-2,'管理员不存在！'));
				exit;
			}else{
				$password=md5($password.$uclogin['salt']);
				if($uclogin['admin_password']!=$password){
					echo json_encode(array(-3,'管理员密码不正确！'));
					exit;
				}
				if($uclogin['status']<=0){
					echo json_encode(array(-4,'管理员被禁用！'));
					exit;
				}
				$account=array(
	        		'uid'=>$uclogin['admin_id'],
	        		'realName'=>$uclogin['real_name'],
	        		'userName'=>$userName,
	        		'role_id'=>$uclogin['role_id'],
        		);
        if($uclogin['role_id']==0){
        	session('roleName','超级管理员');
        }else{
	        $role=D('Admin')->getThisRoleInfo($uclogin['role_id']);
        	session('roleName',$role['name']);
        }
				session('adminAccount',$account);
				cookie('adminAccount',json_encode($account));
				echo json_encode(array($uclogin['admin_id'],'登录成功！'));
				exit;
			}
		}else{
			echo json_encode(array(-100,'超过权限！'));
		}
	}
  
	//退出登录
	public function logout(){
		session(null);
		cookie('adminAccount',null);
		header("Location:".__APP__."/Login");
	}
}