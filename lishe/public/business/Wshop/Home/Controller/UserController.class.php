<?php
/**
  +------------------------------------------------------------------------------
 * UserController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: UserController.class.php v001 2016-06-02
 * @description 短信接口封装
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
class UserController extends CommonController {
	public function __construct(){
		parent::__construct();
		$this->modelDeposit=M('sysuser_user_deposit');
		$this->modelUser=M('sysuser_user');
		$this->modelDeposit=M('sysuser_user_deposit'); //用户积分表
		$this->modelAccount=M('sysuser_account'); //用户登录信息表
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
				echo json_encode(array(-4,'该手机号码已激活，请直接登录','-3'));
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
	
	public function checkActivateCode(){
		$mobile=I('get.mobile');
		$activateCode=I('get.checkCode');
		if(session($mobile.'activateCode')!=$activateCode){
			echo -1;
			exit;
		}
		session($mobile.'activateCode',NULL);
		echo 100;
	}
	
	//用户激活
	public function activateMember(){
		$mobile = I('post.mobile');
		$password = I('post.password');
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
			$markInfo=M('company_config')->field('mark')->where($where)->find();
			$mark = ucfirst($markInfo['mark']);//首字母大写
        	
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
        		'userName'=>$data['empName']
    		);
    		session('account',$account['member']);
    		cookie('mark',$mark);
    		cookie('account',json_encode($account));
    		cookie('LSUID',$userId);
    		cookie('UNAME',$mobile);
			
			echo json_encode(array(100,'激活成功！',$userId,$mark));
		}else{
			echo json_encode(array(-1,$return['msg'],$return['errcode']));
		}
	}
	
	//本地注册
	public function register($account,$info,$balance){
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
	
	//更新本地积分
	public function syncBalance($userId,$balance){
		$checkBalance=$this->modelDeposit->field('user_id')->where('user_id='.$userId)->find();
		if(empty($checkBalance['user_id'])){
			$balance['user_id']=$userId;
			return $this->modelDeposit->add($balance);
		}
		return $this->modelDeposit->where('user_id='.$userId)->save($balance);
	}

	//公共方法得到用户账号信息
	public function commonUserInfo(){
		if(empty($this->uid)){
            redirect(__APP__."/Login/login/index");
        }   
		$where=array(
			'user_id'=>$this->uid 
			);
		$userInfo=$this->modelUser->where($where)->field('name,username')->find();

		if($userInfo){
			return $userInfo;
		}else{
			return '';
		}
	}


	// 用户中心  20160729 start
	public function userCenter(){ 
		// var_dump($_SESSION);
	     		 
		$userInfo = $this->commonUserInfo();
		// $this->assign('userName',$userInfo['name']);

		$depositInfo=$this->modelDeposit->where('user_id='.$this->uid)->field('deposit')->find();
		if($depositInfo){
			$this->assign('depositInfo',floatval($depositInfo['deposit'])*100);
		}
		$this->display();
	}
	// 用户中心  20160729 end

	//修改基本资料 20160729 start
	public function userProfile(){
		$userInfo = $this->commonUserInfo();
		$this->assign('account',$this->account);
		$this->assign('userName',$userInfo['username']);
		$this->assign('Name',$userInfo['name']);
		$accountInfo=$this->modelAccount->where('user_id='.$this->uid)->field('email')->find();
		if($accountInfo){
			$this->assign('Email',$accountInfo['email']);
		}
		$this->display('profile');
	}
	//修改基本资料 20160729 end

	//修改资料信息 20160801 start
	public function userModProfile(){
		$name=I('get.name','','trim');
		$wxEmail=I('get.wxEmail','','trim');
		if($name==''){
			echo '1';
			exit;
		}
		$data1=array(
			'name'=>$name
			);
		$chgName=$this->modelUser->where('user_id='.$this->uid)->data($data1)->save();
		$data2=array(
			'email'=>$wxEmail
			);
   		$chgEmail=$this->modelAccount->where('user_id='.$this->uid)->data($data2)->save();
   		if($chgName || $chgEmail){
   			echo '2';
   			exit;
   		}
	}
	//修改资料信息 20160801 end

	//修改账户密码 20160729 start
	public function changePwd(){
		$userInfo = $this->commonUserInfo();
		$this->assign('account',$this->account);
		$this->assign('userName',$userInfo['username']);

		$this->display('chgpwd');
	}
	// 修改账户密码 20160729  end

	// 修改密码操作 20160803 start 
	public function changePw(){
		$oldPass=I('get.pwd','','trim');
		$newPass=I('get.npwd','','trim');
		$rePass=I('get.cpwd','','trim');
		if($oldPass==''){
			echo '-1';
			exit;
		}elseif($newPass==''){
			echo '-2';
			exit;
		}elseif(strlen($newPass) < 6 || strlen($newPass) > 18){
			echo '-3';
			exit;
		}elseif($newPass != $rePass){
			echo '-4';
			exit;
		}
		$phoneNumArr=$this->modelAccount->where('user_id='.$this->uid)->field('mobile')->find();
		$phoneNum=$phoneNumArr['mobile'];
		$sign=md5('doType=up&newPass='.$newPass.'&oldPass='.$oldPass.'&phoneNum='.$phoneNum.C('API_KEY'));
		$data=array(
			'phoneNum'=>$phoneNum,
			'oldPass'=>$oldPass,
			'doType'=>'up',
			'newPass'=>$newPass,
			'sign'=>$sign
			);
		$res=$this->requestPost(C('API').'mallUser/updatePass',$data);	
		// var_dump($res);
		$info=json_decode($res,TRUE);;
		// var_dump($info);
		if($info['result']==100){
			echo '1'; //修改成功
			exit;
		}else{
			echo '-5';
			exit;
		}

	}
	// 修改密码操作 20160803 end1

	// 客服 20160805 start
	public function customerServer(){
		$this->display('customServer');
	}
	// 客服 20160805 end
}