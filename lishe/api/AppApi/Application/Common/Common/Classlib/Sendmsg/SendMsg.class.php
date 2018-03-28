<?php
/**
 * +----------------------------------------------------------------------
 * |@Category:		[发送短信类];							@version:1.0
 * +----------------------------------------------------------------------
 * |@Namespace:		[Common/Common/Classlib/Sendmsg];
 * +----------------------------------------------------------------------
 * |@Name:			[SendMsg.class.php];
 * +----------------------------------------------------------------------
 * |@Filesource:	[ThinkPHP.@version(3.2.3)];		@PHP.@version:5.4.3
 * +----------------------------------------------------------------------
 * |@License:(http://www.Apache.org/licenses/LICENSE-2.0);@version:2.2.22
 * +----------------------------------------------------------------------
 * |@Copyright: (c) 2015-2016 (http://lishe.cn) All rights reserved;
 * +----------------------------------------------------------------------
 * |@Author:		lihongqiang				@StartTime:	2017-03-02 10:35
 * +----------------------------------------------------------------------
 * |@Email:		  <lhq@lishe.cn>			@Overtime:	2016-03-02
 * +----------------------------------------------------------------------
 *  */

namespace Common\Common\Classlib\Sendmsg;
class SendMsg {
	const ACCOUNT='lsw@lsw';//账号
	const PASSWORD = 'LISHE%wcdK9';//密码
	const MOS_WSDL = 'http://211.147.239.62/Service/WebService.asmx?wsdl'; //MOS wsdl地址
	const SING = '【礼舍网】';//短信签名
	const TYPE = '1'; //写入短信表中的类型
	const TOTAL = 15; //单个号码发送验证码的限制数量
	
	/**
	 * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @author lihongqiang  2017-03-02
	 * @param int $PhoneNumber //手机号码
	 * @param string $MsgContent //短信内容
	 * @return array
	 */
	public function MsgSend($PhoneNumber,$MsgContent){
    	if(empty($PhoneNumber)){
    		$Info['status'] = 0;
    		$Info['message'] = "手机号码不能为空";
    		return $Info;
    	}else{
    		if(empty($MsgContent)){
    			$Info['status'] = 0;
    			$Info['message'] = "短信内容不能为空";
    			return $Info;
    		}else{
    			//检测手机号码是否正确
    			$IS_PhoneNumber = $this->isPhoneNumber($PhoneNumber);
    			if ($IS_PhoneNumber){
    				$MsgClient = new \SoapClient(self::MOS_WSDL);//new一个新的 SoapClient对象
    				$Msgs['MessageData'] = $this->getMessageData($PhoneNumber, $MsgContent);//获取单条信息数据
    				$Mtpack = $this->getMtpack($Msgs);//获取信息包
    				$beginSend ['account'] = self::ACCOUNT;
    				$beginSend ['password'] = self::PASSWORD;
    				$beginSend ['mtpack'] = $Mtpack;
    				$SendInfo = $MsgClient->Post($beginSend);//调用第三方的接口，返回object对象
    				$callbackStatus = $SendInfo->PostResult->result;//发送状态
    				$callbackMessage = $SendInfo->PostResult->message;//返回的信息
    				if($callbackStatus==0){//发送成功
    					$Info['status'] = 1;
    					$Info['message'] = "短信发送成功";
    					return $Info;
    				}else{
    					$Info['status'] = 0;
    					$Info['message'] = $callbackMessage;
    					return $Info;
    				}
    			}else{
    				$Info['status'] = 0;
    				$Info['message'] = "手机号码不正确";
    				return $SendInfo;
    			}
    		}
    	}
    }
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * //获取单条信息数据
	 * @author lihongqiang
	 * @param int 		$PhoneNumber //手机号码
	 * @param string 	$MsgContent //短信内容
	 * @return array 	$MessageData
	 */
    protected function getMessageData($PhoneNumber,$msgContent){
    	$MessageData['Phone'] = $PhoneNumber;
    	$MessageData['Content'] = $msgContent;
    	$MessageData['vipFlag'] = false;
    	$MessageData['customMsgID'] = '';
    	$MessageData['customNum'] = '';
    	return $MessageData;
    }
    
    
    
    
    /**
     * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name 获取信息包
     * @author lihongqiang
     * @param int 		$PhoneNumber //手机号码
     * @param string 	$MsgContent //短信内容
     * @return array 	$MessageData
     */
    protected function getMtpack($msgs){
    	$uuid=$this->getUuid();
    	$Mtpack['uuid'] = $uuid;
    	$Mtpack['batchID'] = $uuid;
    	$Mtpack['batchName'] = '发送短信';
    	$Mtpack['sendType'] = 1;
    	$Mtpack['msgType'] = 1;
    	$Mtpack['msgs'] = $msgs;
    	$Mtpack['bizType'] = '';
    	$Mtpack['distinctFlag'] = '';
    	$Mtpack['scheduleTime'] = '';
    	$Mtpack['deadline'] = '';
    	return $Mtpack;
    }
    
    
    /**
     * ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
     * @name 获取Uuid的方法，可使用其他方法
     * @author lihongqiang
     * @return string 	$uuid
     */
    protected function getUuid(){
    	mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
    	$charid = strtoupper(md5(uniqid(rand(), true)));
    	$hyphen = chr(45);// "-"
    	$uuid = substr($charid, 0, 8).$hyphen.substr($charid, 8, 4).$hyphen.substr($charid,12, 4).$hyphen.substr($charid,16, 4).$hyphen.substr($charid,20,12);
    	return $uuid;
    }


	/**
	 * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	 * @name:验证手机号是否正确
	 * @author: lihongqiang   2017-03-02
	 * @param: int $PhoneNumber
	 * @return:boolean 
	 * @example:
	 * 移动：134、135、136、137、138、139、150、151、152、157、158、159、182、183、184、187、188、178(4G)、147(上网卡)；
	 * 联通：130、131、132、155、156、185、186、176(4G)、145(上网卡)；
	 * 电信：133、153、180、181、189 、177(4G)；
	 * 卫星通信：1349
	 * 虚拟运营商：170
	 */
	protected function isPhoneNumber($PhoneNumber) {
		if (!is_numeric($PhoneNumber)) {
			return false;
		}else{
			if(preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $PhoneNumber)){
				return true;
			}else{
				return false;
			}
		}
	}
	
	//第三方接口返回的状态代表的信息
	// 	public static function status() {
	// 		return $statusStr = array (
	// 				'0' => '成功',
	// 				'-1' => '账号无效',
	// 				'-2' => '参数：无效',
	// 				'-3' => '连接不上服务器',
	// 				'-5' => '无效的短信数据，号码格式不对',
	// 				'-6' => '用户名密码错误',
	// 				'-7' => '旧密码不正确',
	// 				'-9' => '资金账户不存在',
	// 				'-11' => '包号码数量超过最大限制',
	// 				'-12' => '余额不足',
	// 				'-99' => '系统内部错误',
	// 				'-100' => '其它错误',
	// 		);
	// 	}
}