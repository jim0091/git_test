<?php

// 手机邮箱发送验证码
class system_data_user_vcode{
    //public $ttl = 3600;//86400;
    public $ttl = 3600;

    protected $prefix = 'VCODE_VERIFY:';

    public function __construct()
    {
        kernel::single('base_session')->start();
    }

    //随机取6位字符数
    public function randomkeys($length)
    {
        $key = '';
        $pattern = '1234567890';    //字符池
        for($i=0;$i<$length;$i++)
        {
            $key .= $pattern{mt_rand(0,9)};    //生成php随机数
        }
        return $key;
    }

    //验证码检查
    public function verify($vcode,$send,$type)
    {
        if(empty($vcode) ) return false;
        $vcodeData = $this->getVcode((string)$send,$type);
        if($vcodeData && $vcodeData['vcode'] == $vcode)
        {
            $data = $this->deleteVcode($vcodeData['account'],$type, $vcodeData);
            return $data;
        }
        else
        {
            return false;
        }
    }

    /*
     * 删除验证码（非物理删除，重新生成一个验证码）
     *
     */
    public function deleteVcode($account,$type, $vcodeData)
    {
        $vcode = $this->randomkeys(6);
        $vcodeData['vcode'] = $vcode;
        $key = $this->getVcodeKey($account,$type);
        cache::store('vcode')->put($key, $vcodeData, $this->ttl);
        return $vcodeData;
    }

    public function checkVcode($account,$type='signup'){
        $vcodeData = $this->getVcode($account,$type);
        if($vcodeData && !strpos($account,'@')){
            //if( $vcodeData['createtime'] == date('Ymd') && $vcodeData['count'] == 3 ){
                //throw new \LogicException(app::get('system')->_('每天只能进行3次验证'));
                //return false;
            //}

            if( time() - $vcodeData['lastmodify'] <= 1 ){
                throw new \LogicException(app::get('system')->_('2分钟发送一次,还没到两分钟则不进行发送'));
                return false;
            }

            if( $vcodeData['createtime'] != date('Ymd') ){
                $vcodeData['count'] = 0;
            }
        }
        return $vcodeData;
    }

    public function getVcodeKey($account,$type='signup')
    {
        return $this->prefix.$account.$type;
    }

    //获取验证码
    public function getVcode($account,$type='signup')
    {
        $key = $this->getVcodeKey($account,$type);

        $vcode = cache::store('vcode')->get($key);

        return $vcode;
    }

    //短信发送
    public function send_sms($type,$mobile)
    {
        if( !$tmpl = $this->sendtypeToTmpl($type) ) return false;
        $vcodeData = $this->checkVcode($mobile,$type);
        $vcode = $this->randomkeys(6);
        $vcodeData['account'] = $mobile;
        $vcodeData['vcode'] = $vcode;
        $vcodeData['count']  += 1;
        $vcodeData['createtime'] = date('Ymd');
        $vcodeData['lastmodify'] = time();
        $data['vcode'] = $vcode;
        $key = $this->getVcodeKey($mobile,$type);
        
        //调用接口发送短信
        $url=config::get('link.lishe_shop_url').'Sms/sendSms';
    	$data=array(
    		'mobile'=>$mobile,
    		'content'=>'您的短信验证码为：'.$vcode
    	);    	
		$return=kernel::single('base_httpclient')->post($url,$data);
        if($return == "fail"){
            logger::info('验证码发送失败',$return);
            throw new \LogicException(app::get('system')->_('验证码发送失败!'));
        }
        cache::store('vcode')->put($key, $vcodeData, $this->ttl);
        logger::info('验证码发送成功',$vcodeData);
        return true;
    }
    //邮件发送
    public function send_email($type,$email,$content, $is_url = true){
        if( !$tmpl = $this->sendtypeToTmpl($type) ) return false;
        $vcodeData = $this->checkVcode($email,$type);
        $vcode = $this->randomkeys(6);
        $vcodeData['account'] = $email;
        $vcodeData['vcode'] = $vcode;
        $vcodeData['count']  = 1;
        $vcodeData['createtime'] = date('Ymd');
        $vcodeData['lastmodify'] = time();

        $data['shopname'] = app::get('sysconf')->getConf('site.name');
        $data['vcode'] = $content."&vcode=".$vcode;
        //当$is_url不为true时只发送单纯的验证码
        if(! $is_url)
        {
            $data['vcode'] = $vcode;
        }
        $key = $this->getVcodeKey($email,$type);

        $result = messenger::sendEmail($email,$tmpl,$data);

        if($result['rsp'] == "fail")
        {
            logger::info('邮件发送失败',$result);
            throw new \LogicException(app::get('system')->_('邮件发送失败,请检查邮箱格式是否正确!'));
        }

        cache::store('vcode')->put($key, $vcodeData, $this->ttl);
        logger::info('验证码发送成功',$vcodeData);
        return true;
    }

    //短信发送模板设置
    public function sendtypeToTmpl($sendtype){

        $tmpl = false;
        switch($sendtype){
        case 'activation': //激活
            $tmpl = 'account-member';
            break;
        case 'reset': //重置手机号或者邮箱
            $tmpl = 'account-member';
            break;
         case 'unreset': //重置手机号或者邮箱
            $tmpl = 'account-unmember';
            break;
        case 'forgot': //找回密码
            $tmpl = 'account-lostPw';
            break;
        case 'signup': //手机注册
            $tmpl = 'account-signup';
            break;
        case 'auth_shop': //商家安全验证
            $tmpl = 'account-shop';
            break;
        case 'findPw_shop': //商家找回密码
            $tmpl = 'findPw-shop';
            break;
        case 'depost_forgot':
            $tmpl = 'deposit-lostPw';
            break;
        }
        return $tmpl;
    }



}
