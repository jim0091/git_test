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
use Think\Controller;
class CmsController extends CommonController {
	public function _initialize() {
		header ( "content-type:text/html;charset=utf-8" );
	}
	
	
	
	//发送修改密码的短信验证码   lihongqiang
	public function sendAmendMsgVerifyCode(){
		$uid = $this->uid;
		$account = $this->account;
		if(!$uid){
			$this->retError(-1,"请登录");
		}else{
			$postData = I('post.');
			if(empty($postData)){
				$this->retError(-2,"参数缺失");
			}else{
				$mobile = $postData['mobile'];
				if(empty($mobile)){
					$this->retError(-3,"请输入手机号");
				}else{
					if($mobile!=$account){
						$this->retError(-4,"请输入您正在登录的账号");
					}else{
						$Clent = new SignController();
						$isMobile = $Clent->isMobile($mobile);
						if($isMobile){
							$checkCode = rand(100000,999999);
							$RedisSet = $this->redis->set('amendPassCode:'.$mobile,md5(C('SHOP_KEY').$checkCode),900);
							if($RedisSet){
								$SendMsg = A('Sms')->send($mobile,'您正在修改密码，验证码'.$checkCode.'，请在5分钟内按页面提示提交验证码，切勿将验证码泄露于他人。');
								if($SendMsg['result']==0){
									$result['status'] = 1;
									$result['msg'] = "短信验证码发送成功";
									$this->retSuccess($result,$result['msg']);
								}else{
									$result['status'] = -7;
									$result['message'] = "服务繁忙，发送失败";
									$this->retError($result['status'],$result['msg']);
								}
							}else{
								$this->retError(-5,"请输入手机号");
							}
						}else{
							$this->retError(-6,"请输入正确的手机号");
						}	
					}
				}
			}
		}
	}
			
