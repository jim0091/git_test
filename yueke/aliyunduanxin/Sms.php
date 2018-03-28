<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/2
 * Time: 17:35
 */
namespace app\index\controller;

use think\Controller;
use think\Db;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Regions\EndpointConfig;
require_once '/home/www/fentu_server/extend/Aliyun/vendor/autoload.php';
//require_once 'D:\wamp\www\fentu_admin\extend\Aliyun\vendor\autoload.php';
class Sms extends Controller
{
    public  function sms($mobile = '',$type = '',$cause = '')
    {
        if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
            return '手机号码有误，请输入正确的手机号码';
        }
        //此处需要替换成自己的AK信息
        $accessKeyId = "LTAIfQH5r2yb1GjJ";//参考本文档步骤2
        $accessKeySecret = "4EqO1BoQQDRM4edvPc3xQBWr3CgQtk";//参考本文档步骤2
        //短信API产品名（短信产品名固定，无需修改）
        $product = "Dysmsapi";
        //短信API产品域名（接口地址固定，无需修改）
        $domain = "dysmsapi.aliyuncs.com";
        //暂时不支持多Region（目前仅支持cn-hangzhou请勿修改）
        $region = "cn-hangzhou";
        // 手动加载endpoint
        EndpointConfig::load();
        //初始化访问的acsCleint
        $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
        DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
        $acsClient= new DefaultAcsClient($profile);
        $request = new SendSmsRequest();
        //必填-短信接收号码。支持以逗号分隔的形式进行批量调用，批量上限为1000个手机号码,批量调用相对于单条调用及时性稍有延迟,验证码类型的短信推荐使用单条调用的方式
        $request->setPhoneNumbers($mobile);
        //必填-短信签名
        $request->setSignName("分图app");
        //必填-短信模板Code
        if($type == 3)
        {
            $request->setTemplateCode("SMS_108005113");
        }else if($type == 4){
            $request->setTemplateCode("SMS_107820119");
        }else if($type == 5){
            $request->setTemplateCode("SMS_107750097");
        }

        //SMS_107820119
        //选填-假如模板中存在变量需要替换则为必填(JSON格式),友情提示:如果JSON中需要带换行符,请参照标准的JSON协议对换行符的要求,比如短信内容中包含\r\n的情况在JSON中需要表示成\\r\\n,否则会导致JSON在服务端解析失败
        if($type == 3)
        {
            $auth_code = rand(1000,9999);
            $request->setTemplateParam("{code:".$auth_code."}");
        }else if($type == 5){
            $request->setTemplateParam('{"cause":"'.$cause .'"}');
        }
        //选填-发送短信流水号
//        $request->setOutId("1234");
        //发起访问请求
        $acsResponse = json_decode(json_encode($acsClient->getAcsResponse($request)));
        if($acsResponse->Code == 'OK')
        {
            if($type == 3 || $type == 2 || $type == 1)
            {
                $arr['auth_code'] = $auth_code;
                $arr['BizId'] = $acsResponse->BizId;
                $arr['type'] = $type;
                $arr['add_time'] = time();
                Db::table('sms')->insert($arr);
                return $data = ['return_code'=>'SUCCESS','BizId'=>$acsResponse->BizId];
            }
        }
        return $acsResponse->Message;
    }

    /**
     * 短信验证码
     * @param string $bizId
     * @param int $auth_code
     * @param int $type
     * @return bool
     */
    public function auth($bizId,$auth_code,$type)
    {
        $info = Db::table('sms')->where(['BizId'=>$bizId])->find();
        if($info)
        {
            if($type != $info['type'])
            {
                return '无效的验证码';
            }
            if($info['state'] == 2)
            {
                return '已失效';
            }
            if($info['add_time']+54000 < time())
            {
                return '已过期';
            }
            if($info['auth_code'] == $auth_code)
            {
                Db::table('sms')->where(['BizId'=>$bizId])->update(['state'=>2]);
                return 'SUCCESS';
            }
        }
        return '无效的验证码';
    }

    /**
     * js验证 不改变状态
     * @param $bizId
     * @param $auth_code
     * @param $type
     * @return bool
     */
    public function verify($bizId,$auth_code,$type)
    {
        $info = Db::table('sms')->where(['BizId'=>$bizId])->find();
        if($info)
        {
            if($type != $info['type'])
            {
                return '无效的验证码';
            }
            if($info['state'] == 2)
            {
                return '已失效';
            }
            if($info['add_time']+54000 < time())
            {
                return '已过期';
            }
            if($info['auth_code'] == $auth_code)
            {
                return true;
            }
        }
        return '无效的验证码';
    }
}
