<?php  
namespace Home\Controller;
class LoginController extends CommonController{
	public function __construct(){
		parent::__construct();
		$this->userAccountModel = M('sysuser_account');//用户登录表
	}

	public function login(){
		if($_POST){
			$userName = I('post.userName');
	  		$password = I('post.password');
	  		$url=I('post.refer');
	  		
	  		$this->superAdminLogin($userName,$password,$url);
	  		
			if(empty($userName) or empty($password)){
				echo 0;
				exit;
			}
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
				$checkUser=$this->userAccountModel->field('user_id')->where($condition)->find();
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
				
        		$account=array(
	        		'id'=>$userId,
	        		'comId'=>$data['comId'],
	        		'account'=>$userName,
	        		'userName'=>$data['empName']
        		);
        		session('account',json_encode($account));        		 		     
				cookie('account',json_encode($account));
        		echo json_encode(array($userId,'登录成功！',urldecode($url)));
			}
		}else{
			$this->display();
		}
	}
	public function register(){		
		$this->assign('refer',I('refer'));		
		$this->display();
	}
	//退出登录
	public function logout(){
		session('account',null);
		cookie('account',null);
		session(null);
		session_destroy();
		redirect(__APP__."/Login/login");
	}
	
}
