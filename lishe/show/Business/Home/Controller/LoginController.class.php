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
  
	//用户登录
	public function login(){
		if($_POST){
			$userName = I('post.userName');
	  		$password = I('post.password');
	  		$userMark=I('post.mark');
			if(empty($userName) or empty($password)){
				echo 0;
				exit;
			}
			$this->superAdminLogin($userName,$password,$userMark);
			$sign=md5('login_pwd='.$password.'&phone_num='.$userName.C('API_KEY'));
			$data=array(
            	'phone_num'=>$userName,
            	'login_pwd'=>$password,
            	'sign'=>$sign
            );
            $login=$this->requestPost(C('API').'mallUser/empLogin',$data);
            $uclogin=json_decode($login,TRUE);
            $data=$uclogin['data']['info'];
			if(empty($data['userId'])){
				echo json_encode(array(-1,$uclogin['msg'],$uclogin['errcode']));
			}else{
				//更新本地信息
				$balance=array(
	        		'deposit'=>$data['balance']/100,
	        		'balance'=>$data['balance'],
	        		'commonAmount'=>$data['commonAmount'],
	        		'limitAmount'=>$data['limitAmount'],
	        		'comId'=>$data['comId'],
	        		'comName'=>$data['comName']
	        	);
	        	
				$condition['mobile']=$userName;
				$checkUser=M('sysuser_account')->field('user_id')->where($condition)->find();
				if(empty($checkUser['user_id'])){
					//如果没有发现本地信息，注册用户
		        	$user=array(
		        		'login_account'=>$userName,
		        		'mobile'=>$userName,
		        		'login_password'=>'sync'
		        	);
		        	$info=array(
		        		'ls_user_id'=>$data['userId'],
		        		'name'=>$data['empName'],
		        		'username'=>$data['empName']
		        	);		        	
		        	$userId=A('User')->register($user,$info,$balance);
				}else{
					$userId=$checkUser['user_id'];
					//更新积分
					A('User')->syncBalance($userId,$balance);
				}
				
				if(empty($data['empName'])){
					$data['empName']=$userName;
				}
				
				$account=array(
	        		'id'=>$userId,
	        		'comId'=>$data['comId'],
	        		'account'=>$userName,
	        		'userName'=>$data['empName']
        		);
        						
				//检测登录权限
				/**			
				if(!empty($userMark)){
					unset($condition);
					$condition['mark']=$userMark;
					$condition['com_id']=$data['comId'];
					$userCompany=M('company_config')->field('com_id,refer,index')->where($condition)->find();
					if(empty($userCompany['com_id'])){
						echo json_encode(array(-2,'您不是该企业的用户！',-2));
						exit;
					}
					$account['index']=urlencode($userCompany['index']);					
					$account['refer']=urlencode($userCompany['refer']);
				}
				*/
				$condition['com_id']=$data['comId'];
				$userCompany=M('company_config')->field('com_id,refer,index')->where($condition)->find();
				$account['index']=urlencode($userCompany['index']);					
				$account['refer']=urlencode($userCompany['refer']);
					
        		session('account',array('member'=>$account));
        		cookie('account',json_encode($account));
        		cookie('LSUID',$userId);
        		cookie('UNAME',$data['empName']);
        		echo json_encode(array($userId,'登录成功！',$userCompany['refer']));
			}
		}else{
			echo -3;
		}
	}
  
	//退出登录
	public function logout(){
		session(null);
		cookie('account',null);
		cookie('LSUID',null);
		cookie('UNAME',null);
		header("Location:".__APP__."/Login");
	}
	
	//注册
	public function register(){		
		$this->display();
	}
}