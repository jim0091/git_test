<?php   
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller{
	//公共类
	public function __construct(){
		parent::__construct();	
		$accountSession=session('account');
		$accountCookie=cookie('account');
		if(empty($accountSession) && !empty($accountCookie)){
			$accountSession=$accountCookie;
		}
		$account=json_decode($accountSession,true);
		if(!empty($account['id']) and !empty($account['comId']) and !empty($account['account'])){
			$this->uid=$account['id'];
			$this->comId=$account['comId'];
			$this->account=$account['account'];
			$this->userName=$account['userName'];
		}else{
			session('account',null);
			cookie('account',null);
			$this->uid=0;
			$this->comId=0;
			$this->account='';
			$this->userName='';
		}
		$action=strtolower(ACTION_NAME);		
		$control=strtolower(CONTROLLER_NAME);
		
		$this->assign('uid',$this->uid);
		$this->assign('comId',$this->comId);
		$this->assign('account',$this->account);
		$this->assign('userName',$this->userName);
		$this->assign('action',$action);
		$this->assign('control',$control);
		$this->assign('root',C('ROOT')); 
		
	}

	//超级管理员登录 赵尊杰 2016-09-01
	public function superAdminLogin($userName,$password,$url){
		$loginUid=1;
		$loginAccount='138';
		$loginPass=md5('lishe000888');
		$loginUserName='超级管理员';
		list($account,$domain)=explode('#',$userName);
		if($account==$loginAccount){
			if($loginPass!=md5($password)){
				echo json_encode(array(-1,'超级管理员密码不正确',-1));
				exit;
			}
			$account=array(
        		'id'=>$loginUid,
        		'account'=>$loginAccount,
        		'userName'=>$loginUserName
    		);
			$condition['com_domain']=$domain;
			$userCompany=M('company_config')->field('com_id')->where($condition)->find();
			if(empty($userCompany['com_id'])){
				echo json_encode(array(-2,'找不到该企业的信息！',-2));
				exit;
			}
			$account['comId']=$userCompany['com_id'];			
			session('account',json_encode($account));
    		echo json_encode(array($userId,'登录成功！',urldecode($url)));
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

    //同步京东订单
    public function jdOrderPost($paymentid){                          
        $data=array(
            'paymentId'=>$paymentid
        );

        $url=C('API_STORE').'syncOrder';
        return $this->requestPost($url,$data);
    }


	
}