	//修改密码，比对验证码
	public function checkAmendMsgVerifyCode(){
		$uid = $this->uid;
		if($uid){
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
						$mobileCheckCode = $this->redis->get('amendPassCode:'.$mobile);
						if(empty($mobileCheckCode)){
							$this->retError(-4,"验证码已失效，请重新发送验证码");
							exit;
						}
						if($mobileCheckCode!=md5(C('SHOP_KEY').$verify)){
							$this->retError(-5,"短信验证码不正确");
							exit;
						}else{
							$this->redis->rm('amendPassCode:'.$mobile);
							$result['status'] = true;
							$result['message'] = '可以进行下一步操作，去修改密码';
							$this->retSuccess($result);
						}
					}
				}
			}else{
				$this->retError(-1,"参数缺失");
			}
		}else{
			$this->retError(-100,"请登录");
		}
	}	
	
	
	
	//设置登录密码
	public function setLoginPassword(){
		$uid = $this->uid;
		if($uid){
			$postData = I('post.');
			if(empty($postData)){
				$this->retError(-1,"参数缺失");
			}else{
				$mobile = $postData['mobile'];
				$where['mobile'] = $mobile;
				$userAccountObj = M('sysuser_account');
				$findUser = $userAccountObj->where($where)->find();
				if($findUser){
					$newPassword = $postData['newPassword'];
					$affirmPassword = $postData['affirmPassword'];
					if(empty($newPassword)){
						$this->retError(-2,"请输入新密码");
					}else{
						if(empty($affirmPassword)){
							$this->retError(-3,"请输入确认密码");
						}else{
							if($affirmPassword!=$newPassword){
								$this->retError(-4,"两次输入密码不一致");
							}else{
								$sign=md5('doType=fg&newPass='.$newPassword.'&phoneNum='.$mobile.C('API_KEY'));
								$data=array(
										'doType'=>'fg',
										'newPass'=>$newPassword,
										'phoneNum'=>$mobile,
										'sign'=>$sign
								);
								$res=$this->requestPost(C('API').'mallUser/updatePass',$data);
								$return=json_decode($res,TRUE);
								if($return['result']==100 && $return['errcode']==0){
									$result['status'] = 1;
									$result['message'] = '密码修改成功！';
									$this->retSuccess($result,$result['message']);
								}else{
									$this->retError(-100,$return['msg']);
								}
							}
						}
					}
				}else{
					$this->retError(-5,"系统没有这个账号");
				}
			}
		}else{
			$this->retError(-10,"请重新登录");
		}
	}
	
    
	//修改支付密码发送验证码
	public function sendAmendPayMsgVerifyCode(){
		$uid = $this->uid;
		$account = $this->account;
		if(!$uid){
			$this->retError(-1,"请登录");
		}else{
			$postData = I('post.');
			if(empty($postData)){
				$this->retError(-2,"参数缺失");
			}else{
				$mobile = $postData['mobile'];
				if(empty($mobile)){
					$this->retError(-3,"请输入手机号");
				}else{
					if($mobile!=$account){
						$this->retError(-4,"请输入您正在登录的账号");
					}else{
						$Clent = new SignController();
						$isMobile = $Clent->isMobile($mobile);
						if($isMobile){
							$checkCode = rand(100000,999999);
							$RedisSet = $this->redis->set('amendPayCode:'.$mobile,md5(C('SHOP_KEY').$checkCode),900);
							if($RedisSet){
								$SendMsg = A('Sms')->send($mobile,'您正在修改支付密码，验证码'.$checkCode.'，请在5分钟内按页面提示提交验证码，切勿将验证码泄露于他人。');
								if($SendMsg['result']==0){
									$result['status'] = 1;
									$result['msg'] = "短信验证码发送成功";
									$this->retSuccess($result,$result['msg']);
								}else{
									$result['status'] = -7;
									$result['message'] = "服务繁忙，发送失败";
									$this->retError($result['status'],$result['msg']);
								}
							}else{
								$this->retError(-5,"请输入手机号");
							}
						}else{
							$this->retError(-6,"请输入正确的手机号");
						}	
					}
				}
			}
		}
	}
    
	//修改支付密码，比对验证码
	public function checkAmendPayMsgVerifyCode(){
		$uid = $this->uid;
		if($uid){
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
						$mobileCheckCode = $this->redis->get('amendPayCode:'.$mobile);
						if(empty($mobileCheckCode)){
							$this->retError(-4,"验证码已失效，请重新发送验证码");
							exit;
						}
						if($mobileCheckCode!=md5(C('SHOP_KEY').$verify)){
							$this->retError(-5,"短信验证码不正确");
							exit;
						}else{
							$this->redis->rm('amendPayCode:'.$mobile);
							$result['status'] = true;
							$result['message'] = '可以进行下一步操作，去修改支付密码';
							$this->retSuccess($result);
						}
					}
				}
			}else{
				$this->retError(-1,"参数缺失");
			}
		}else{
			$this->retError(-100,"请登录");
		}
	}
    
	//设置支付密码
	public function setPayPassword(){
		$uid = $this->uid;
		if($uid){
			$postData = I('post.');
			if(empty($postData)){
				$this->retError(-1,"参数缺失");
			}else{
				$mobile = $postData['mobile'];
				$where['user_id'] = $uid;
				$userAccountObj = M('sysuser_account');
				$findUser = $userAccountObj->where($where)->find();
				if($findUser){
					if($findUser['mobile']!=$mobile){
						$this->retError(-6,"非法操作");
					}else{
						$newPayPassword = $postData['newPayPassword'];
						$affirmPayPassword = $postData['affirmPayPassword'];
						if(empty($newPayPassword)){
							$this->retError(-2,"请输入新密码");
						}else{
							if(empty($affirmPayPassword)){
								$this->retError(-3,"请输入确认密码");
							}else{
								if($affirmPayPassword!=$newPayPassword){
									$this->retError(-4,"两次输入密码不一致");
								}else{
									$sysuser_user_deposit = M('sysuser_user_deposit');//积分表
									$data['md5_password'] = md5($newPayPassword);
									$condition['user_id'] = $uid;
									$findData = $sysuser_user_deposit->where($condition)->find();
									if($findData){
										if($findData['md5_password']==md5($newPayPassword)){
											//新密码跟原密码一样
											$this->retError(-100,"新密码不能跟原密码相同");
										}else{
											$bool = $sysuser_user_deposit->where($condition)->save($data);
											if($bool){
												$result['status'] = 1;
												$result['message'] = '支付密码修改成功！';
												$this->retSuccess($result,$result['message']);
											}else{
												$this->retError(-20,"服务繁忙，支付密码修改失败");
											}
										}
									}else{
										$this->retError(-15,"服务繁忙");
									}
								}
							}
						}
					}
				}else{
					$this->retError(-5,"系统故障，修改失败");
				}
			}
		}else{
			$this->retError(-10,"请重新登录");
		}
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

	
	
	//接口返回错误信息
	public function retError($errCode=1,$msg='操作失败'){
		$ret=array(
				'result'=>100,
				'errcode'=>$errCode,
				'msg'=>$msg
		);
		echo json_encode($ret);
		exit;
	}
	
	//接口返回结果
	public function retSuccess($data=array(),$msg='操作成功'){
		$ret=array(
				'result'=>100,
				'errcode'=>0,
				'msg'=>$msg,
				'data'=>$data
		);
		echo json_encode($ret);
		exit;
	}
	
}

