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
			$referUrl=$this->refer;
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
		if(!empty($refer)){
			$this->assign('refer',urldecode($refer));
		}				
		$this->display();
	}
	
	//用户登录
	public function sign(){
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
				//多种积分类型 详细
	      $balanceDetail = $data['userPointsList'];   
				//多种积分类型 详细	        	
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
		        	$userId=$this->register($user,$info,$balance,$balanceDetail);
				}else{
					$userId=$checkUser['user_id'];
					//更新积分
					$this->syncBalance($userId,$balance,$balanceDetail);
				}
				
        		$account=array(
	        		'id'=>$userId,
	        		'comId'=>$data['comId'],
	        		'account'=>$userName,
	        		'userName'=>$data['empName']
        		);
        		$condition['com_id']=$data['comId'];
				$userCompany=$this->modelCompanyCfg->field('com_id,refer,index')->where($condition)->find();
				$account['index']=urlencode($userCompany['index']);					
				$account['refer']=urlencode($userCompany['refer']);	
				
        		session('account',array('member'=>$account));   		 		     
				cookie('account',json_encode($account));
				cookie('LSUID',$userId);
		    	cookie('UNAME',$userName);
		    	if(empty($url)){
					$url=$account['refer'];
				}
        		echo json_encode(array($userId,'登录成功！',urldecode($url)));
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
						$balanceDetail = array(
							0 => array(
								'remainScore' => 0,
								'pointsChannel' => 'www',
								'pointName' => '商城通用积分',
								'pointTypeId' => 1
							)
						);							
						$this->syncBalance($userId,$balance,$balanceDetail);
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
		$activateCode=I('post.checkCode');
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
		
		$sign=md5('login_pwd='.$password.'&phone_num='.$mobile.C('API_KEY'));
		$data=array(
			'login_pwd'=>$password,
        	'phone_num'=>$mobile,
        	'sign'=>$sign
        );
        $res=$this->requestPost(C('API').'mallUser/activateUser',$data);
        $return=json_decode($res,TRUE);
        if($return['result']==100){
        	if($return['errcode']===0){
	        	$data=$return['data']['info'];  
				//多种积分类型 详细			
			      $balanceDetail = $data['userPointsList'];   
				//多种积分类型 详细						      	
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
		        	$userId=$this->register($user,$info,$balance,$balanceDetail);
		        }else{
					$userId=$checkUser['user_id'];
					//更新积分
					$this->syncBalance($userId,$balance,$balanceDetail);
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
				
				echo json_encode(array(100,'激活成功！',$userId,$refer));
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
	public function register($account,$info,$balance,$balanceDetail=array()){
		$account['createtime']=time();
		$account['modified_time']=time();
		$userId=$this->userAccountModel->add($account);
		if($userId>0){
			$info['user_id']=$userId;
			$this->userModel->add($info);
			$balance['user_id']=$userId;
			$this->modelDeposit->add($balance);
			//-----添加积分详情表
			if(!empty($balanceDetail)){
				foreach($balanceDetail as $key=>$val){
						$data = array(
							'user_id' => $userId,
							'deposit' => $val['remainScore']/100,
							'balance' => $val['remainScore'],
							'pointsChannel' => $val['pointsChannel'],
							'pointName' => $val['pointName'],
							'pointTypeId' => $val['pointTypeId'],
						);
						M('sysuser_user_deposit_detail')->data($data)->add();
				}			
			}
			//-----添加积分详情表			
		}
		return $userId;
	}
	
	//更新本地积分
	public function syncBalance($userId,$balance,$balanceDetail=array()){
		$checkBalance=$this->modelDeposit->field('user_id')->where('user_id='.$userId)->find();
		//-----添加积分详情表
		if(!empty($balanceDetail)){
			$modelPointDet = M('sysuser_user_deposit_detail');
			foreach($balanceDetail as $key=>$val){
					$data = array(
						'user_id' => $userId,
						'deposit' => $val['remainScore']/100,
						'balance' => $val['remainScore'],
						'pointsChannel' => $val['pointsChannel'],
						'pointName' => $val['pointName'],
						'pointTypeId' => $val['pointTypeId'],
					);
					$map = array(
						'user_id' => $userId,
						'pointTypeId' => $val['pointTypeId']
					);
					$isExist = $modelPointDet->where($map)->find();
					if($isExist){
						//更新该种积分
						$modelPointDet->where($map)->save($data);
					}else{
						//添加该种积分
						$modelPointDet->data($data)->add();
					}
			}
		}
		//-----添加积分详情表		
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
