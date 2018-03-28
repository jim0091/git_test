<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {	
	
	public function __construct(){
		parent::__construct();
		
		$action=strtolower(ACTION_NAME);		
		$control=strtolower(CONTROLLER_NAME);
		
		$account=session('account');
		$accountCookie=cookie('account');
		$account=json_decode($accountCookie,true);

		if(!empty($account['id']) and !empty($account['comId']) and !empty($account['account'])){
			$this->uid=$account['id'];
			$this->comId=$account['comId'];
			$this->account=$account['account'];
			$this->userName=$account['userName'];
			$this->index=$account['index'];
			$this->refer=$account['refer'];

		}else{
			$this->uid=0;
			$this->comId=0;
			$this->account='';
			$this->userName='';
			$this->index='';
			$this->refer='';
		}
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
    
    public function requestJdPost($url='', $data=''){
        if(empty($url) || empty($data)){
            return false;
        }      
        $ch=curl_init();//初始化curl
        curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
        $return=curl_exec($ch);//运行curl
        curl_close($ch);
        return $return;
    }    
    
    //增加授权模拟请求方法 赵尊杰 2016-07-01
    public function accreditPost($url,$data,$user,$password){
        if(empty($url) || empty($data) || empty($user) || empty($password)){
            return false;
        }
        $ch=curl_init();//初始化curl
        curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch,CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch,CURLOPT_USERPWD,''.$user.':'.$password.'');       
        $return=curl_exec($ch);//运行curl
        curl_close($ch);
        return $return;
    }
    
    //接口返回错误信息
    public function retError($errCode=1,$msg='操作失败'){
    	$ret=array(
			'result'=>100,
			'errcode'=>$errCode,
			'msg'=>$msg
		);
		echo json_encode($ret);
		exit;
    }
    
    //接口返回结果
    public function retSuccess($data=array(),$msg='操作成功'){
    	$ret=array(
			'result'=>100,
			'errcode'=>0,
			'msg'=>$msg,
			'data'=>$data
		);
		echo json_encode($ret);
		exit;
    }
    
    public function makeLog($type='',$data=''){
		if(!empty($type)){
			@file_put_contents("/data/www/b2b2c/public/business/logs/".$type."/".$type.'_'.date('YmdH').'.txt',$data."\n",FILE_APPEND);
		}
	}
	
	public function makeSqlLog($data){
		return M('systrade_sync_log')->add($data);
	}
}