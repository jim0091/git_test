<?php
namespace Home\Controller;
use Think\Controller;
use Think\Cache\Driver\Redis;

class SignController extends Controller{
    public function __construct(){
        parent::__construct();
        $this->redis=new Redis();
        $this->userModel=M('sysuser_user');
        $this->userAccountModel=M('sysuser_account');//用户登录表
        $this->modelDeposit=M('sysuser_user_deposit');
        $this->modelCompanyCfg=M('company_config');
        $this->sysuser = D('SysuserUser');
        $this->modelUser=D('User');//订单主表
    }


    public function index(){
        var_dump($this->uid);
    }

    //登录接口
    public function login(){
    	$postData = I('post.');
    	$userName =$postData['userName'];
    	$password = $postData['password'];
        if (empty($userName)) {
            $this->retError("1","用户名不能为空");
        }
        if (empty($password)) {
            $this->retError("2","密码不能为空");
        }
        $sign = md5('login_pwd=' . $password . '&phone_num=' . $userName . C('API_KEY'));
        $data = array(
            'phone_num' => $userName,
            'login_pwd' => $password,
            'sign' => $sign
        );
        
        //$login = $this->requestPost('http://192.168.1.186:8080/lshe.framework.protocol.http/api/mallUser/empLogin', $data);
        $login = $this->requestPost(C('API') . 'mallUser/empLogin', $data);
       
        $uclogin = json_decode($login, TRUE);
        $data = $uclogin['data']['info'];
        if (empty($data['userId'])) {
            $this->retError("-1",$uclogin['msg']);
        } else {
        	
            //更新本地信息
            $balance = array(
                'deposit' => $data['balance'] / 100,
                'balance' => $data['balance'],
                'commonAmount' => $data['commonAmount'],
                'limitAmount' => $data['limitAmount'],
                'comId' => $data['comId'],
                'comName' => $data['comName'],
            	//'isHr' => $data['isHr'],
            	'department' => $data['departName']
            );
            $condition['mobile'] = $userName;
            $checkUser = $this->userAccountModel->field('user_id')->where($condition)->find();
//             exit;
            
            if (empty($checkUser['user_id'])) {
                //如果没有发现本地信息，注册用户
                $user = array(
                    'login_account' => $userName,
                    'mobile' => $userName,
                    'login_password' => 'sync'
                );
                $info = array(
                    'ls_user_id' => $data['userId'],
                    'name' => $data['empName'],
                    'username' => $data['empName']
                );
                $userId = $this->register($user, $info, $balance);
            } else {
            	
                $userId = $checkUser['user_id'];
                
                //更新积分
               	$a =  $this->syncBalance($userId, $balance);
            }
            $token=$this->sysuser->makeToken($data['userId'],$password);//生成token
            
            $info['id'] = $userId;
            $info['comId'] = $data['comId'];
            $info['account'] = $userName;
            $info['userName'] = $data['empName'];
           	$this->redis->set($token,$info);
            $info['token']=$token;
            $_SESSION['uid'] = $userId;
            $_SESSION['user_id'] = $userId;
            $_SESSION['userName'] = $data['empName'];
            $_SESSION['account'] = $userName;
            $_SESSION['com_id'] = $data['comId'];
            $_SESSION['token'] = $info['token'];
            //判断本地用户的userName和name是否为空,如果为空则把一企一舍返回的数据更新进去
            $sysuser_userObj = M('sysuser_user');
            $cond['user_id'] = $userId;
            $findUser_User = $sysuser_userObj->where($cond)->field('name,userName')->find();
            if(empty($findUser_User['userName'])||empty($findUser_User['name'])){
            	$save['userName'] = $userName;
            	$save['name'] = $data['empName'];
            	$bool = $sysuser_userObj->where($cond)->save($save);
            }
            $this->retSuccess($info,"登陆成功");
        }


    }
    //登出
    public function logout(){
        $token=I("post.token");
        if(empty($token)){
            $this->retError(-1,"token不能为空");
            exit;
        }else{
            $this->redis->rm($token);
            $this->retSuccess(array("status"=>true),"退出登录成功");
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
    //注册
    public function signup(){
        $mobile = I('post.mobile');
        $password = I('post.password');
        $checkCode=I('post.checkCode',0,'intval');
        if(empty($mobile)){
            $this->retError(-1,'手机号码为空');
            exit;
        }
        if($checkCode<100000||$checkCode>999999){
            $this->retError(-2,'验证码输入长度有误');
            exit;
        }
        if(!$this->redis->get($mobile."activateCode")){
            $this->retError(-5,"验证码已失效");
            exit;
        }
        $activateCheck=$this->redis->get($mobile.'activateCode');
        if(empty($activateCheck)){
            $this->retError(-6,"获取验证码失败！请重试");
            exit;
        }

        if($activateCheck!=md5(C('SHOP_KEY').$checkCode)){
            $this->retError(-4,"手机验证码不正确");
            exit;
        }
        $this->redis->rm($mobile."activateCode");
        $res=$this->checkMember($mobile);
        if($res[0]==100){
            if($res[1]=='true' && $res[2]=='true'){
                $this->retError(-3,'该手机号码已激活，请直接登录');
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
            $balance=array();
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
                            'comName'=>$data['comName'],
			            	//'isHr' => $data['isHr'],
			            	'department' => $data['departName']
                        );
                        $this->syncBalance($userId,$balance);
                        //同步登陆
                        $account=array(
                            'id'=>$userId,
                            'account'=>$mobile,
                            'userName'=>$data['empName']
                        );
                        $token=$this->sysuser->makeToken($userId,$password);
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
                $this->retError(-1,$return['msg']);
            }
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
        if($return['result']==100 && $return['errcode']==0){
            $data=$return['data']['info'];
            return array(100,$data['isRegister'],$data['isActive']);
        }else{
            return array(-1,$return['msg'],$return['errcode']);
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

    //激活时验证用户账号
    public function checkSignUp(){
        $mobile = I('post.mobile');
        if(empty($mobile)){
            $this->retError(-1,'手机号码为空');
            exit;
        }
        $activateCode=rand(100000,999999);
        $this->redis->set($mobile."activateCode",md5(C('SHOP_KEY').$activateCode),900);
        $sres=A('Sms')->send($mobile,'您的激活验证码为：'.$activateCode);
        $this->retSuccess(array('msg'=>'手机验证码已发送'));

    }

    //激活时验证用户账号
    public function checkActivate(){
        $mobile = I('post.mobile');
        if(empty($mobile)){
            $this->retError(-1,'手机号码为空');
            exit;
        }
        $res=$this->checkMember($mobile);
        if($res[0]==100){
            if($res[1]=='false'){
                $this->retError(-2,'您的账号不存在');
                exit;
            }
            if($res[1]=='true' && $res[2]=='true'){
                $this->retError(-3,'该手机号码已激活，请直接登录');
                exit;
            }
            $activateCode=rand(100000,999999);
            $this->redis->set($mobile."activateCode",md5(C('SHOP_KEY').$activateCode),900);
            $sres=A('Sms')->send($mobile,'您的激活验证码为：'.$activateCode);
            $this->retError(100,'手机验证码已发送');
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
            $this->retError(-2,"必要参数为空");
            exit;
        }
        if(!$this->redis->get($mobile."activateCode")){
            $this->retError(-5,"验证码已失效");
            exit;
        }
        $activateCheck=$this->redis->get($mobile.'activateCode');
        if(empty($activateCheck)){
            $this->retError(-6,"获取验证码失败！请重试");
            exit;
        }

        if($activateCheck!=md5(C('SHOP_KEY').$activateCode)){
            $this->retError(-4,"手机验证码不正确");
            exit;
        }
        $this->redis->rm($mobile."activateCode");
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
                $balance=array(
                    'deposit'=>$data['balance']/100,
                    'balance'=>$data['balance'],
                    'commonAmount'=>$data['commonAmount'],
                    'limitAmount'=>$data['limitAmount'],
                    'comId'=>$data['comId'],
                    'comName'=>$data['comName'],
	            	'isHr' => $data['isHr'],
	            	'department' => $data['departName']
                );
                //查询返回地址
                $where['com_id'] = $data['comId'];
                $markInfo=$this->modelCompanyCfg->field('refer,index')->where($where)->find();
                $refer = urlencode($markInfo['refer']);
                $condition['mobile']=$mobile;
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
                $token=$this->sysuser->makeToken($userId,$password);
                $this->redis->set($token,$account);
                $account['token']=$token;
                $this->retSuccess($account,'激活成功！');
            }else{
                $this->retError(-1,$return['msg']);
            }
        }else{
            $this->retError(-1,$return['msg']);
        }
    }

    //找回密码发送验证码
    public function getPassCheckCode(){
        $mobile = I('post.mobile');
        if(empty($mobile)){
            $this->retError(-1,"手机号为空");
            exit;
        }
        $res=$this->checkMember($mobile);
        if($res[0]==100){
            if($res[1]=='false'){
                $this->retError(-2,'您的账号不存在');
                exit;
            }
            if($res[1]=='true' && $res[2]=='false'){
                $this->retError(-3,'该手机号码未激活，请先激活');
                exit;
            }
            $checkCode=rand(100000,999999);
            $res=$this->redis->set($mobile.'getPassCode',md5(C('SHOP_KEY').$checkCode),900);
            if($res){
                $sres=A('Sms')->send($mobile,'您的手机验证码为：'.$checkCode);
                $this->retSuccess(array("status"=>true),"手机验证码已发送");
            }else{
                $this->retError(-4,"验证码发送失败");
            }
        }else{
            echo json_encode($res);
        }
    }
    
    
    
    //找回密码，设置新密码
    public function setLoginPassword(){
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
    							$this->retError(0,$return['msg']);
    						}
    					}
    				}
    			}
    		}else{
    			$this->retError(-5,"系统没有这个账号");
    		}
    	}
    }
    
    
    //发送忘记密码的短信验证码   lihongqiang
    public function sendRetrievedMsgVerifyCode(){
    	$postData = I('post.');
    	if(empty($postData)){
    		$this->retError(-1,"参数缺失");
    	}else{
    		$mobile = $postData['mobile'];
    		if(empty($mobile)){
    			$this->retError(-2,"请输入手机号");
    		}else{
    			$isMobile = $this->isMobile($mobile);
    			if($isMobile){
    				$checkCode = rand(100000,999999);
    				$RedisSet = $this->redis->set('retrievedPassCode:'.$mobile,md5(C('SHOP_KEY').$checkCode),900);
    				if($RedisSet){
    					$SendMsg = A('Sms')->send($mobile,'您正在重置密码，验证码'.$checkCode.'，请在5分钟内按页面提示提交验证码，切勿将验证码泄露于他人。');
    					if($SendMsg['result']==0){
    						$result['status'] = 1;
    						$result['msg'] = "短信验证码发送成功";
    						$this->retSuccess($result,$result['msg']);
    					}else{
    						$result['status'] = 0;
    						$result['message'] = "服务繁忙，发送失败";
    						$this->retError($result['status'],$result['msg']);
    					}
    				}else{
    					$this->retError(-4,"请输入手机号");
    				}
    			}else{
    				$this->retError(-3,"请输入正确的手机号");
    			}
    		}
    	}
    }
    
    //忘记密码——比对短信验证码
    public function checkRetrievedMsgVerifyCode(){
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
    				$mobileCheckCode = $this->redis->get('retrievedPassCode:'.$mobile);
    				if(empty($mobileCheckCode)){
    					$this->retError(-4,"验证码已失效，请重新发送验证码");
    					exit;
    				}else{
    					if($mobileCheckCode!=md5(C('SHOP_KEY').$verify)){
    						$this->retError(-5,"手机验证码不正确");
    						exit;
    					}else{
    						$this->redis->rm('retrievedPassCode:'.$mobile);
    						$result['status'] = 1;
    						$result['message'] = '可以进行下一步操作，去填写密码';
    						$this->retSuccess($result);
    					}
    				}
    			}
    		}
    	}else{
    		$this->retError(-1,"参数缺失");
    	}
    }
    
    
    
    
    /**
     * 验证手机号是否正确
     * @author: lihongqiang   lhq@lishe.cn
     * @param: number $mobile
     * @example:
     * 移动：134、135、136、137、138、139、150、151、152、157、158、159、182、183、184、187、188、178(4G)、147(上网卡)；
     * 联通：130、131、132、155、156、185、186、176(4G)、145(上网卡)；
     * 电信：133、153、180、181、189 、177(4G)；
     * 卫星通信：1349
     * 虚拟运营商：170
     */
    public function isMobile($mobile) {
    	if (!is_numeric($mobile)) {
    		return false;
    	}
    	if(preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile)){
    		return true;
    	}else{
    		return false;
    	}
    	//return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
    }
    
    
    
    public function test(){
    	$mobile = '13066899989';
    	//$res=$this->redis->set($mobile.'getPassCode');
    	var_dump($this->redis->get('retrievedPassCode:'.$mobile));
    	var_dump(md5(C('SHOP_KEY').'116177'));
//     	var_dump($res);
    }
    
    public function test3(){
    	$mobile = '13066899989';
    	$SendMsg = A('Sms')->send($mobile,'您正在重置密码，验证码'.'123456'.'，请在5分钟内按页面提示提交验证码，切勿将验证码泄露于他人。');
    	var_dump($SendMsg);
    	var_dump(json_decode($SendMsg));exit;
    }
    

    //找回密码
    public function getPassWord(){
        $mobile = I('post.mobile');
        $password = I('post.password');
        $checkCode=I('post.checkCode');
        if(empty($mobile) or empty($password) or empty($checkCode)){
            $this->retError(-2,"必要参数为空");
            exit;
        }
        $mobileCheckCode=$this->redis->get($mobile.'getPassCode');
        if(!$mobileCheckCode){
            $this->retError(-3,'验证码已过期');
            exit;
        }
        if($mobileCheckCode!=md5(C('SHOP_KEY').$checkCode)){
            $this->retError(-4,'手机验证码不正确');
            exit;
        }
        $this->redis->rm($mobile.'getPassCode');
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
            $this->retSuccess(array("status"=>true),'密码重置成功，请重新登录！');
        }else{
            $this->retError(-1,$return['msg']);
        }
    }
    
    //检查用户token是否失效
    public function checkToken(){
        $token=I("post.token");
        if(empty($token)){
            $this->retError(-2,"没有传入token");
        }
        $data=array();
        if($this->redis->get($token)){
            $data['status']=true;
            $this->retSuccess($data,"token未失效");
        }else{
            $data['status']=false;
            $this->retError(-1,"token已失效");
        }
    }


    //设置支付密码操作
    public function doSetPayPwd(){
        $pwd=I('post.payPwd');
        if(!$this->uid){
            $this->retError("-1","请登录");
            exit;
        }
        if(empty($pwd)){
            $this->retError("-1","密码不能为空");
            exit;
        }
        $data['md5_password'] = md5(I('post.payPwd'));
        $condition['user_id'] = $this->uid;
        $res = $this->modelUser->updatePayPassword($condition,$data);
        if ($res) {
           // echo json_encode(array(1,'修改成功！'));
            $this->retSuccess(array('msg'=>'修改成功'));
        }else{
            //echo json_encode(array(0,'修改失败，请重试！'));
            $this->retError("-1","修改失败");
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