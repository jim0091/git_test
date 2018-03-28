<?php
/**
  +------------------------------------------------------------------------------
 * SignController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: SignController.class.php v001 2016-10-21
 * @description 用户登录/注册/激活/找回密码
  +------------------------------------------------------------------------------
 */  
namespace Home\Controller;
class SignController extends CommonController{
	public function __construct(){
		parent::__construct();
		if(!empty($this->uid)){
			$referUrl=C('LISHE_URL');
			header("Location:".$referUrl);
		}
		$this->userModel=M('sysuser_user');
		$this->userAccountModel=M('sysuser_account');//用户登录表
		$this->modelDeposit=M('sysuser_user_deposit');
		$this->modelCompanyCfg=M('company_config');
	}
	
	public function __destruct(){
		cookie('checkImgCode',NULL);
	    cookie('activateMobile',NULL);
	    cookie('checkMobile',NULL);
	}	
	
	//登录注册激活页面
	public function index(){
		$refer=I('refer');
		if(empty($refer)){
			$refer=$_SERVER['HTTP_REFERER'];
		}
		if(empty($refer)){
			$refer=C('LISHE_URL');
		}
		$this->assign('refer',urldecode($refer));		
		$this->display();
	}
	
	//直接跳转到激活页面
	//给Java那边使用
	public function acTask(){
		$redirect = I('get.redirect','','trim,urldecode,strip_tags,stripslashes');
		if(empty($redirect)){
			$redirect = U('Sign/index');
		}
		$this->assign('redirect', $redirect);
		$this->assign('fromAcTask','acTask');
		$this->display('acTask');
	}
	
