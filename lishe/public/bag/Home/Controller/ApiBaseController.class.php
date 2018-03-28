<?php
namespace Home\Controller;
use Think\Controller;
class ApiBaseController extends Controller {
	
	private static $_requestId=0; //请求id，请求标志，仅供参考，（不能当做唯一标志，可能会串号）
	//private $_requestTime = '1970-01-01 00:00:00';
    private static $_ip = '0.0.0.0';
	
	/**
	 * 初始化
	 */
	protected function _initialize(){
		self::$_ip = get_client_ip(0, true);
		self::$_requestId = $this->requestId(); //ip.time.
		//日志记录
		$data = json_encode(I('param.','null',''));
		$this->log('method:'.REQUEST_METHOD.' data:'.$data, 'mark');
	}
	
	/**
	 * 空操作
	 */
	public function _empty(){
		echo 'error request';
	}
    
    /**
     * 建议该方法放到function.php中
     * 校验签名，签名为post过来的参数集合，具体算法看源代码，注意，必须包含sign字段，否则签名不通过
     * @param array $param
     * @option string 操作选项，取值范围，CHECK检测，CREATE生成, 默认'CHECK'
     * @return boolean
     */
	protected function signature($param = array(), $option='CHECK'){
		
		if(empty($param) || !is_array($param)){
			return false;
		}
		$sign = '';
		if($option == 'CHECK'){
			//判断sign字段
			if(empty($param['sign'])){
				return false;
			}
			$sign = $param['sign'];
			unset($param['sign']);
		}
		//排序，按ASCII升序
		ksort($param, SORT_REGULAR);
		$signStr = '';
		//拼接成 a=123&b=123格式
		array_walk($param, function($value, $key) use (&$signStr){
			$signStr .= "$key=$value&";
		});
		if(empty($signStr)){
			return false;
		}
		//删除最后一个‘&’，并加上key
		$signStr = rtrim($signStr, '&') . C('API_KEY');
		//self::log("signature:$signStr mysign=".md5($signStr) . 'sign='.$sign);
		$md5Str = md5($signStr);
		if($option == 'CHECK'){
			return $md5Str == $sign; //返回校验结果
		}else if($option == 'CREATE'){
			return $md5Str;
		}else{
			return false;
		}
	}
    
    /**
     * 获取请求id<br/>
     * @return string
     * @author Gaolong
     */
	protected static function requestId(){
    	return substr(microtime(true) * 10000, 6) . substr(ip2long(self::$_ip), rand(0, 6), 4);
    }
    
    /**
     * 日志记录
     * @param string $msg
     * @author Gaolong
     */
    protected static function log($msg = '', $logFlag='error',$logName = ACTION_NAME){
    	$logPath = APP_PATH.'/Logs/Api/'.date('Ymd').'_'.$logName.'.log'; //设置Home目录同级
    	//格式
    	$msg = 'time:'.date('Y-m-d H:i:s') . " {$logFlag} requestId:".self::$_requestId.' ip:'.self::$_ip." {$msg}";
    	//写入
    	@file_put_contents($logPath, "{$msg}\r\n", FILE_APPEND);
    }
    
    //接口返回错误信息
    protected function retError($errCode=1, $msg='操作失败'){
    	$ret = array(
    			'result'=>100,
    			'errcode'=>$errCode,
    			'msg'=>$msg,
    			'requestId'=>self::$_requestId
    	);
    	$this->ajaxReturn($ret);
    }
    
    //接口返回结果
    protected function retSuccess($data=array(), $msg='操作成功'){
    	$ret = array(
    			'result'=>100,
    			'errcode'=>0,
    			'msg'=>$msg,
    			'data'=>$data,
    			'requestId'=>self::$_requestId
    	);
    	$this->ajaxReturn($ret);
    }
}