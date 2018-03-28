<?php
/*
 * 东莞移动饭卡订单接口
 * 此方法需要PHP加载 SoapClient 库
 */

namespace Home\Controller;
class MobileApiController extends CommonController{
    /**
     * 存放提供远程服务的URL。
     * @access  private
     * @var     array       $api_urls
     */
    var $api_wsdl = 'http://120.198.246.23/OnlineOrder_Ecard/OnlineOrderWebService.asmx?wsdl';

    /**
     * 存放SoapClient对象
     * @access  private
     * @var     object      $t
     */
	var $client   = NULL;
	
	/***
	 *  DES加密验证字符串
	 */
	var $systemid = 'Ecard_POS';
	var $password = 'POS12345';
	var $DESKey   = 'qtonepos';
	var $datetime = NULL;
	var $DES 	  = NULL;

    /**
     * 存放程序执行过程中的错误信息，这样做的一个好处是：程序可以支持多语言。
     * 程序在执行相关的操作时，error_no值将被改变，可能被赋为空或大等0的数字.
     * 为空或0表示动作成功；大于0的数字表示动作失败，该数字代表错误号。
     *
     * @access  public
     * @var     array       $errors
     */
    var $errors   = array('server_errors'=>array('error_no' => -1, 'error_msg' => ''));
	var $strlenEmpCode = 10;
	
    /**
     * 构造函数
     * @access  public
     * @return  void
     */
    public function __construct(){
        $this->mobileapi();
    }

    /**
     * 构造函数
     * @access  public
     * @return  void
     */
    public function mobileapi(){		
        /* SoapClient对象 */
		$this->client = new \SoapClient($this->api_wsdl);
		
		/* DES加密字符串*/
		require 'Show/Home/Common/DES.php';
		$this->DES = new \DES($this->DESKey);//new DES($this->DESKey);
    }

	/**
	 * 生成DES加密串
	 * @return Array
	 * @param systemid
	 * @param pwd
	 */
	public function DesPwd(){
		$str = $this->systemid."|".$this->password;
		$mstr = $this->DES->encrypt($str);

		return array('systemid'=>$this->systemid,'pwd'=>$mstr);
	}	

	/**
	 * 验证手机号码，获取用户信息
	 * @return Array
	 * @param REQUEST MobileNo
	 */
	public function getUserInfo(){
		$MobileNo = isset($_REQUEST['MobileNo']) ? $_REQUEST['MobileNo'] : '';
		
		if(!$this->isMobile($MobileNo)){
			$this->errors['server_errors']['error_no']  = 0;
			$this->errors['server_errors']['error_msg'] = '非法的手机号码!';
			return $this->errors;
		}
// 模拟数据		
//		if($MobileNo == '13662294883'){
//			$array = array('ExtensionData'=>array(),'Code'=>'0','Decription'=>'操作成功 ','IDStatus'=>'1','EmpCode'=>'11214521','EmpName'=>'彭志勇','EmpMobile'=>'13662294883','PosBalance'=>'1000.00',);
//			return $array;
//		}elseif($MobileNo == '13827473553'){
//			$array = array('ExtensionData'=>array(),'Code'=>'0','Decription'=>'操作成功 ','IDStatus'=>'1','EmpCode'=>'11214521','EmpName'=>'彭志勇','EmpMobile'=>'13827473553','PosBalance'=>'800.00',);
//			return $array;
//		}

        $params = array('MobileNo' => $MobileNo);
		$params += $this->DesPwd();

		//Post
		$ret = $this->client->GetUserLoginInfo($params);
		
        if (!$ret->GetUserLoginInfoResult){
            $this->errors['server_errors']['error_no'] = 7;
			$this->errors['server_errors']['error_msg'] = 'HTTP响应体为空';

            return $this->errors;
        }
		
		echo json_encode($this->object_array($ret->GetUserLoginInfoResult));
		//return $this->object_array($ret->GetUserLoginInfoResult);
	}

