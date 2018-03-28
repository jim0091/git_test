<?php
/*--------------------------------
功能:		HTTP接口 发送短信类
--------------------------------*/

class SendCode
{
	const ACCOUNT='lsw@lsw';
	const PASSWORD = 'LISHE%wcdK9';
	const MOS_WSDL = 'http://211.147.239.62/Service/WebService.asmx?wsdl'; //MOS wsdl地址
	const SING = '【礼舍网】';//短信签名
	const TYPE = '1'; //写入短信表中的类型
	const TOTAL = 15; //单个号码发送验证码的限制数量
	/**
    * 2016-09-08 zzw
    *发送手机验证码
    */
   function sendPhoneCode($phone,$content){   
        $this->client = new SoapClient(self::MOS_WSDL);//SoapClient对象
        $uuid = $this->getUuid();
    	 $MessageData = array(	
			'Phone'=>$phone,
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
		//var_dump($mtpack);
		$ret = $this->client->Post(array('account'=>self::ACCOUNT,'password'=>self::PASSWORD,'mtpack'=>$mtpack)); 
		//var_dump($ret);      
		return $this->objectToArr($ret->PostResult);
       

    }
    //生成uuid的方法
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

}
?>