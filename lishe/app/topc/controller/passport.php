<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topc_ctl_passport extends topc_controller
{
    public function __construct(){
        parent::__construct();
        $this->setLayoutFlag('passport');
        kernel::single('base_session')->start();
        $this->passport = kernel::single('topc_passport');
    }
	
	//登录界面
    public function signin(){
    	if(userAuth::check()){
        	header("location:/member-index.html");
        }
        $pagedata['next_page'] = request::server('HTTP_REFERER');
        $pagedata['isShowVcode'] = userAuth::isShowVcode('login');
        return $this->page('topc/passport/signin/signin.html',$pagedata);
    }

	//注册界面
    public function signup(){
        header("location:/business/index.php/User/signup");
    }

    //退出
    public function logout(){
        userAuth::logout();
        header("location:/shop.html");
    }
    //找回密码第一步
    public function findPwd()
    {
        return $this->page('topc/passport/forgot/forgot.html');
    }

    //找回密码第二步
    public function findPwdTwo()
    {
        $postData = utils::_filter_input(input::get());
        if($postData)
        {
        	$pagedata['send_status']='true';
            $pagedata['data']=array('mobile'=>$postData['username']);
            return view::make('topc/passport/forgot/two.html', $pagedata);
        }

        //$url = url::action('topc_ctl_passport@findPwd');
        //$msg = app::get('topc')->_('账户不存在');
        //return $this->splash('error',$url,$msg);
    }

    //找回密码第三步
    public function findPwdThree()
    {
        $postData = utils::_filter_input(input::get());
        $vcode = $postData['vcode'];
        $loginName = $postData['uname'];
        $sendType = $postData['type'];
        $_SESSION['forget']['loginName']=$loginName;
        try
        {
            $vcodeData=userVcode::verify($vcode,$loginName,$sendType);
            if(!$vcodeData)
            {
                throw new \LogicException('验证码输入错误');
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }
        return view::make('topc/passport/forgot/three.html', $postData);
    }
    //找回密码第四步
    public function findPwdFour()
    {
        $postData = utils::_filter_input(input::get());
        $account = $postData['account'];
        $loginName=$_SESSION['forget']['loginName'];
        if($account!=$loginName)
        {
        	unset($_SESSION['forget']['loginName']);
            $msg = app::get('topc')->_('页面已过期,请重新找回密码');
            return $this->splash('failed',null,$msg,true);
        }

        $validator = validator::make(
            ['password' => $postData['password'] , 'password_confirmation' => $postData['confirmpwd']],
            ['password' => 'min:6|max:20|confirmed'],
            ['password' => '密码长度不能小于6位!|密码长度不能大于20位!|输入的密码不一致!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',$url,$error[0],true);
            }
        }
        //调用接口更改密码
        $url = config::get('link.lishe_company_url').config::get('link.company_updatePass');
        $sign=md5('doType=fg&newPass='.$postData['password'].'&phoneNum='.$account.config::get('link.company_key'));
		$data=array(
        	'doType'=>'fg',
        	'newPass'=>$postData['password'],
        	'phoneNum'=>$account,
        	'sign'=>$sign
        );
        
		$res = kernel::single('base_httpclient')->post($url,$data);
		$reMsg=json_decode($res,TRUE);
		if($reMsg['result']==100){
			if($reMsg['errcode']>0){
				$url = url::action('topc_ctl_passport@findPwd');
				return $this->splash('error',$url,$reMsg['msg'],true);
			}				
		}else{
			$url = url::action('topc_ctl_passport@findPwd');
			return $this->splash('error',$url,$reMsg['msg'],true);
		}
		return $this->splash('success','/passport-signin.html','密码重置成功!');
    }
    
    //发送验证码
    public function sendVcode(){
        $postData = utils::_filter_input(input::get());
        $validator = validator::make(
            [$postData['uname']],['required'],['您的邮箱或手机号不能为空!']
        );

        //验证码发送之前的判断
        //这里之前是判断用户post数据是否包含verifycode字段，如果不包含就跳过验证码了。这里改为判断用户使用手机注册（by Elrond at 2015.1.27）
        $accountType = app::get('topc')->rpcCall('user.get.account.type',array('user_name'=>$postData['uname']),'buyer');
        if($accountType == 'mobile'){
            $valid = validator::make(
                [$postData['verifycode']],['required']
            );
            if($valid->fails()){
                return $this->splash('error',null,"图片验证码不能为空!");
            }
            if(!base_vcode::verify($postData['verifycodekey'],$postData['verifycode'])){
                return $this->splash('error',null,"图片验证码错误!");
            }
        }

       if ($validator->fails()){
            $messages = $validator->messagesInfo();
            foreach( $messages as $error ){
                return $this->splash('error',null,$error[0]);
            }
        }
        //$accountType = kernel::single('pam_tools')->checkLoginNameType($postData['uname']);
        try
        {
            $this->passport->sendVcode($postData['uname'],$postData['type']);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        if($accountType == "email"){
            return $this->splash('success',null,"邮箱验证链接已经发送至邮箱，请登录邮箱验证");
        }else{
            return $this->splash('success',null,"验证码发送成功");
        }
    }

}