	/**
	 * 获取用户余额
	 * @return Array
	 * @param REQUEST EmpCode
	 */
	public function getPosBalance(){
		$EmpCode = isset($_REQUEST['EmpCode']) ? $_REQUEST['EmpCode'] : '';
	
		//11214 52174
		if(!is_numeric($EmpCode) || strlen($EmpCode)!=$this->strlenEmpCode){
			$this->errors['server_errors']['error_no']  = 1;
			$this->errors['server_errors']['error_msg'] = '非法的员工编号!';
			return $this->errors;
		}
		
        $params  = array('EmpCode' => $EmpCode);
		$params += $this->DesPwd();

		//Post
		$ret = $this->client->GetPosBalance($params);
		
        if (!$ret->GetPosBalanceResult){
            $this->errors['server_errors']['error_no'] = 7;
			$this->errors['server_errors']['error_msg'] = 'HTTP响应体为空';

            return $this->errors;
        }
		echo json_encode($this->object_array($ret->GetPosBalanceResult));
		//return $this->object_array($ret->GetPosBalanceResult);
	}
	
	/**
	 * 提交订单
	 * $order Array
	 * @param EmpCode 			员工编号
	 * @param OrderNum 			订单号
	 * @param OrderTime 		下单时间
	 * @param OrderType 		订单类型
	 * @param OrderMoney 		订单ECARD支付金额
	 * @param OrderTotalMoney 	订单总额
	 * @return Array
	 */
	function Order(){
		$order['EmpCode'] 		  = isset($_REQUEST['EmpCode']) ? $_REQUEST['EmpCode'] : '';
		$order['OrderNum'] 		  = isset($_REQUEST['OrderNum']) ? $_REQUEST['OrderNum'] : '';
		$order['OrderTime'] 	  = isset($_REQUEST['OrderTime']) ? $_REQUEST['OrderTime'] : '';
		$order['OrderType'] 	  = isset($_REQUEST['OrderType']) ? $_REQUEST['OrderType'] : '';
		$order['OrderMoney'] 	  = isset($_REQUEST['OrderMoney']) ? $_REQUEST['OrderMoney'] : '';
		$order['OrderTotalMoney'] = isset($_REQUEST['OrderTotalMoney']) ? $_REQUEST['OrderTotalMoney'] : '';
		//1121452174
		if(!is_numeric($order['EmpCode']) || strlen($order['EmpCode'])!=$this->strlenEmpCode){
			$this->errors['server_errors']['error_no']  = 1;
			$this->errors['server_errors']['error_msg'] = '非法的员工编号!';
			return $this->errors;
		}

		$order += $this->DesPwd();
		//Post
		$ret = $this->client->InsertOrderData($order);
		$Result = array();
		
        if (!$ret->InsertOrderDataResult){
            $this->errors['server_errors']['error_no'] = 7;
			$this->errors['server_errors']['error_msg'] = 'HTTP响应体为空';

            return $this->errors;
        }else{
			$Result = $this->object_array($ret->InsertOrderDataResult);
		}

		echo json_encode($Result);
		//return $Result;
	}
	
