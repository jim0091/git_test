<?php
/**
  +------------------------------------------------------------------------------
 * SmsController
  +------------------------------------------------------------------------------
 * @author   	赵尊杰 <10199720@qq.com>
 * @version  	$Id: SmsController.class.php v001 2016-06-02
 * @description 短信接口封装
  +------------------------------------------------------------------------------
 */
namespace Home\Controller;
use Think\Controller;
class SmsController extends Controller {
	const ACCOUNT='lsw@lsw';
	const PASSWORD = 'LISHE%wcdK9';
	const MOS_WSDL = 'http://211.147.239.62/Service/WebService.asmx?wsdl'; //MOS wsdl地址
	const SING = '【礼舍网】';//短信签名
	const TYPE = '1'; //写入短信表中的类型
	const TOTAL = 30; //单个号码发送验证码的限制数量
	
	public function __construct(){
		parent::__construct();
		$this->client = new \SoapClient(self::MOS_WSDL);//SoapClient对象
	}
	
	//验证码内容
	public function smsContent($smsCode){
		$content = "您的礼舍验证码：".$smsCode."。";
		return $content;
	}
		
	//写入数据库
	public function insertSMS($receiver,$content,$ret=array()){
		
	}
	
	//处理发送
    public function send($mobile, $content){
		$mobile = $this->checkMobile($mobile);
		if(!$mobile){
			return array('result'=>9,'uuid'=>'','message'=>'手机号码不正确');
		}
		//if($this->SendXz($mobile)===false){
			//return array('result'=>8,'uuid'=>'','message'=>'该手机号码近期发送验证码已达到'.$this->total.'次');
		//}
		$uuid=$this->getUuid();
		$MessageData = array(	
			'Phone'=>$mobile,
			'Content'=>$content,
			'vipFlag'=>'false',
			'customMsgID'=>'',
			'customNum'=>''
		);
		$mtpack = array(
			'uuid'=>$uuid,
			'batchID'=>$uuid,
			'batchName'=>'发送短信',
			'sendType'=>'1',
			'msgType'=>'1',
			'msgs'=>array('MessageData'=>$MessageData),
			'bizType'=>'',
			'distinctFlag'=>'',
			'scheduleTime'=>'',
			'deadline'=>''
		);								
		//Post发送短信方法：
		$ret = $this->client->Post(array('account'=>self::ACCOUNT,'password'=>self::PASSWORD,'mtpack'=>$mtpack));		
		return $this->objectToArr($ret->PostResult);
	}
	
	//获取状态报告
	public function getSmsInfo(){		
		$param = array('account'=>self::ACCOUNT,'password'=>self::PASSWORD);
		$ret = $this->client->GetAccountInfo($param);
		return $this->objectToArr($ret);
	}

	//获取上行信息方法
	public function getMOMessage(){		
		$param = array('account'=>self::ACCOUNT,'password'=>self::PASSWORD,'pagesize'=>'10');
		$ret = $this->client->GetMOMessage($param);
		return $this->objectToArr($ret);
	}
	
	//生成uuid的方法，可使用其他方法
	public function getUuid(){
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8).$hyphen.substr($charid, 8, 4).$hyphen.substr($charid,12, 4).$hyphen.substr($charid,16, 4).$hyphen.substr($charid,20,12);
        return $uuid;
	}

	//处理返回值
	public function objectToArr($array){
		if(is_object($array)){
			$array = (array)$array;
		}
		if(is_array($array)){
			foreach($array as $key=>$value){
				$array[$key] = $this->objectToArr($value);
			}
		}
		return $array;
	}
	
	//验证手机号码
	public function checkMobile($mobile){
		$mobile = preg_match("/1[345678]{1}\d{9}$/",$mobile)?$mobile: '';
		return $mobile;
	}	
	
	public function sendSms(){
		$mobile = I('post.mobile');
		$content = I('post.content');
		return $this->send($mobile,$content);
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
}