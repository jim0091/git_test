<?php  
namespace Home\Controller;
class LoginController extends CommonController{
	public function __construct(){
		parent::__construct();
		$this->userAccountModel = M('sysuser_account');//用户登录表
		$this->modelDeposit=M('sysuser_user_deposit');
	}

	public function login(){
		$url=I('refer');
		$url = str_replace("-","/",$url);
        if(empty($url)){
            $url=$_SERVER['HTTP_REFERER'];
            $url = str_replace("-","/",$url);
        }
        $this->assign("refer",urldecode($url));
		if($_POST){
			$userName = I('post.userName');
	  		$password = I('post.password');  		
	  		$this->superAdminLogin($userName,$password,$url);
	  		
			if(empty($userName) or empty($password)){
				echo json_encode(array(0,"请正确填写您的登录资料！"));
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
		        	$userId=$this->doRegister($user,$info,$balance);
				}else{					
					$userId=$checkUser['user_id'];
					//更新积分
					$this->syncBalance($userId,$balance);
				}				
				$condition['com_id']=$data['comId'];
				$company=M('company_config')->field('com_id,wshop_refer,wshop_index')->where($condition)->find();
				if(!empty($company['wshop_refer'])){
					$url=$company['wshop_refer'];
				}
        		$account=array(
	        		'id'=>$userId,
	        		'comId'=>$data['comId'],
	        		'account'=>$userName,
	        		'userName'=>$data['empName'],
	        		'refer'=>$company['wshop_refer'],
	        		'index'=>$company['wshop_index']
        		);
        		session('account',json_encode($account));        		 		     
				cookie('account',json_encode($account));
        		echo json_encode(array($userId,'登录成功！',urldecode($url)));
			}
		}else{
			$this->display();
		}
	}
	//更新本地积分
	public function syncBalance($userId,$balance){
		$checkBalance=$this->modelDeposit->field('user_id')->where('user_id='.$userId)->find();
		if(empty($checkBalance['user_id'])){
			$balance['user_id']=$userId;
			return $this->modelDeposit->add($balance);
		}
		return $this->modelDeposit->where('user_id='.$userId)->save($balance);
	}
	//本地注册
	public function doRegister($account,$info,$balance){
		$account['createtime']=time();
		$account['modified_time']=time();
		$userId=M('sysuser_account')->add($account);
		if($userId>0){
			$info['user_id']=$userId;
			M('sysuser_user')->add($info);
			$balance['user_id']=$userId;
			$this->modelDeposit->add($balance);
		}
		return $userId;
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


	//用户注册
	public function signup(){		
		$this->display();
	}
	
	public function signupMember(){
		$mobile = I('post.mobile');
		$password = I('post.password');
		$imgCode = I('post.imgCode');		
		if(empty($mobile)){
			echo json_encode(array(-1,'手机号码为空','-1'));
			exit;
		}
		if(strtolower(session('imgCode'))!=strtolower($imgCode)){
			echo json_encode(array(-2,'图片验证码不正确','-2'));
			exit;
		}
		$res=$this->checkMember($mobile);
		if($res[0]==100){
			if($res[1]=='true' && $res[2]=='true'){
				echo json_encode(array(-4,'该手机号码已激活，请直接登录',__APP__."/Login/login"));
				exit;
			}
			$sign=md5('login_pwd='.$password.'&phone_num='.$mobile.C('API_KEY'));
			$data=array(
				'login_pwd'=>$password,
	        	'phone_num'=>$mobile,
	        	'sign'=>$sign
	        );
	        $res=$this->requestPost(C('API').'mallUser/register',$data);
	        $return=json_decode($res,TRUE);			
			if($return['result']==100){
	        	$data=$return['data']['info'];     
	        	//本地注册用户
	        	$user=array(
	        		'login_account'=>$mobile,
	        		'mobile'=>$mobile,
	        		'login_password'=>'activate'
	        	);
	        	$info=array(
	        		'ls_user_id'=>$data['userId'],
	        		'name'=>$data['empName'],
	        		'username'=>$data['empName']
	        	);
	        	$userId=$this->doRegister($user,$info,$balance);
	        	if(!empty($userId)){
			        //更新积分
			        $balance=array(
		        		'deposit'=>0,
		        		'balance'=>0,
		        		'commonAmount'=>0,
		        		'limitAmount'=>0,
		        		'comId'=>$data['comId'],
		        		'comName'=>$data['comName']
		        	);
					$this->syncBalance($userId,$balance);
		        	//同步登陆
		        	$account=array(
		        		'id'=>$userId,
		        		'account'=>$mobile,
	        			'comId'=>$data['comId'],
		        		'userName'=>$data['empName']
		    		);
		    		session('account',$account['member']);
		    		cookie('account',json_encode($account));
					echo json_encode(array(100,'注册成功，礼舍欢迎您！',$userId));
				}else{
					echo json_encode(array(-2,'本地注册失败，请联系管理员！',-2));
				}
			}else{
				echo json_encode(array(-1,$return['msg'],$return['errcode']));
			}
		}else{
			echo json_encode($res);
		}
	}
	
	//用户激活
	public function activate(){
		$this->display();
	}
	
	//激活时验证用户账号
	public function checkActivate(){
		$mobile = I('get.mobile');
		$imgCode = I('get.imgCode');
		$op=I('get.op','','trim');	//用于判断pc 或者 mobile	
		if(empty($mobile)){
			echo json_encode(array(-1,'手机号码为空','-1'));
			exit;
		}
		if($op=='pc'){
			if(strtolower(session('imgCode'))!=strtolower($imgCode)){
				echo json_encode(array(-2,'图片验证码不正确','-2'));
				exit;
		    }
		}
		$res=$this->checkMember($mobile);
		if($res[0]==100){
			if($res[1]=='false'){
				echo json_encode(array(-3,'您的账号不存在','-3'));
				exit;
			}
			if($res[1]=='true' && $res[2]=='true'){
				echo json_encode(array(-4,'该手机号码已激活，请直接登录','-3'));
				exit;
			}
			$activateCode=rand(1000,9999);
			session($mobile.'activateCode',$activateCode);
			$sres=A('Sms')->send($mobile,'您的激活验证码为：'.$activateCode);
			echo json_encode(array(100,'手机验证码已发送'));
		}else{
			echo json_encode($res);
		}
	}
	
	//检测用户的注册和激活状态
	public function checkMember($mobile){
		$sign=md5('phone_num='.$mobile.C('API_KEY'));
		$data=array(
        	'phone_num'=>$mobile,
        	'sign'=>$sign
        );
        $res=$this->requestPost(C('API').'mallUser/checkUser',$data);
        $return=json_decode($res,TRUE);
        if($return['result']==100){
			$data=$return['data']['info'];
			return array(100,$data['isRegister'],$data['isActive']);
		}else{
			return array(-1,$return['msg'],$return['errcode']);
		}
	}
	
	
	//用户激活
	public function activateMember(){
		$activateCode=I('post.checkCode');
		$mobile = I('post.mobile');
		$password = I('post.password');
		if (md5($activateCode) != cookie('phoneCode') || md5($activateCode) != session('phoneCode')) {
			echo json_encode(array(0,'验证码错误','0'));
			exit;
		}
		if(empty($mobile) or empty($password)){
			echo json_encode(array(-2,'必要参数为空','-2'));
			exit;
		}
		$sign=md5('login_pwd='.$password.'&phone_num='.$mobile.C('API_KEY'));
		$data=array(
			'login_pwd'=>$password,
        	'phone_num'=>$mobile,
        	'sign'=>$sign
        );
        $res=$this->requestPost(C('API').'mallUser/activateUser',$data);
        $return=json_decode($res,TRUE);
        if($return['result']==100){
        	$data=$return['data']['info'];        	
        	$balance=array(
        		'deposit'=>$data['balance']/100,
        		'balance'=>$data['balance'],
        		'commonAmount'=>$data['commonAmount'],
        		'limitAmount'=>$data['limitAmount'],
        		'comId'=>$data['comId'],
        		'comName'=>$data['comName']
        	);
        	//查询mark
			$where['com_id'] = $data['comId'];
			$company=M('company_config')->field('mark,wshop_refer,wshop_index')->where($where)->find();
			if(!empty($company['wshop_refer'])){
				$url=$company['wshop_refer'];
			}else{
				$url='http://www.lishe.cn/wshop.php';
			}
			$mark = ucfirst($company['mark']);//首字母大写
        	
        	$condition['mobile']=$userName;
			$checkUser=M('sysuser_account')->field('user_id')->where($condition)->find();
			if(empty($checkUser['user_id'])){
	        	//本地注册用户
	        	$user=array(
	        		'login_account'=>$mobile,
	        		'mobile'=>$mobile,
	        		'login_password'=>'activate'
	        	);
	        	$info=array(
	        		'ls_user_id'=>$data['userId'],
	        		'name'=>$data['empName'],
	        		'username'=>$data['empName']
	        	);
	        	$userId=$this->doRegister($user,$info,$balance);
	        }else{
				$userId=$checkUser['user_id'];
				//更新积分
				$this->syncBalance($userId,$balance);
			}
        	//同步登陆
        	$account=array(
        		'id'=>$userId,
        		'account'=>$mobile,
        		'userName'=>$data['empName'],
        		'refer'=>$company['wshop_refer'],
        		'index'=>$company['wshop_index']        		
    		);
    		session('account',$account['member']);
    		cookie('mark',$mark);
    		cookie('account',json_encode($account));
			
			echo json_encode(array(100,'激活成功！',$userId,$url));
		}else{
			echo json_encode(array(-1,$return['msg'],$return['errcode']));
		}
	}
	



	//公共方法得到用户账号信息
	public function commonUserInfo(){
		if(empty($this->uid)){
            redirect(__APP__."/Login/login/index");
        }   
		$condition=array(
			'user_id'=>$this->uid 
			);
		$userInfo=$this->modelUser->getUserInfo($condition);

		if($userInfo){
			return $userInfo;
		}else{
			return '';
		}
	}

	//发送手机验证码
    public function sendPhoneCode(){        
        vendor('SendPhoneCode.SendCode','','.php');
        $phone = I("post.phone"); 
        $randomNumber=rand(10000,99999);
        session('phoneCode',md5($randomNumber));
        cookie('phoneCode',md5($randomNumber),3600);  
        $content = "您的礼舍验证码：".$randomNumber."。";
        $sendCode = new \SendCode();
        $codeResult = $sendCode->sendPhoneCode($phone,$content);
        if ($codeResult['message'] == "成功") {
            echo 1;
        }else{
            echo 0;
        }
    }
    //找回密码
    public function findPwd(){
    	$this->display();
    }

    // 修改密码操作 
	public function changePwd(){
		$phoneNum=I('post.phoneNum','','trim');
		$code=I('post.code','','trim');
		$pwd=I('post.pwd','','trim');
		$rpwd =I('post.rpwd','','trim');
		if($phoneNum==''){
			echo json_encode(array(0,'请填写手机号码！'));
			exit;
		};
		if($code==''){
			echo json_encode(array(0,'请填写手机验证码！'));
			exit;
		};
		if (md5($code) != cookie('phoneCode') || md5($code) != session('phoneCode')) {
			echo json_encode(array(0,'手机验证码错误！'));
			exit;
		}
		if(strlen($pwd) < 6 || strlen($pwd) > 18){
			echo json_encode(array(0,'密码长度不能小于6，超过18！'));
			exit;
		};
		if($pwd != $rpwd){
			echo json_encode(array(0,'密码和确认密码必须相同！'));
			exit;
		}

		$sign=md5('doType=fg&newPass='.$pwd.'&phoneNum='.$phoneNum.C('API_KEY'));
		$data=array(
        	'doType'=>'fg',
        	'newPass'=>$pwd,
        	'phoneNum'=>$phoneNum,
        	'sign'=>$sign
        );
        $res=$this->requestPost(C('API').'mallUser/updatePass',$data);
		$return=json_decode($res,TRUE);
		if($return['result']==100 && $return['errcode']==0){
			echo json_encode(array(1,'密码重置成功，请重新登录！'));				
		}else{
			echo json_encode(array(0,$return['msg']));
		}

	}
	
}