	/**
	 * 取消订单后 退款 支持部分退款
	 * $order Array
	 * @param id 				自增ID，主键
	 * @param EmpCode 			员工编号
	 * @param RtnNum            退单流水号 ,唯一标志，重复提交返回成功，不执行
	 * @param OrderNum 			订单号
	 * @param RtnTime 			取消订单时间
	 * @param RtnMoney 			退款金额
	 * @return Array
	 */
	public function OrderCannel(){
		$orderCannel['EmpCode'] 	  = isset($_REQUEST['EmpCode']) ? $_REQUEST['EmpCode'] : '';
		$orderCannel['RtnNum'] 		  = isset($_REQUEST['RtnNum']) ? $_REQUEST['RtnNum'] : '';
		$orderCannel['OrderNum'] 	  = isset($_REQUEST['OrderNum']) ? $_REQUEST['OrderNum'] : '';
		$orderCannel['RtnTime'] 	  = isset($_REQUEST['RtnTime']) ? $_REQUEST['RtnTime'] : '';
		$orderCannel['RtnMoney'] 	  = isset($_REQUEST['RtnMoney']) ? $_REQUEST['RtnMoney'] : '';

		if(!is_numeric($order['EmpCode']) || strlen($order['EmpCode'])!=$this->strlenEmpCode){
			$this->errors['server_errors']['error_no']  = 1;
			$this->errors['server_errors']['error_msg'] = '非法的员工编号!';
			return $this->errors;
		}
		
		//Post
		$orderCannel += $this->DesPwd();

		$ret = $this->client->UpdateOrderCannel($orderCannel);
		$Result = array();

        if(!$ret->UpdateOrderCannelResult){
            $this->errors['server_errors']['error_no'] = 7;
			$this->errors['server_errors']['error_msg'] = 'HTTP响应体为空';

            return $this->errors;
        }else{
			$Result = $this->object_array($ret->UpdateOrderCannelResult);
		}
		echo json_encode($Result);
		//return $Result;
	}
	
	/**
	 * 订单签收
	 * @Array $order 
	 * @param EmpCode 					员工编号
	 * @param OrderNum 					订单号
	 * @param SignType 					签收类型 0本人/1系统过期自动签收
	 * @param SignTime 					签收时间 
	 * @param SatisfactionStatus 		评价状态 0未评价/1好/2中/3差
	 * @param SatisfactionDes 			评价内容
	 * @return Array
	 */
	public function OrderSign(){
		$order['EmpCode']				  = isset($_REQUEST['EmpCode']) ? $_REQUEST['EmpCode'] : '';
		$order['OrderNum']				  = isset($_REQUEST['OrderNum']) ? $_REQUEST['OrderNum'] : '';
		$order['SignType']				  = isset($_REQUEST['SignType']) ? $_REQUEST['SignType'] : '';
		$order['SignTime'] 	  			  = isset($_REQUEST['SignTime']) ? $_REQUEST['SignTime'] : '';
		$order['SatisfactionStatus'] 	  = isset($_REQUEST['SatisfactionStatus']) ? $_REQUEST['SatisfactionStatus'] : '';
		$order['SatisfactionDes'] 		  = isset($_REQUEST['SatisfactionDes']) ? $_REQUEST['SatisfactionDes'] : '';
		
		if(!is_numeric($order['EmpCode']) || strlen($order['EmpCode'])!=$this->strlenEmpCode){
			$this->errors['server_errors']['error_no']  = 1;
			$this->errors['server_errors']['error_msg'] = '非法的员工编号!';
			return $this->errors;
		}
		$order += $this->DesPwd();
		$ret = $this->client->UpdateOrderSign($order);
		$Result = array();
		
        if (!$ret->UpdateOrderSignResult){
            $this->errors['server_errors']['error_no'] = 7;
			$this->errors['server_errors']['error_msg'] = 'HTTP响应体为空';
            return $this->errors;
        }else{
			$Result = $this->object_array($ret->UpdateOrderSignResult);
		}
		
		echo json_encode($Result);
		//return $Result;
	}

    /**
    * 验证手机号是否正确
    * @param INT $mobile
    */
    public function isMobile($mobile){
        if (!is_numeric($mobile)){
            return false;
        }
        return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
    }
	
    /**
     * 对象转数组
     *
     * @access  public
     * @param   object
     * @return  array
     */
	public function object_array($array){ //处理返回值
		if(is_object($array)){
			$array = (array)$array;
		}
		if(is_array($array)){
			foreach($array as $key=>$value){
				$array[$key] = $this->object_array($value);
			}
		}
		return $array;
	}

}