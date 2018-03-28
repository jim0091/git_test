<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[接口];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Home/Controller/];
 * +----------------------------------------------------------------------
 * |@Name:			[PayController.class.php];	
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3	
 * +----------------------------------------------------------------------
 * |@License:(http://www.apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-1-4 15:06
 * +----------------------------------------------------------------------
 * |@Email:		<Angelljoy@sina.com>		@OverTime:	2016
 * +----------------------------------------------------------------------
 *  */
namespace Home\Controller;
use Think\Cache\Driver\Redis;

use Think\Controller;
class LoginController extends Controller {
	public function __construct(){
		parent::__construct();
		$this->redis = new Redis();
// 		$this->userModel=M('sysuser_user');
// 		$this->userAccountModel=M('sysuser_account');//用户登录表
// 		$this->modelDeposit=M('sysuser_user_deposit');
// 		$this->modelCompanyCfg=M('company_config');
// 		$this->sysuser = D('SysuserUser');
// 		$this->modelUser=D('User');//订单主表
	}
	
	public function _initialize() {
		header ( "content-type:text/html;charset=utf-8" );
	}
	
	//注册
	public function sendRegisterVerifyCode(){
		$postData = I('post.');
		if(empty($postData)){
			$this->retError(-1,'请输入手机号');
		}else{
			$mobile = $postData['mobile'];
			$RegisterInfo = $this->checkMember($mobile);
			if($RegisterInfo['status']==1){
				//可以注册，开始发送短信验证码
				$Clent = new SignController();
				$isMobile = $Clent->isMobile($mobile);
				if($isMobile){
					$checkCode = rand(100000,999999);
					$RedisSet = $this->redis->set('RegisterCode:'.$mobile,md5(C('SHOP_KEY').$checkCode),900);
					if($RedisSet){
						$SendMsg = A('Sms')->send($mobile,'您正在注册账号，验证码'.$checkCode.'，我们欢迎您。');
						if($SendMsg['result']==0){
							$result['status'] = 1;
							$result['msg'] = "短信验证码发送成功";
							$this->retSuccess($result,$result['msg']);
						}else{
							$result['status'] = -1;
							$result['message'] = "服务繁忙，发送失败";
							$this->retError($result['status'],$result['msg']);
						}
					}else{
						$this->retError(-4,"请输入手机号");
					}
				}else{
					$this->retError(-3,"请输入正确的手机号");
				}
			}else{
				$this->retError($RegisterInfo['status'],$RegisterInfo['message']);
			}
		}
	}
	
	
	//注册——比对短信验证码
	public function checkRegisterMsgVerifyCode(){
		$postData = I('post.');
		if(!empty($postData)){
			$mobile = $postData['mobile'];
			$verify = $postData['verify'];
			if(empty($mobile)){
				$this->retError(-2,"手机号不能为空");
			}else{
				if(empty($verify)){
					$this->retError(-3,"验证码不能为空");
				}else{
					$mobileCheckCode = $this->redis->get('RegisterCode:'.$mobile);
					if(empty($mobileCheckCode)){
						$this->retError(-4,"验证码已失效，请重新发送验证码");
						exit;
					}else{
						if($mobileCheckCode!=md5(C('SHOP_KEY').$verify)){
							$this->retError(-5,"手机验证码不正确");
							exit;
						}else{
							$this->redis->rm('RegisterCode:'.$mobile);
							$result['status'] = 1;
							$result['message'] = '可以进行下一步操作，去设置密码';
							$this->retSuccess($result);
						}
					}
				}
			}
		}else{
			$this->retError(-1,"服务繁忙");
		}
	}
	
	
	//注册第三步，两次输入密码
	public function memberRegister(){
		$postData = I('post.');
		if(!empty($postData)){
			$mobile = $postData['mobile'];
			$Password = $postData['password'];
			$affirmPassword = $postData['affirmPassword'];
			if(empty($mobile)){
				$this->retError(-2,"手机号不能为空");
			}else{
				if(empty($Password)){
					$this->retError(-2,"请输入新密码");
				}else{
					if(empty($affirmPassword)){
						$this->retError(-3,"请输入确认密码");
					}else{
						if($affirmPassword!=$Password){
							$this->retError(-4,"两次输入密码不一致");
						}else{
							$sign=md5('login_pwd='.$Password.'&phone_num='.$mobile.C('API_KEY'));
				            $data=array(
				                'login_pwd'=>$Password,
				                'phone_num'=>$mobile,
				                'sign'=>$sign
				            );
				            $res=$this->requestPost(C('API').'mallUser/register',$data);
				            //$res = '{"result":100,"errcode":0,"msg":"success","data":{"info":{"phoneNum":"18377890162","comId":"-1","comName":"VR企业","userId":123452492}}}';
							$return=json_decode($res,TRUE);
							$balance=array();
							if($return['result']==100){
								if($return['errcode']==0){
									$data1=$return['data']['info'];
									if(empty($data1['empName'])){
										$data1['empName']=$mobile;
									}
									//本地注册用户
									$user=array(
											'login_account'=>$mobile,
											'mobile'=>$mobile,
											'login_password'=>'activate'
									);
									$info=array(
											'ls_user_id'=>$data1['userId'],
											'name'=>$data1['empName'],
											'username'=>$data1['empName']
									);
									$userId=$this->register($user,$info,$balance);
									if(!empty($userId)){
										//更新积分
										$balance=array(
												'deposit'=>0,
												'balance'=>0,
												'commonAmount'=>0,
												'limitAmount'=>0,
												'comId'=>$data1['comId'],
												'comName'=>$data1['comName'],
												'isHr' => $data['isHr'],
												'department' => $data['departName']
										);
										$this->syncBalance($userId,$balance);
										//同步登陆
										$account=array(
												'id'=>$userId,
												'account'=>$mobile,
												'userName'=>$data1['empName']
										);
										$token= D('SysuserUser')->makeToken($userId,$Password);
										$this->redis->set($token,$account);
										$account['token']=$token;
										$this->retSuccess($account,'注册成功，礼舍欢迎您！');
									}else{
										$this->retError(-4,"本地注册失败，请联系管理员！");
									}
								}else{
									$this->retError(-5,$return['msg']);
								}
						}else{
							$this->retError(-6,$return['msg']);
						}
					}
					}
				}
			}
		}else{
			$this->retError(-1,"请输入密码");
		}
	}
	
	//本地注册
	public function register($account,$info,$balance){
		$account['createtime']=time();
		$account['modified_time']=time();
		$userAccountModel = M('sysuser_account');//用户登录表
		$userModel=M('sysuser_user');
		$userId=$userAccountModel->add($account);
		if($userId>0){
			$info['user_id']=$userId;
			$a = $userModel->add($info);
			$balance['user_id']=$userId;
			$modelDeposit=M('sysuser_user_deposit');
			$modelDeposit->add($balance);
		}
		return $userId;
	}
	
	//更新本地积分
	public function syncBalance($userId,$balance){
		$modelDeposit=M('sysuser_user_deposit');
		$checkBalance=$modelDeposit->field('user_id')->where('user_id='.$userId)->find();
	
		if(empty($checkBalance['user_id'])){
			$balance['user_id']=$userId;
			return $modelDeposit->add($balance);
		}
		return $modelDeposit->where('user_id='.$userId)->save($balance);
	}
	
	//检测用户的注册和激活状态
	public function checkMember($mobile){
		$sign=md5('phone_num='.$mobile.C('API_KEY'));
		$data['phone_num'] = $mobile;
		$data['sign'] = $sign;
		$res=$this->requestPost(C('API').'mallUser/checkUser',$data);
		$return=json_decode($res,TRUE);
		if($return['result']!=100){//一企一舍接口通讯失败
			$callback['status'] = -1;
			$callback['message'] = "服务繁忙，注册失败";
			return $callback;
		}else{
			if(empty($return['data'])){
				$callback['status'] = 0;
				$callback['message'] = "服务繁忙，注册失败";
				return $callback;
			}else{
				$data=$return['data']['info'];
				if($data['isRegister']=='false'){
					$callback['status'] = 1;
					$callback['message'] = "账号不存在，可以注册";
					return $callback;
				}else{
					if($data['isActive']=='true'){
						$callback['status'] = 2;
						$callback['message'] = "您的账号是激活的，可以直接登录，不用再注册了";
						return $callback;
					}else{
						if($data['isActive']=='false'){
							$callback['status'] = 3;
							$callback['message'] = "您的账号激活即可使用，不用注册";
						}else{
							$callback['status'] = 0;
							$callback['message'] = "服务繁忙";
						}
					}
				}
			}
		}
	}
	
 	//检测用户的注册和激活状态
    public function checkMemberPost($mobile){
        if(empty($mobile)){
            $mobile=I("post.mobile");
        }
        $sign=md5('phone_num='.$mobile.C('API_KEY'));
        $data=array(
            'phone_num'=>$mobile,
            'sign'=>$sign
        );
        $res=$this->requestPost(C('API').'mallUser/checkUser',$data);
        $return=json_decode($res,TRUE);
        if($return['result']==100 && $return['errcode']==0){
            $data=$return['data']['info'];
            $msg="已激活";
            if($data['isRegister']=="false"){
                $msg="未激活";
            }
            $this->retSuccess($data,$msg);
        }else{
            $this->retSuccess($return['msg'],"检查出错");
        }
    }
	
	
	
	//接口返回结果
	protected function retSuccess($data=array(),$msg='操作成功'){
		$ret=array(
				'result'=>100,
				'errcode'=>0,
				'msg'=>$msg,
				'data'=>$data
		);
		echo json_encode($ret);
		exit;
	}
	
	//接口返回错误信息
	protected function retError($errCode=1,$msg='操作失败'){
		$ret=array(
				'result'=>100,
				'errcode'=>$errCode,
				'msg'=>$msg
		);
		echo json_encode($ret);
		exit;
	}
	//模拟提交
	public function requestPost($url='', $data=array()) {
		if(empty($url) || empty($data)){
			return false;
		}
		$o="";
		foreach($data as $k=>$v){
			$o.="$k=".$v."&";
		}
		$param=substr($o,0,-1);
		$ch=curl_init();//初始化curl
		curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
		curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
		curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
		curl_setopt($ch,CURLOPT_POSTFIELDS, $param);
		$return=curl_exec($ch);//运行curl
		curl_close($ch);
		return $return;
	}
}