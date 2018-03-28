<?php
namespace Home\Controller;
class UserController extends CommonController {
	public function __construct(){
		parent::__construct();
		$this->modelDeposit=M('sysuser_user_deposit');
	}
	
	//用户注册
	public function signupMember(){
		$mobile = I('post.mobile');
		$password = I('post.password');
		$imgCode = I('post.imgCode');		
		$project=I('post.project');
		if(empty($mobile)){
			echo json_encode(array(-1,'手机号码为空','-1'));
			exit;
		}
		if(!empty($project) && $project ="wshop"){
			
		}else{
			if(strtolower(session('imgCode'))!=strtolower($imgCode)){
				echo json_encode(array(-2,'图片验证码不正确','-2'));
				exit;
			}
			
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
//	public function activate(){
//		$this->display();
//	}
	
	//激活时验证用户账号
	public function checkActivate(){
		$mobile = I('get.mobile');
		$imgCode = I('get.imgCode');		
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
	
	//检测手机是否注册
	//*********不安全*********
	public function checkMobile(){
		$mobile = I('post.mobile','','trim,strip_tags,stripslashes');
		$ret = array('code'=>-1, 'msg'=>'unkown error');
		if(empty($mobile)){
			$ret['code'] = 1001;
			$ret['msg'] = 'empty mobile';
			$this->ajaxReturn($ret);
		}
		$reg = '/^1\d{10}$/';
		if(!preg_match($reg, $mobile)){
			$ret['code'] = 1002;
			$ret['msg'] = 'invalid mobile';
		}
		$result = $this->checkMember($mobile);
		if($result[0] != 100){//调用接口失败
			$ret['code'] = 1003;
			$ret['msg'] = 'sys error';
			$this->ajaxReturn($ret);
		}
		if($result[1] != 'true'){ //是否存在
			$ret['code'] = 1004;
			$ret['msg'] = 'not exist';
			$this->ajaxReturn($ret);
		}
		if($result[2] != 'true'){//
			$ret['code'] = 1005;
			$ret['msg'] = 'not activate';
		}else{
			$ret['code'] = 1;
			$ret['msg'] = 'not activate';
		}
		$this->ajaxReturn($ret);
	}
}