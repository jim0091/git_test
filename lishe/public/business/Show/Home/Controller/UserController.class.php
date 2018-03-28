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
		if(!empty($this->uid)){
			header("location:/member-index.html");
		}
		$this->modelDeposit=M('sysuser_user_deposit');
		$this->modelCompanyCfg=M('company_config');
	}
	
	//用户注册
	public function signup(){		
		$this->display();
	}
	
	public function signupMember(){
		$mobile = I('post.mobile');
		$password = I('post.password');
		$imgCode = I('post.imgCode');		
		$project=I('post.project');
		if(empty($mobile)){
			echo json_encode(array(-1,'手机号码为空','-1'));
			exit;
		}
		if(empty($project)){
			$checkImgCode = session('imgCode');
			if(strtolower($checkImgCode)!=strtolower($imgCode)){
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
	public function activate(){
		$this->display();
	}
	
	//激活时验证用户账号
	public function checkActivate(){
		$mobile = I('get.mobile');
		$imgCode = I('get.imgCode');		
		if(empty($mobile)){
			echo json_encode(array(-1,'手机号码为空','-1'));
			exit;
		}
		//if(strtolower(session('imgCode'))!=strtolower($imgCode)){
			//echo json_encode(array(-2,'图片验证码不正确','-2'));
			//exit;
		//}
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
			session($mobile.'activateCode',md5($activateCode));
			cookie($mobile.'activateCode',md5($activateCode));
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
		$activateCheck=session($mobile.'activateCode');
		if(empty($activateCheck)){
			$activateCheck=cookie($mobile.'activateCode');
		}
		if($activateCheck!=md5($activateCode)){
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
        	//查询返回地址
			$where['com_id'] = $data['comId'];
			$markInfo=$this->modelCompanyCfg->field('refer,index')->where($where)->find();
			$refer = urlencode($markInfo['refer']);
        	
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
        		'userName'=>$data['empName'],
        		'index'=>urlencode($markInfo['index']),
        		'refer'=>urlencode($markInfo['index'])
    		);
    		cookie('refer',$refer);
    		cookie('account',json_encode($account));
    		session('account',json_encode($account));
    		cookie('LSUID',$userId);
    		cookie('UNAME',$mobile);
			
			echo json_encode(array(100,'激活成功！',$userId,$refer));
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
}