	//用户登录
	public function sign(){
		if($_POST){
			$userName = I('post.userName');
	  		$password = I('post.password');
	  		$refer=I('post.refer');
	  		if(empty($refer)){
				$refer=C('LISHE_URL');
			}
	  		$this->superAdminLogin($userName,$password,$refer);
	  		
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
		        	$userId=$this->register($user,$info,$balance);
				}else{
					$userId=$checkUser['user_id'];
					//更新积分
					$this->syncBalance($userId,$balance);
				}				
				
        		$account=array(
	        		'id'=>$userId,
	        		'comId'=>$data['comId'],
	        		'account'=>$userName,
	        		'userName'=>$data['empName']
        		);
        		session('account',array('member'=>$account));   		 		     
				cookie('account',json_encode($account));
				cookie('LSUID',$userId);
		    	cookie('UNAME',$userName);
		    	//查询返回地址
				$where['com_id'] = $data['comId'];
				$markInfo=$this->modelCompanyCfg->field('refer,index')->where($where)->find();
				$refer = urlencode($markInfo['refer']);
        		echo json_encode(array($userId,'登录成功！',urldecode($refer)));
			}
		}else{
			$this->display();
		}
	}
	
	//退出登录
	public function logout(){
		session('account',null);
		cookie('account',null);
		session(null);
		redirect(__APP__."/Sign/index");
	}
	
	//注册
	public function signup(){
		$mobile = I('post.mobile');
		$password = I('post.password');
		$imgCode = I('post.imgCode');
		if(empty($mobile)){
			echo json_encode(array(-1,'手机号码为空','-1'));
			exit;
		}
		
		$checkImgCode = cookie('checkImgCode');
		if($checkImgCode!=md5(C('SHOP_KEY').strtolower($imgCode))){
			echo json_encode(array(-2,'图片验证码不正确','-2'));
			exit;
		}
		$res=$this->checkMember($mobile);
		if($res[0]==100){
			if($res[1]=='true' && $res[2]=='true'){
				echo json_encode(array(-3,'该手机号码已激活，请直接登录','-3'));
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
				if($return['errcode']===0){
		        	$data=$return['data']['info'];
		        	if(empty($data['empName'])){
						$data['empName']=$mobile;
					} 	        	
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
		        	$userId=$this->register($user,$info,$balance);
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
			        		'userName'=>$data['empName']
			    		);
			    		session('account',$account['member']);
			    		cookie('account',json_encode($account));
			    		cookie('LSUID',$userId);
			    		cookie('UNAME',$mobile);
						echo json_encode(array(100,'注册成功，礼舍欢迎您！',$userId));
					}else{
						echo json_encode(array(-4,'本地注册失败，请联系管理员！',-4));
					}
				}else{
					echo json_encode(array(-5,$return['msg'],$return['errcode']));
				}
			}else{
				echo json_encode(array(-1,$return['msg'],$return['errcode']));
			}
		}else{
			echo json_encode($res);
		}
	}
	
	
	//检测是否被激活，acTask使用
	public function isActivate(){
		$mobile = I('post.mobile','','trim');
		$ret = array('code' => -1, 'msg'=>'unkown error');
		$reg='/^0?1[3|4|5|7|8][0-9]\d{8}$/';
		if(!preg_match($reg, $mobile)){
			$ret['msg'] = '手机号码有误';
			$this->ajaxReturn($ret);
		}
		$result = $this->checkMember($mobile);
		if($result[0] != 100){
			$ret['msg'] = '未获取到手机号信息';
			$this->ajaxReturn($ret);
		}
		if($result[1] == 'false'){
			$ret['msg'] = '您的账号不存在';
		}else{
			if($result[2] == 'true'){
				$ret['code'] = 1001;
				$ret['msg'] = '该手机号码已激活，请登录';
			}else{
				$ret['code'] = 1;
				$ret['msg'] = 'success';
			}
		}
		$this->ajaxReturn($ret);
	}
	//校验验证码，acTask使用
	public function checkActivateCode(){
		$mobile = I('post.mobile','','trim');
		$code = I('post.code','');
		$ret = array('code'=>-1, 'msg'=>'unkown error');
		$reg='/^0?1[3|4|5|7|8][0-9]\d{8}$/';
		
		if(!preg_match($reg, $mobile)){
			$ret['msg'] = '手机号有误';
			$this->ajaxReturn($ret);
		}
		if(empty($code)){
			$ret['msg'] = '您的验证码为空';
			$this->ajaxReturn($ret);
		}
		if(strlen($code) != 6){
			$ret['msg'] = '您的验证码有误';
			$this->ajaxReturn($ret);
		}
		$activateCode = session($mobile.'activateCode');
		if(empty($activateCode)){
			$activateCode=cookie($mobile.'activateCode');
		}
		if(empty($activateCode)){
			$ret['msg'] = '您的验证码不存在，请重新获取';
			$this->ajaxReturn($ret);
		}
		if($activateCode == md5(C('SHOP_KEY').$code)){
			$ret['code'] = 1;
			$ret['msg'] = 'success';
		}else{
			$ret['msg'] = '您输入的验证码有误，请核对';
		}
		$this->ajaxReturn($ret);
	}
	
	//激活时验证用户账号
	public function checkActivate(){
		$mobile = I('get.mobile');		
		if(empty($mobile)){
			echo json_encode(array(-1,'手机号码为空','-1'));
			exit;
		}
		$res=$this->checkMember($mobile);
		if($res[0]==100){
			if($res[1]=='false'){
				echo json_encode(array(-2,'您的账号不存在','-2'));
				exit;
			}
			if($res[1]=='true' && $res[2]=='true'){
				echo json_encode(array(-3,'该手机号码已激活，请直接登录','-3'));
				exit;
			}
			$activateCode=rand(100000,999999);
			session($mobile.'activateCode',md5(C('SHOP_KEY').$activateCode));
			cookie($mobile.'activateCode',md5(C('SHOP_KEY').$activateCode));
			cookie('activateMobile',md5(C('SHOP_KEY').$mobile));
			
			$sres=A('Sms')->send($mobile,'您的激活验证码为：'.$activateCode);
			echo json_encode(array(100,'手机验证码已发送'));
		}else{
			echo json_encode($res);
		}
	}	
	
	//用户激活
	public function activate(){
		$mobile = I('post.mobile');
		$password = I('post.password');
		$activateCode = I('post.checkCode');
		$from = I('post.from');
		if(empty($mobile) or empty($password) or empty($activateCode)){
			echo json_encode(array(-2,'必要参数为空','-2'));
			exit;
		}
		$checkMobile=cookie('activateMobile');
		if(!empty($checkMobile) && $checkMobile!=md5(C('SHOP_KEY').$mobile)){
			echo json_encode(array(-3,'手机号码非法','-3'));
			exit;
		}
		
 		$activateCheck=session($mobile.'activateCode');
 		if(empty($activateCheck)){
 			$activateCheck=cookie($mobile.'activateCode');
 		}
 		session($mobile.'activateCode',NULL);
 		cookie($mobile.'activateCode',NULL);
		
 		if($activateCheck!=md5(C('SHOP_KEY').$activateCode)){			
 			echo json_encode(array(-4,'手机验证码不正确','-4'));
 			exit;
 		}
		$data=array(
			'login_pwd'=>$password,
			'phone_num'=>$mobile,
		);
		if($from == 'acTask'){ //链接为U('Sign/acTask')的激活
			$data['flag'] = 'newTask';
		}
		//else{
			//$sign = md5('login_pwd='.$password.'&phone_num='.$mobile.C('API_KEY')); 
			//旧版签名， 暂时保留，以下为新方法
			
		//}
		$sign = apiSign($data);
		$data['sign'] = $sign;
        $res=$this->requestPost(C('API').'mallUser/activateUser',$data);
        $return=json_decode($res,TRUE);
        if($return['result']==100){
        	if($return['errcode']===0){
	        	$data=$return['data']['info'];        	
	        	$balance=array(
	        		'deposit'=>$data['balance']/100,
	        		'balance'=>$data['balance'],
	        		'commonAmount'=>$data['commonAmount'],
	        		'limitAmount'=>$data['limitAmount'],
	        		'comId'=>$data['comId'],
	        		'comName'=>$data['comName']
	        	);
	        	//查询返回地址
				$where['com_id'] = $data['comId'];
				$markInfo=$this->modelCompanyCfg->field('refer,index')->where($where)->find();
				$refer = urlencode($markInfo['refer']);
	        	
	        	$condition['mobile']=$userName;
				$checkUser=$this->userAccountModel->field('user_id')->where($condition)->find();
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
		        	$userId=$this->register($user,$info,$balance);
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
	        		'index'=>urlencode($markInfo['index']),
	        		'refer'=>urlencode($markInfo['index'])
	    		);
	    		session('account',$account['member']);
	    		cookie('account',json_encode($account));
	    		cookie('LSUID',$userId);
	    		cookie('UNAME',$mobile);
				
				echo json_encode(array(100,'激活成功！',$userId, $refer));
			}else{
				echo json_encode(array(-1,$return['msg'],$return['errcode']));
			}
		}else{
			echo json_encode(array(-1,$return['msg'],$return['errcode']));
		}
	}
	
	//找回密码发送验证码
	public function getPassCheckCode(){
		$mobile = I('get.mobile');		
		if(empty($mobile)){
			echo json_encode(array(-1,'手机号码为空','-1'));
			exit;
		}
		$res=$this->checkMember($mobile);
		if($res[0]==100){
			if($res[1]=='false'){
				echo json_encode(array(-2,'您的账号不存在','-2'));
				exit;
			}
			if($res[1]=='true' && $res[2]=='false'){
				echo json_encode(array(-3,'该手机号码未激活，请先激活','-3'));
				exit;
			}
			$checkCode=rand(100000,999999);
			session($mobile.'getPassCode',md5(C('SHOP_KEY').$checkCode));
			cookie($mobile.'getPassCode',md5(C('SHOP_KEY').$checkCode));
			cookie('checkMobile',md5(C('SHOP_KEY').$mobile));
			
			$sres=A('Sms')->send($mobile,'您的手机验证码为：'.$checkCode);
			echo json_encode(array(100,'手机验证码已发送'));
		}else{
			echo json_encode($res);
		}
	}	
	
	//找回密码
	public function getPassWord(){
		$mobile = I('post.mobile');
		$password = I('post.password');
		$checkCode=I('post.checkCode');
		if(empty($mobile) or empty($password) or empty($checkCode)){
			echo json_encode(array(-2,'必要参数为空','-2'));
			exit;
		}
		$checkMobile=cookie('checkMobile');
		if(!empty($checkMobile) && $checkMobile!=md5(C('SHOP_KEY').$mobile)){
			echo json_encode(array(-3,'手机号码非法','-3'));
			exit;
		}
		
		$mobileCheckCode=session($mobile.'getPassCode');
		if(empty($mobileCheckCode)){
			$mobileCheckCode=cookie($mobile.'getPassCode');
		}
		session($mobile.'getPassCode',NULL);
		cookie($mobile.'getPassCode',NULL);
		
		if($mobileCheckCode!=md5(C('SHOP_KEY').$checkCode)){			
			echo json_encode(array(-4,'手机验证码不正确','-4'));
			exit;
		}		
		
        $sign=md5('doType=fg&newPass='.$password.'&phoneNum='.$mobile.C('API_KEY'));
		$data=array(
        	'doType'=>'fg',
        	'newPass'=>$password,
        	'phoneNum'=>$mobile,
        	'sign'=>$sign
        );
        $res=$this->requestPost(C('API').'mallUser/updatePass',$data);
		$return=json_decode($res,TRUE);
		if($return['result']==100 && $return['errcode']==0){
			echo json_encode(array(100,'密码重置成功，请重新登录！',100));				
		}else{
			echo json_encode(array(-1,$return['msg'],$return['errcode']));
		}
	}

	//本地注册
	public function register($account,$info,$balance){
		$account['createtime']=time();
		$account['modified_time']=time();
		$userId=$this->userAccountModel->add($account);
		if($userId>0){
			$info['user_id']=$userId;
			$this->userModel->add($info);
			$balance['user_id']=$userId;
			$this->modelDeposit->add($balance);
		}
		return $userId;
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
	
	//检测用户的注册和激活状态
	public function checkMember($mobile){
		$sign=md5('phone_num='.$mobile.C('API_KEY'));
		$data=array(
        	'phone_num'=>$mobile,
        	'sign'=>$sign
        );
        $res=$this->requestPost(C('API').'mallUser/checkUser',$data);
        $return=json_decode($res,TRUE);
        if($return['result']==100 && $return['errcode']==0){
			$data=$return['data']['info'];
			return array(100,$data['isRegister'],$data['isActive']);
		}else{
			return array(-1,$return['msg'],$return['errcode']);
		}
	}
	
}
