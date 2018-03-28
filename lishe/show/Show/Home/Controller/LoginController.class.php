<?php
namespace Home\Controller;
class LoginController extends CommonController {
		public function __construct(){
			parent::__construct();
			$this->comId=session('comId');
			$this->dShow=D('Show');
	}
	public function index(){
		$from = I('get.from','','trim,strip_tags,stripslashes');
		$redirect = '';
		if($from == 'acTask'){
			$redirect = 'http://'.$_SERVER['HTTP_HOST'].U('Login/index');
			$redirect = urlencode($redirect);
		}else{
			$from = '';//目前只有acTask，后期修改
		}
		
		$comId=$this->comId;
		if(session('account')){
			$referUrl=A('Preview')->checkComId($comId);
			if($referUrl==0){
				$referUrl=__APP__."/Index/index";
			}
			header("Location:".$referUrl); 		
		}			
		$condition['com_id']=$comId;
		$condition['is_delete']=0;
		$templete=$this->dShow->getCompanyInfo($condition,"templete,refer");
		$tempName=$templete['templete'];	
		if(!empty($tempName)){
			$tempName=$tempName.'login';
		}else{
//			$tempName='login';
			$tempName='moonlogin';
		}				
		
		$this->assign('from', $from);
		$this->assign('redirect', $redirect);
		$this->assign('refer',$templete['refer']);
		$this->display($tempName);			
	}
	//用户登录
	public function login(){
		if($_POST){
			$userName = I('post.userName');
	  		$password = I('post.password');
	  		$userMark=I('post.mark');
	  		$check=I('post.check');
	  		if(empty($check)){
				$check='y';
			}
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
				if(!empty($userMark)){
					unset($condition);
					$condition['mark']=$userMark;
					$userCompany=M('company_config')->field('com_id,refer,index')->where($condition)->find();	
					$account['index']=urlencode($userCompany['index']);
					$account['refer']=urlencode($userCompany['refer']);
				}
				
				if($check=='y' && $userCompany['com_id']!=$data['comId']){
					echo json_encode(array(-2,'您不是该企业的用户！',-2));
					exit;
				}
				
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
}