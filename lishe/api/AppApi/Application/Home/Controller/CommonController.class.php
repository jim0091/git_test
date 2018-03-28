<?php   
namespace Home\Controller;
use Think\Controller;
use Think\Cache\Driver\Redis;
class CommonController extends Controller{
	//公共类
	public function __construct(){
		parent::__construct();
		$this->redis=new Redis();
        $token=I("post.token");
        if(empty($token)){
        	$token=I("get.token");
        }
        if($token){
            $userInfo=$this->redis->get($token);
            if($userInfo){
                $this->uid=$userInfo['id'];
                $this->comId=$userInfo['comId'];
                $this->account=$userInfo['account'];
                $this->userName=$userInfo['userName'];
                $_SESSION['uid'] = $userInfo['id']; 
                $_SESSION['comId'] = $userInfo['comId'];
                $_SESSION['account'] = $userInfo['account'];
                $_SESSION['userName'] = $userInfo['userName'];
//                 $uid = $_SESSION['uid'];
            }
        }
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
    //文件日志
    public function makeLog($type='',$data=''){
        if(!empty($type)){
            @file_put_contents(C('DIR_LOG').$type."/".$type.'_'.date('YmdH').'.txt',$data."\n",FILE_APPEND);
        }
    }
}
