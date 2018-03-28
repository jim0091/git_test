<?php
/**
 * Created by PhpStorm.
 * User: 360314105@qq.com
 * Date: 2018/3/12
 * Time: 10:36
 */
namespace app\Home\Controller;

use think\Controller;
use think\Validate;
use app\Home\Common\AuthCode;

//登录
class Login extends Controller
{
    private $appid;
    private $appSecret;

    public function __construct()
    {
        $this->appid = '';
        $this->appSecret = '';
    }

    /*
     * 小程序授权登录
     * @param string code 微信code
     * @param string encryptedData 微信加密数据
     * @param string iv ...
     */
    public function login()
    {
        $param = input('post.');
        //验证规则
        $rule = [
            'code'=>'require',
            'encryptedData'=>'require',
            'iv'=>'require',
        ];
        //验证提示
        $msg = [
            'code.require'=>'缺少必要参数',
            'encryptedData.require'=>'缺少必要参数',
            'iv.require'=>'缺少必要参数',
        ];
        $validate = new Validate($rule,$msg);
        $validate->check($param);
        $error = $validate->getError();
        if(!empty($error))
        {
            return apiFail(9001,$error);
        }
        //拼接链接
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$this->appid."&secret=".$this->appSecret."&js_code=".$param['code']."&grant_type=authorization_code";
        //请求微信接口获取session_key
        $sessionKey = json_encode(httpGet($url));
        if(isset($sessionKey->session_key))
        {
            return apiFail(9002,'参数有误');
        }
        //加密验证
        $data = $this->decryptData($this->appid,$sessionKey->session_key,$param['encryptedData'],$param['iv'],$data);
        if($data)
        {
            $data = json_decode($data);
            //token
            $auth = new AuthCode();
            //查询是否已注册
            $res = Db::table('user')->where('union_id',$data->unionId)->find();
            $arr['auth_data'] = json_encode(array('weixin'=>array('sessionKey'=>$sessionKey->session_key,'expires_in'=>7200,'openId'=>$data->openId)));
            //如果已注册更新数据
            if($res)
            {
                $arr['update_time'] = time();
                $state = Db::table('user')->where('union_id',$data->unionId)->update($arr);
                if($state)
                {
                    $token = $auth->createToken($res['user_id']);
                    return apiSuccess(0,'SUCCESS',$token);
                }
                return apiFail(9001, '更新失败');
            }
            //如果未注册 执行注册
            $arr['avatar'] = $data->avatarUrl;
            $arr['user_name'] = $data->nickName;
            $arr['union_id'] = $data->unionId;
            $arr['add_time'] = time();
            $arr['user_id'] = MD5($data->unionId.time());
            $arr['birthday'] = date('Y-m-d',time());
            $state = Db::table('user')->insert($arr);
            if($state)
            {
                $token = $auth->createToken($arr['user_id']);
                return apiSuccess(0,'SUCCESS',$token);
            }
            return apiFail(2001 ,'注册失败');
        }
        return apiFail(9004,'验证失败');
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData($appid,$sessionKey,$encryptedData,$iv,&$data)
    {
        if (strlen($sessionKey) != 24) {
            return false;
        }
        $aesKey=base64_decode($sessionKey);
        if (strlen($iv) != 24) {
            return false;
        }
        $aesIV=base64_decode($iv);

        $aesCipher=base64_decode($encryptedData);

        $result = $this->decrypt($aesKey,$aesCipher,$aesIV);

        if ($result[0] != 0) {
            return $result[0];
        }
        $dataObj=json_decode( $result[1] );
        if( $dataObj  == NULL )
        {
            return false;
        }
        if( $dataObj->watermark->appid != $appid )
        {
            return false;
        }
        $data = $result[1];
        return $data;
    }


    /**
     * 对需要加密的明文进行填充补位
     * @param  string $text 需要进行填充补位操作的明文
     * @return string 补齐明文字符串
     */
    public function encode($text)
    {
        $block_size = 16;
        $text_length = strlen( $text );
        //计算需要填充的位数
        $amount_to_pad = $block_size - ( $text_length % PKCS7Encoder::$block_size );
        if ( $amount_to_pad == 0 ) {
            $amount_to_pad = 16;
        }
        //获得补位所用的字符
        $pad_chr = chr( $amount_to_pad );
        $tmp = "";
        for ( $index = 0; $index < $amount_to_pad; $index++ ) {
            $tmp .= $pad_chr;
        }
        return $text . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     * @param array $decrypted 解密后的明文
     * @return string 删除填充补位后的明文
     */
    public function decode($text)
    {

        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }

    /**
     * 对密文进行解密
     * @param string $aesCipher 需要解密的密文
     * @param string $aesIV 解密的初始向量
     * @return array/boolean 解密得到的明文
     */
    public function decrypt($aesKey,$aesCipher,$aesIV )
    {

        try {

            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');

            mcrypt_generic_init($module,$aesKey,$aesIV);

            //解密
            $decrypted = mdecrypt_generic($module,$aesCipher);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
        } catch (\Exception $e) {
            return array(ErrorCode::$IllegalBuffer,null);
        }


        try {
            //去除补位字符
            $result = $this->decode($decrypted);

        } catch (\Exception $e) {
            //print $e;
            return false;
        }
        return array(0, $result);
    }
}