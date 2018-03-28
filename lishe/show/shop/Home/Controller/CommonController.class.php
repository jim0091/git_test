<?php   
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller{
	//公共类
	public function __construct(){
		parent::__construct();
				
		//如果商城清除cookie，则清除所有的cookie和session，实现同步退出
		$accountCookie=cookie('account');
		if(empty($accountCookie)){
			session(null);
			cookie('account',null);
			cookie('LSUID',null);
			cookie('UNAME',null);
		}
		$accountSession=session('account');
		if(!empty($accountSession)){
			$account=$accountSession['member'];
		}
		if(empty($accountSession) && !empty($accountCookie)){
			$account=json_decode($accountCookie,true);
		}
		
		if(!empty($account['id']) and !empty($account['comId']) and !empty($account['account'])){
			$this->uid=$account['id'];
			$this->comId=$account['comId'];
			$this->account=$account['account'];
			$this->userName=$account['userName'];
			$this->index=$account['index'];
			$this->refer=$account['refer'];
		}else{
			$this->uid='';
			$this->comId='';
			$this->account='';
			$this->userName='';
		}
		$action=strtolower(ACTION_NAME);		
		$control=strtolower(CONTROLLER_NAME);
		$this->action=$action;
		$this->control=$control;
		
		$this->assign('uid',$this->uid);
		$this->assign('comId',$this->comId);
		$this->assign('account',$this->account);
		$this->assign('userName',$this->userName);
		$this->assign('index',urldecode($this->index));
		$this->assign('action',$action);
		$this->assign('control',$control);
		$this->assign('root',C('ROOT'));
	}

	//超级管理员登录 赵尊杰 2016-09-01
	public function superAdminLogin($userName,$password,$mark){
		$loginUid=1;
		$loginAccount='13800008888';
		$loginPass=md5('lishe000888');
		$loginUserName='超级管理员';
		if($userName==$loginAccount){
			if($loginPass!=md5($password)){
				echo json_encode(array(-1,'超级管理员密码不正确',-1));
				exit;
			}
			$account=array(
        		'id'=>$loginUid,
        		'account'=>$loginAccount,
        		'userName'=>$loginUserName
    		);
			$condition['mark']=$mark;
			$userCompany=M('company_config')->field('com_id,refer,index')->where($condition)->find();
			if(empty($userCompany['com_id'])){
				echo json_encode(array(-2,'找不到该企业的信息！',-2));
				exit;
			}
			$account['comId']=$userCompany['com_id'];
			$account['index']=urlencode($userCompany['index']);					
			$account['refer']=urlencode($userCompany['refer']);
			
			session('account',array('member'=>$account));
    		cookie('account',json_encode($account));
    		cookie('LSUID',$loginUid);
    		cookie('UNAME',$data['empName']);
    		echo json_encode(array($loginUid,'登录成功！',$userCompany['refer']));
    		exit;
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

    //文件日志
    public function makeLog($type='',$data=''){
		if(!empty($type)){
			@file_put_contents(C('DIR_LOG').$type."/".$type.'_'.date('YmdH').'.txt',$data."\n",FILE_APPEND);
		}
	}
	
	public function getComDomain(){
		$host=$_SERVER['HTTP_HOST'];
    	$domain=current(explode(".",$host));
    	if(empty($domain)){
			$domain='www';
		}
    	return $domain;
    }
    
    public function getPayType(){
		$host=$_SERVER['HTTP_HOST'];
    	$domain=current(explode(".",$host));
    	if(empty($domain)){
			return NULL;
		}
		$payType=array(
			'gd10086'=>'e-card'
		);
		return $payType[$domain];
    }
    
    //企业规则
    public function getRule($data){
    	if($data['type']=='itemLimit' && $this->comId=='1467166836740'){
    		if($data['from']!=md5($data['itemId'])){
				return array('limited'=>1);
			}else{
				return array('limited'=>0);
			}
		}
    }
}
